<?php

namespace App\Http\Controllers\Api\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ESP32Controller extends Controller
{
    /**
     * Get device configuration including server time, update request status, and check interval
     * 
     * @deprecated This endpoint is deprecated. The system is migrating from REST API to MQTT broker communication.
     * @param Request $request
     * @return JsonResponse
     */
    public function getConfig(Request $request): JsonResponse
    {
        try {
            // Get the device based on API token
            $device = $this->getDeviceFromToken($request);
            
            if (!$device) {
                return response()->json([
                    'error' => 'Invalid API token or device not found'
                ], 401);
            }

            // Check if device is active
            if (!$device->station_active) {
                return response()->json([
                    'error' => 'Device is not active'
                ], 403);
            }

            // Prepare configuration response
            $now = Carbon::now('Asia/Singapore');
            $config = [
                'serverTime' => $now->format('Y-m-d H:i:s'), // Format for ESP32 processing: 2025-08-16 11:08:30
                'updateRequest' => (bool) $device->request_update,
                'nextCheckInterval' => $device->data_interval_minutes * 60, // Convert minutes to seconds
                'station_id' => $device->station_id,
                'data_collection_time' => $device->data_collection_time_minutes * 60, // Convert minutes to seconds
            ];

            // If there was an update request, reset it to false after sending config
            if ($device->request_update) {
                $device->update(['request_update' => false]);
                
                // Log the update request fulfillment
                \Log::info('ESP32 Config - Update request fulfilled', [
                    'device_id' => $device->id,
                    'station_id' => $device->station_id,
                    'api_token_last_4' => substr($device->api_token, -4)
                ]);
            }

            // Update device last_seen timestamp
            $device->update([
                'last_seen' => now(),
                'status' => 'online'
            ]);

            return response()->json($config);

        } catch (\Exception $e) {
            \Log::error('ESP32 Config Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
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

    /**
     * Upload sensor data from ESP32 device
     * 
     * @deprecated This endpoint is deprecated. The system is migrating from REST API to MQTT broker communication.
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadData(Request $request): JsonResponse
    {
        try {
            // Get the device based on API token
            $device = $this->getDeviceFromToken($request);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid API token or device not found'
                ], 401);
            }

            // Check if device is active
            if (!$device->station_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Device is not active'
                ], 403);
            }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'station_code' => 'required|string|max:50',
                'humidity' => 'required|numeric|min:0|max:100',
                'temperature' => 'required|numeric|min:-50|max:100',
                'rssi' => 'required|integer|min:-120|max:0',
                'battery_voltage' => 'required|numeric|min:0|max:5',
                'update_request' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify station code matches device
            if ($request->station_code !== $device->station_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station code does not match device'
                ], 403);
            }

            // Use database transaction to minimize lock time
            $sensorReading = \DB::transaction(function () use ($device, $request) {
                // Create sensor reading
                $reading = SensorReading::create([
                    'device_id' => $device->id,
                    'temperature' => $request->temperature,
                    'humidity' => $request->humidity,
                    'rssi' => $request->rssi,
                    'battery_voltage' => $request->battery_voltage,
                    'reading_time' => now(),
                    'web_triggered' => $request->boolean('update_request', false)
                ]);

                // Update device status and reset request_update to FALSE
                // last_seen will be updated only when dashboard refreshes
                $device->update([
                    'status' => 'online',
                    'request_update' => false
                ]);

                return $reading;
            });

            // Log after transaction (non-blocking)
            \Log::info('ESP32 Data Upload Success', [
                'device_id' => $device->id,
                'station_id' => $device->station_id,
                'reading_id' => $sensorReading->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sensor data uploaded successfully',
                'data' => [
                    'reading_id' => $sensorReading->id,
                    'device_id' => $device->id,
                    'station_id' => $device->station_id,
                    'timestamp' => $sensorReading->reading_time->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
                    'request_update' => false
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('ESP32 Upload Error', [
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
}