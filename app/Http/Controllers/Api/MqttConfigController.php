<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MqttConfigController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Trigger config request to ESP32 device via MQTT
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function requestConfig(Request $request): JsonResponse
    {
        try {
            $device = $this->getDeviceFromToken($request);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid API token or device not found'
                ], 401);
            }

            if (!$device->station_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Device is not active'
                ], 403);
            }

            // Prepare configuration data
            $now = Carbon::now('Asia/Singapore');
            $config = [
                'serverTime' => $now->format('Y-m-d H:i:s'),
                'updateRequest' => (bool) $device->request_update,
                'nextCheckInterval' => $device->data_interval_minutes * 60,
                'station_id' => $device->station_id,
                'data_collection_time' => $device->data_collection_time_minutes * 60,
            ];

            // Send config via MQTT
            $success = $this->mqttService->sendConfigToDevice($config);

            if ($success) {
                // Reset update request flag and update device status
                if ($device->request_update) {
                    $device->update(['request_update' => false]);
                }

                $device->update([
                    'last_seen' => now(),
                    'status' => 'online'
                ]);

                \Log::info('MQTT Config sent successfully', [
                    'device_id' => $device->id,
                    'station_id' => $device->station_id,
                    'config' => $config
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Configuration sent to device via MQTT',
                    'data' => $config
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to send configuration via MQTT'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('MQTT Config Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get device configuration data (for manual requests)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getConfig(Request $request): JsonResponse
    {
        try {
            $device = $this->getDeviceFromToken($request);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid API token or device not found'
                ], 401);
            }

            if (!$device->station_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Device is not active'
                ], 403);
            }

            // Prepare configuration response
            $now = Carbon::now('Asia/Singapore');
            $config = [
                'serverTime' => $now->format('Y-m-d H:i:s'),
                'updateRequest' => (bool) $device->request_update,
                'nextCheckInterval' => $device->data_interval_minutes * 60,
                'station_id' => $device->station_id,
                'data_collection_time' => $device->data_collection_time_minutes * 60,
            ];

            // Reset update request flag and update device status
            if ($device->request_update) {
                $device->update(['request_update' => false]);
            }

            $device->update([
                'last_seen' => now(),
                'status' => 'online'
            ]);

            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            \Log::error('MQTT Get Config Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get device from API token
     * 
     * @param Request $request
     * @return Device|null
     */
    private function getDeviceFromToken(Request $request): ?Device
    {
        // Try to get token from Authorization header (Bearer token)
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } else {
            // Fallback to api_token parameter
            $token = $request->input('api_token');
        }

        if (!$token) {
            return null;
        }

        return Device::with(['state', 'district'])
            ->where('api_token', $token)
            ->where('station_active', true)
            ->first();
    }
}