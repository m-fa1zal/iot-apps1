<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use App\Models\Device;
use App\Models\SensorReading;
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

            // Subscribe to data upload topic
            $this->mqttService->subscribeToDataUpload(function ($topic, $message) {
                $this->handleDataUpload($topic, $message);
            });

            // Subscribe to config response topic
            $this->mqttService->subscribeToConfigResponse(function ($topic, $message) {
                $this->handleConfigResponse($topic, $message);
            });

            $this->info('Subscribed to MQTT topics. Listening for messages...');

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
     * Handle sensor data upload from ESP32
     */
    private function handleDataUpload($topic, $message)
    {
        try {
            $this->line("Received data upload: {$message}");
            
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->error('Invalid JSON data received');
                return;
            }

            // Validate required fields
            $validator = Validator::make($data, [
                'station_id' => 'required|string|max:50',
                'humidity' => 'required|numeric|min:0|max:100',
                'temperature' => 'required|numeric|min:-50|max:100',
                'rssi' => 'required|integer|min:-120|max:0',
                'battery_voltage' => 'required|numeric|min:0|max:5',
                'update_request' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                $this->error('Data validation failed: ' . $validator->errors()->first());
                return;
            }

            // Find device by station ID
            $device = Device::where('station_id', $data['station_id'])
                ->where('station_active', true)
                ->first();

            if (!$device) {
                $this->error("Device not found for station ID: {$data['station_id']}");
                return;
            }

            // Save sensor reading
            $sensorReading = DB::transaction(function () use ($device, $data) {
                $reading = SensorReading::create([
                    'device_id' => $device->id,
                    'temperature' => $data['temperature'],
                    'humidity' => $data['humidity'],
                    'rssi' => $data['rssi'],
                    'battery_voltage' => $data['battery_voltage'],
                    'reading_time' => now(),
                    'web_triggered' => $data['update_request'] ?? false
                ]);

                // Update device status
                $device->update([
                    'status' => 'online',
                    'last_seen' => now(),
                    'request_update' => false
                ]);

                return $reading;
            });

            // Send response back to ESP32
            $response = [
                'success' => true,
                'message' => 'Sensor data received successfully',
                'reading_id' => $sensorReading->id,
                'timestamp' => $sensorReading->reading_time->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
            ];

            $this->mqttService->sendDataResponse($response);

            $this->info("Data saved successfully. Reading ID: {$sensorReading->id}");

            Log::info('MQTT Data Upload processed', [
                'device_id' => $device->id,
                'station_id' => $device->station_id,
                'reading_id' => $sensorReading->id
            ]);

        } catch (\Exception $e) {
            $this->error('Error processing data upload: ' . $e->getMessage());
            Log::error('MQTT Data Upload processing error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Handle config response from ESP32
     */
    private function handleConfigResponse($topic, $message)
    {
        try {
            $this->line("Received config response: {$message}");
            
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->error('Invalid JSON config response received');
                return;
            }

            // Log the config response
            Log::info('MQTT Config Response received', [
                'topic' => $topic,
                'data' => $data
            ]);

            $this->info('Config response processed successfully');

        } catch (\Exception $e) {
            $this->error('Error processing config response: ' . $e->getMessage());
            Log::error('MQTT Config Response processing error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }
}