<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MqttConfigController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Handle heartbeat request from ESP32 device (HTTP fallback)
     * Step 5: ESP32 send heartbeat request → iot/heartBeat/request
     * Step 6: Server send response → iot/heartBeat/response
     */
    public function handleHeartbeat(Request $request): JsonResponse
    {
        try {
            // Validate heartbeat request data - new format from process flow
            $request->validate([
                'station_id' => 'required|string|max:50',
                'task' => 'required|string',
                'message' => 'required|string', 
                'status' => 'required|string'
            ]);

            // Get station information with device status and configuration
            $station = DB::table('station_information as si')
                ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->where('si.station_id', $request->station_id)
                ->where('si.station_active', true)
                ->select(
                    'si.*',
                    'ds.status',
                    'ds.request_update',
                    'ds.last_seen',
                    'dc.data_interval',
                    'dc.data_collection_time',
                    'dc.configuration_update'
                )
                ->first();
            
            if (!$station) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station not found for station ID: ' . $request->station_id
                ], 404);
            }

            // Update device status - ESP32 is online
            DB::table('device_status')
                ->where('station_id', $request->station_id)
                ->update([
                    'status' => 'online',
                    'last_seen' => now(),
                    'updated_at' => now()
                ]);

            // Log MQTT task
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $request->station_id,
                'topic' => 'iot/heartBeat/request',
                'task_type' => 'heartbeat',
                'direction' => 'request',
                'status' => 'received',
                'received_at' => now()
            ]);

            // Prepare heartbeat response - Step 6 format
            $response = [
                'station_id' => $station->station_id,
                'task' => 'heartbeat',
                'message' => 'data received',
                'success' => true,
                'request_update' => (bool) $station->request_update,
                'configuration_update' => (bool) $station->configuration_update
            ];

            Log::info('MQTT Heartbeat processed (HTTP fallback)', [
                'station_id' => $station->station_id,
                'request_update' => $station->request_update,
                'configuration_update' => $station->configuration_update
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('MQTT Heartbeat Error (HTTP fallback)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Handle configuration update completion from ESP32
     * Step 7: ESP32 sends configuration update complete → iot/config/request
     */
    public function handleConfigRequest(Request $request): JsonResponse
    {
        try {
            // Validate configuration request - Step 7 format
            $request->validate([
                'station_id' => 'required|string|max:50',
                'task' => 'required|string',
                'message' => 'required|string',
                'configuration_update' => 'required|boolean'
            ]);

            // Get station with configuration
            $station = DB::table('station_information as si')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->where('si.station_id', $request->station_id)
                ->where('si.station_active', true)
                ->select('si.*', 'dc.data_interval', 'dc.data_collection_time')
                ->first();
            
            if (!$station) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station not found for station ID: ' . $request->station_id
                ], 404);
            }

            // Update configuration_update to FALSE as ESP32 confirms completion
            DB::table('device_configurations')
                ->where('station_id', $request->station_id)
                ->update([
                    'configuration_update' => false,
                    'updated_at' => now()
                ]);

            // Log MQTT task
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $request->station_id,
                'topic' => 'iot/config/request',
                'task_type' => 'configuration_update',
                'direction' => 'request',
                'status' => 'received',
                'received_at' => now()
            ]);

            // Send configuration response with actual config values - Step 7 response format
            $response = [
                'station_id' => $station->station_id,
                'task' => 'Configuration Update',
                'message' => 'data received',
                'success' => true,
                'data_collection_time' => $station->data_collection_time,
                'data_interval' => $station->data_interval
            ];

            Log::info('MQTT Configuration Update completed', [
                'station_id' => $station->station_id,
                'data_interval' => $station->data_interval,
                'data_collection_time' => $station->data_collection_time
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('MQTT Configuration Request Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get device configuration for a station
     */
    public function getConfig(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'station_id' => 'required|string|max:50'
            ]);

            $config = DB::table('station_information as si')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
                ->where('si.station_id', $request->station_id)
                ->where('si.station_active', true)
                ->select(
                    'si.station_id',
                    'si.station_name',
                    'dc.data_interval',
                    'dc.data_collection_time',
                    'dc.configuration_update',
                    'ds.request_update'
                )
                ->first();
            
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'config' => $config
            ]);

        } catch (\Exception $e) {
            Log::error('Get Config Error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error occurred'
            ], 500);
        }
    }
}