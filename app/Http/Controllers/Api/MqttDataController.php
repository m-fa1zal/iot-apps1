<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MqttDataController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Process sensor data upload from ESP32 (HTTP fallback)
     * Step 9: ESP32 sends sensor data → iot/data/request
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Validate request data - Step 9 format from process flow
            $validator = Validator::make($request->all(), [
                'station_id' => 'required|string|max:50',
                'task' => 'required|string',
                'message' => 'required|string',
                'humidity' => 'required|numeric|min:0|max:100',
                'temperature' => 'required|numeric|min:-50|max:100',
                'rssi' => 'required|integer|min:-120|max:0',
                'battery_voltage' => 'required|numeric|min:0|max:5',
                'update_request' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find station by station ID
            $station = DB::table('station_information')
                ->where('station_id', $request->station_id)
                ->where('station_active', true)
                ->first();

            if (!$station) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station not found for station ID: ' . $request->station_id
                ], 404);
            }

            // Use database transaction
            $sensorReading = DB::transaction(function () use ($station, $request) {
                // Create sensor reading - now using station_id instead of device_id
                $readingId = DB::table('sensor_readings')->insertGetId([
                    'station_id' => $station->station_id,
                    'temperature' => $request->temperature,
                    'humidity' => $request->humidity,
                    'rssi' => $request->rssi,
                    'battery_voltage' => $request->battery_voltage,
                    'reading_time' => now(),
                    'web_triggered' => $request->boolean('update_request', false),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Update device status and set request_update to FALSE as per Step 9
                DB::table('device_status')
                    ->where('station_id', $station->station_id)
                    ->update([
                        'status' => 'online',
                        'last_seen' => now(),
                        'request_update' => false, // ESP32 sets this to FALSE after data upload
                        'updated_at' => now()
                    ]);

                return $readingId;
            });

            // Log MQTT task
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $request->station_id,
                'topic' => 'iot/data/request',
                'task_type' => 'data_upload',
                'direction' => 'request',
                'status' => 'received',
                'received_at' => now()
            ]);

            // Prepare response - Step 9 response format
            $response = [
                'station_id' => $station->station_id,
                'task' => 'Upload Data',
                'message' => 'data received',
                'success' => true
            ];

            Log::info('MQTT Data received successfully (HTTP fallback)', [
                'station_id' => $station->station_id,
                'reading_id' => $sensorReading,
                'temperature' => $request->temperature,
                'humidity' => $request->humidity
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('MQTT Data Error (HTTP fallback)', [
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
     * Set update request flag for device (for manual data requests)
     * This sets request_update = TRUE in device_status table
     */
    public function requestData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'station_id' => 'required|string|max:50'
            ]);

            $station = DB::table('station_information')
                ->where('station_id', $request->station_id)
                ->where('station_active', true)
                ->first();
            
            if (!$station) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station not found for station ID: ' . $request->station_id
                ], 404);
            }

            // Set request_update flag - device will get this on next heartbeat
            DB::table('device_status')
                ->where('station_id', $request->station_id)
                ->update([
                    'request_update' => true,
                    'updated_at' => now()
                ]);

            Log::info('Data request flag set for station', [
                'station_id' => $station->station_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data request queued - device will receive on next heartbeat',
                'station_id' => $station->station_id
            ]);

        } catch (\Exception $e) {
            Log::error('Data Request Error', [
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
     * Get latest sensor data for a station
     */
    public function getLatestData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'station_id' => 'required|string|max:50'
            ]);

            $latestReading = DB::table('sensor_readings')
                ->where('station_id', $request->station_id)
                ->orderBy('reading_time', 'desc')
                ->first();
            
            if (!$latestReading) {
                return response()->json([
                    'success' => false,
                    'error' => 'No sensor data found for station ID: ' . $request->station_id
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $latestReading
            ]);

        } catch (\Exception $e) {
            Log::error('Get Latest Data Error', [
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