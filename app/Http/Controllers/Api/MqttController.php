<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MqttController extends Controller
{
    protected $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Handle heartbeat request from ESP32
     * Route: POST /api/mqtt/heartbeat/request
     */
    public function handleHeartbeat(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|string',
            'api_key' => 'required|string',
            'task' => 'required|string|in:heartbeat',
            'message' => 'required|string|in:SEND',
            'params' => 'required|array',
            'params.status' => 'required|string|in:online,offline'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'station_id' => $request->input('station_id', ''),
                'api_key' => $request->input('api_key', ''),
                'task' => 'heartbeat',
                'message' => 'ERROR',
                'success' => false,
                'error' => 'Invalid payload format: ' . $validator->errors()->first()
            ], 400);
        }

        $response = $this->mqttService->handleHeartbeat($request->all());
        
        // Publish response to MQTT broker
        if ($response['success'] ?? false) {
            $topic = $this->mqttService->generateResponseTopic($response['station_id'], 'heartbeat');
            $this->mqttService->publishToMqtt($topic, $response);
        }

        return response()->json($response);
    }

    /**
     * Handle configuration request from ESP32
     * Route: POST /api/mqtt/config/request
     */
    public function handleConfigRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|string',
            'api_key' => 'required|string',
            'task' => 'required|string|in:Configuration Update',
            'message' => 'required|string|in:SEND',
            'params' => 'required|array',
            'params.configuration_update' => 'required|string|in:false'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'station_id' => $request->input('station_id', ''),
                'api_key' => $request->input('api_key', ''),
                'task' => 'Configuration Update',
                'message' => 'ERROR',
                'success' => false,
                'error' => 'Invalid payload format: ' . $validator->errors()->first()
            ], 400);
        }

        $response = $this->mqttService->handleConfigRequest($request->all());
        
        // Publish response to MQTT broker
        if ($response['reply']['success'] ?? false) {
            $topic = $this->mqttService->generateResponseTopic($response['station_id'], 'config');
            $this->mqttService->publishToMqtt($topic, $response);
        }

        return response()->json($response);
    }

    /**
     * Handle data upload request from ESP32
     * Route: POST /api/mqtt/data/request
     */
    public function handleDataUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|string',
            'api_key' => 'required|string',
            'task' => 'required|string|in:Upload Data',
            'message' => 'required|string|in:SEND',
            'params' => 'required|array',
            'params.humidity' => 'required|numeric',
            'params.temperature' => 'required|numeric',
            'params.rssi' => 'required|integer',
            'params.battery_voltage' => 'required|numeric',
            'params.update_request' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'station_id' => $request->input('station_id', ''),
                'api_key' => $request->input('api_key', ''),
                'task' => 'Upload Data',
                'message' => 'ERROR',
                'success' => false,
                'error' => 'Invalid payload format: ' . $validator->errors()->first()
            ], 400);
        }

        $response = $this->mqttService->handleDataUpload($request->all());
        
        // Publish response to MQTT broker
        if ($response['reply']['success'] ?? false) {
            $topic = $this->mqttService->generateResponseTopic($response['station_id'], 'data');
            $this->mqttService->publishToMqtt($topic, $response);
        }

        return response()->json($response);
    }

    /**
     * Request manual data update from device
     * Route: POST /api/mqtt/data/manual-request
     */
    public function requestDataUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request: ' . $validator->errors()->first()
            ], 400);
        }

        $success = $this->mqttService->requestDataUpdate($request->input('station_id'));

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Data update requested successfully' : 'Failed to request data update'
        ]);
    }

    /**
     * Request configuration update from device
     * Route: POST /api/mqtt/config/manual-request
     */
    public function requestConfigUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request: ' . $validator->errors()->first()
            ], 400);
        }

        $success = $this->mqttService->requestConfigUpdate($request->input('station_id'));

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Configuration update requested successfully' : 'Failed to request configuration update'
        ]);
    }
}