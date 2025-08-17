<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorReading;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MqttDataController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Process sensor data upload from ESP32 via MQTT
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function receiveData(Request $request): JsonResponse
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

            // Validate request data
            $validator = Validator::make($request->all(), [
                'station_id' => 'required|string|max:50',
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

            // Verify station ID matches device
            if ($request->station_id !== $device->station_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Station ID does not match device'
                ], 403);
            }

            // Use database transaction
            $sensorReading = DB::transaction(function () use ($device, $request) {
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

                // Update device status
                $device->update([
                    'status' => 'online',
                    'request_update' => false
                ]);

                return $reading;
            });

            // Prepare response
            $response = [
                'success' => true,
                'message' => 'Sensor data received successfully',
                'data' => [
                    'reading_id' => $sensorReading->id,
                    'device_id' => $device->id,
                    'station_id' => $device->station_id,
                    'timestamp' => $sensorReading->reading_time->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
                    'request_update' => false
                ]
            ];

            // Send response via MQTT
            $this->mqttService->sendDataResponse($response);

            \Log::info('MQTT Data received successfully', [
                'device_id' => $device->id,
                'station_id' => $device->station_id,
                'reading_id' => $sensorReading->id
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('MQTT Data Error', [
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
     * Manually trigger data request to ESP32 device
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function requestData(Request $request): JsonResponse
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

            // Set update request flag
            $device->update(['request_update' => true]);

            // Send request via MQTT (this could be a specific command topic)
            $request_data = [
                'command' => 'request_data',
                'station_id' => $device->station_id,
                'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
            ];

            $success = $this->mqttService->publish('iot/commands/request_data', json_encode($request_data));

            if ($success) {
                \Log::info('MQTT Data request sent', [
                    'device_id' => $device->id,
                    'station_id' => $device->station_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data request sent to device via MQTT'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to send data request via MQTT'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('MQTT Data Request Error', [
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