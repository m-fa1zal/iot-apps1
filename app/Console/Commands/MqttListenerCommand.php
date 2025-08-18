<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MqttListenerCommand extends Command
{
    protected $signature = 'mqtt:listen {--timeout=3600}';
    protected $description = 'Listen for MQTT messages from ESP32 devices';

    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
    }

    public function handle()
    {
        $timeout = $this->option('timeout');
        
        $this->info("Starting MQTT listener (timeout: {$timeout}s)...");
        
        try {
            // Connect to MQTT broker
            if (!$this->mqttService->connect()) {
                $this->error('Failed to connect to MQTT broker');
                return 1;
            }

            $this->info('Connected to MQTT broker successfully');

            // Subscribe to all MQTT topics according to new process flow
            $this->mqttService->subscribeToHeartbeatRequest(function ($topic, $message) {
                $this->handleHeartbeatRequest($topic, $message);
            });

            $this->mqttService->subscribeToConfigRequest(function ($topic, $message) {
                $this->handleConfigRequest($topic, $message);
            });

            $this->mqttService->subscribeToDataRequest(function ($topic, $message) {
                $this->handleDataRequest($topic, $message);
            });

            $this->info('Subscribed to MQTT topics. Listening for messages...');
            $this->info('Topics: iot/heartBeat/request, iot/config/request, iot/data/request');

            // Start listening loop
            $this->mqttService->loop($timeout);

        } catch (\Exception $e) {
            $this->error('MQTT Listener error: ' . $e->getMessage());
            Log::error('MQTT Listener Command error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        } finally {
            $this->mqttService->disconnect();
            $this->info('MQTT listener stopped');
        }

        return 0;
    }

    /**
     * Handle heartbeat request from ESP32
     * Step 5: ESP32 → Server: iot/heartBeat/request
     * Step 6: Server → ESP32: iot/heartBeat/response
     */
    private function handleHeartbeatRequest($topic, $message)
    {
        try {
            $this->line("Received heartbeat request: {$message}");
            
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->error('Invalid JSON heartbeat request received');
                return;
            }

            // Validate required fields - Step 5 format
            $validator = Validator::make($data, [
                'station_id' => 'required|string|max:50',
                'task' => 'required|string',
                'message' => 'required|string',
                'status' => 'required|string'
            ]);

            if ($validator->fails()) {
                $this->error('Heartbeat validation failed: ' . $validator->errors()->first());
                return;
            }

            // Get station information with device status and configuration
            $station = DB::table('station_information as si')
                ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->where('si.station_id', $data['station_id'])
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
                $this->error("Station not found for station ID: {$data['station_id']}");
                return;
            }

            // Update device status - ESP32 is online
            DB::table('device_status')
                ->where('station_id', $data['station_id'])
                ->update([
                    'status' => 'online',
                    'last_seen' => now(),
                    'updated_at' => now()
                ]);

            // Log MQTT task
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $data['station_id'],
                'topic' => $topic,
                'task_type' => 'heartbeat',
                'direction' => 'request',
                'status' => 'received',
                'received_at' => now()
            ]);

            // Send heartbeat response with configuration - Step 6 format
            $response = [
                'station_id' => $station->station_id,
                'task' => 'heartbeat',
                'message' => 'data received',
                'success' => true,
                'request_update' => (bool) $station->request_update,
                'configuration_update' => (bool) $station->configuration_update
            ];

            $this->mqttService->sendHeartbeatResponse($response);

            // Log response
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $data['station_id'],
                'topic' => 'iot/heartBeat/response',
                'task_type' => 'heartbeat',
                'direction' => 'response',
                'status' => 'sent',
                'received_at' => now()
            ]);

            $this->info("Heartbeat processed for station: {$station->station_id}");

            Log::info('MQTT Heartbeat processed', [
                'station_id' => $station->station_id,
                'request_update' => $station->request_update,
                'configuration_update' => $station->configuration_update
            ]);

        } catch (\Exception $e) {
            $this->error('Error processing heartbeat request: ' . $e->getMessage());
            Log::error('MQTT Heartbeat processing error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Handle configuration request from ESP32
     * Step 7: ESP32 → Server: iot/config/request
     */
    private function handleConfigRequest($topic, $message)
    {
        try {
            $this->line("Received config request: {$message}");
            
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->error('Invalid JSON config request received');
                return;
            }

            // Validate required fields - Step 7 format
            $validator = Validator::make($data, [
                'station_id' => 'required|string|max:50',
                'task' => 'required|string',
                'message' => 'required|string',
                'configuration_update' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $this->error('Config validation failed: ' . $validator->errors()->first());
                return;
            }

            // Get station with configuration
            $station = DB::table('station_information as si')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->where('si.station_id', $data['station_id'])
                ->where('si.station_active', true)
                ->select('si.*', 'dc.data_interval', 'dc.data_collection_time')
                ->first();

            if (!$station) {
                $this->error("Station not found for station ID: {$data['station_id']}");
                return;
            }

            // Update configuration_update to FALSE as ESP32 confirms completion
            DB::table('device_configurations')
                ->where('station_id', $data['station_id'])
                ->update([
                    'configuration_update' => false,
                    'updated_at' => now()
                ]);

            // Log MQTT task
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $data['station_id'],
                'topic' => $topic,
                'task_type' => 'configuration_update',
                'direction' => 'request',
                'status' => 'received',
                'received_at' => now()
            ]);

            // Send configuration response with actual config values - Step 7 response
            $response = [
                'station_id' => $station->station_id,
                'task' => 'Configuration Update',
                'message' => 'data received',
                'success' => true,
                'data_collection_time' => $station->data_collection_time,
                'data_interval' => $station->data_interval
            ];

            $this->mqttService->sendConfigResponse($response);

            // Log response
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $data['station_id'],
                'topic' => 'iot/config/response',
                'task_type' => 'configuration_update',
                'direction' => 'response',
                'status' => 'sent',
                'received_at' => now()
            ]);

            $this->info("Config update completed for station: {$station->station_id}");

            Log::info('MQTT Configuration Update completed', [
                'station_id' => $station->station_id,
                'data_interval' => $station->data_interval,
                'data_collection_time' => $station->data_collection_time
            ]);

        } catch (\Exception $e) {
            $this->error('Error processing config request: ' . $e->getMessage());
            Log::error('MQTT Config processing error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Handle sensor data upload from ESP32
     * Step 9: ESP32 → Server: iot/data/request
     */
    private function handleDataRequest($topic, $message)
    {
        try {
            $this->line("Received data upload: {$message}");
            
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->error('Invalid JSON data received');
                return;
            }

            // Validate required fields - Step 9 format
            $validator = Validator::make($data, [
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
                $this->error('Data validation failed: ' . $validator->errors()->first());
                return;
            }

            // Find station by station ID
            $station = DB::table('station_information')
                ->where('station_id', $data['station_id'])
                ->where('station_active', true)
                ->first();

            if (!$station) {
                $this->error("Station not found for station ID: {$data['station_id']}");
                return;
            }

            // Save sensor reading
            $sensorReadingId = DB::transaction(function () use ($station, $data) {
                // Create sensor reading - using station_id instead of device_id
                $readingId = DB::table('sensor_readings')->insertGetId([
                    'station_id' => $station->station_id,
                    'temperature' => $data['temperature'],
                    'humidity' => $data['humidity'],
                    'rssi' => $data['rssi'],
                    'battery_voltage' => $data['battery_voltage'],
                    'reading_time' => now(),
                    'web_triggered' => $data['update_request'],
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
                'station_id' => $data['station_id'],
                'topic' => $topic,
                'task_type' => 'data_upload',
                'direction' => 'request',
                'status' => 'received',
                'received_at' => now()
            ]);

            // Send response back to ESP32 - Step 9 response format
            $response = [
                'station_id' => $station->station_id,
                'task' => 'Upload Data',
                'message' => 'data received',
                'success' => true
            ];

            $this->mqttService->sendDataResponse($response);

            // Log response
            DB::table('mqtt_task_logs')->insert([
                'station_id' => $data['station_id'],
                'topic' => 'iot/data/response',
                'task_type' => 'data_upload',
                'direction' => 'response',
                'status' => 'sent',
                'received_at' => now()
            ]);

            $this->info("Data saved successfully. Reading ID: {$sensorReadingId}");

            Log::info('MQTT Data Upload processed', [
                'station_id' => $station->station_id,
                'reading_id' => $sensorReadingId,
                'temperature' => $data['temperature'],
                'humidity' => $data['humidity']
            ]);

        } catch (\Exception $e) {
            $this->error('Error processing data upload: ' . $e->getMessage());
            Log::error('MQTT Data Upload processing error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }
}