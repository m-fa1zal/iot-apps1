<?php

namespace App\Services;

use App\Models\SensorReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    protected $mqtt_host;
    protected $mqtt_port;
    protected $mqtt_username;
    protected $mqtt_password;
    protected $client;
    protected $connectionSettings;
    protected $isConnected = false;

    public function __construct()
    {
        $this->mqtt_host = config('mqtt.host', 'localhost');
        $this->mqtt_port = config('mqtt.port', 1883);
        $this->mqtt_username = config('mqtt.username', '');
        $this->mqtt_password = config('mqtt.password', '');
        
        $this->initializeClient();
    }

    /**
     * Initialize MQTT client with configuration
     */
    private function initializeClient()
    {
        $clientId = config('mqtt.client_id', 'laravel_iot_' . uniqid());
        
        $this->client = new MqttClient($this->mqtt_host, $this->mqtt_port, $clientId);
        
        $this->connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60);
            
        if (!empty($this->mqtt_username)) {
            $this->connectionSettings
                ->setUsername($this->mqtt_username)
                ->setPassword($this->mqtt_password);
        }
    }

    /**
     * Connect to MQTT broker
     */
    public function connect(): bool
    {
        try {
            if (!$this->isConnected) {
                $this->client->connect($this->connectionSettings, true);
                $this->isConnected = true;
                Log::info('MQTT Service connected successfully to ' . $this->mqtt_host . ':' . $this->mqtt_port);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('MQTT connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect from MQTT broker
     */
    public function disconnect(): void
    {
        try {
            if ($this->isConnected) {
                $this->client->disconnect();
                $this->isConnected = false;
                Log::info('MQTT Service disconnected');
            }
        } catch (\Exception $e) {
            Log::error('MQTT disconnect error: ' . $e->getMessage());
        }
    }

    /**
     * Subscribe to topic with callback
     */
    public function subscribe(string $topic, callable $callback, int $qos = 0): bool
    {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return false;
            }

            $this->client->subscribe($topic, $callback, $qos);
            
            Log::debug('MQTT subscribed to topic', [
                'topic' => $topic,
                'qos' => $qos
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('MQTT subscribe failed: ' . $e->getMessage(), [
                'topic' => $topic
            ]);
            return false;
        }
    }

    /**
     * Listen for messages (blocking)
     */
    public function loop(int $timeoutSeconds = 1): void
    {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return;
            }

            $this->client->loop(true, true, $timeoutSeconds);
        } catch (\Exception $e) {
            Log::error('MQTT loop error: ' . $e->getMessage());
        }
    }

    /**
     * Check if client is connected
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    /**
     * Handle heartbeat request from ESP32
     */
    public function handleHeartbeat(array $payload): array
    {
        try {
            $stationId = $payload['station_id'] ?? null;
            $apiKey = $payload['api_key'] ?? null;
            $task = $payload['task'] ?? null;
            $message = $payload['message'] ?? null;
            $params = $payload['params'] ?? [];

            if (!$stationId || !$apiKey || $task !== 'heartbeat' || $message !== 'SEND') {
                return $this->createErrorResponse($stationId, $apiKey, 'heartbeat', 'Invalid payload format');
            }

            $deviceConfig = $this->authenticateDevice($stationId, $apiKey);
            if (!$deviceConfig) {
                return $this->createErrorResponse($stationId, $apiKey, 'heartbeat', 'Invalid device credentials');
            }

            // Update device status and last seen
            DB::table('device_status')
                ->where('station_id', $stationId)
                ->update([
                    'status' => $params['status'] ?? 'online',
                    'last_seen' => now(),
                    'updated_at' => now()
                ]);

            // Get current flags
            $deviceStatus = DB::table('device_status')->where('station_id', $stationId)->first();
            $configUpdate = DB::table('device_configurations')->where('station_id', $stationId)->first();

            // Create heartbeat response
            $response = [
                'station_id' => $stationId,
                'api_key' => $apiKey,
                'task' => 'heartbeat',
                'message' => 'RECEIVED',
                'success' => true,
                'reply' => [
                    'current_time' => time(),
                    'request_update' => (bool) ($deviceStatus->request_update ?? false),
                    'configuration_update' => (bool) ($configUpdate->configuration_update ?? false)
                ]
            ];

            Log::debug("Heartbeat processed for device: {$stationId}");
            return $response;

        } catch (\Exception $e) {
            Log::error("Heartbeat error: " . $e->getMessage());
            return $this->createErrorResponse($stationId ?? '', $apiKey ?? '', 'heartbeat', 'Internal server error');
        }
    }

    /**
     * Handle configuration request from ESP32
     */
    public function handleConfigRequest(array $payload): array
    {
        try {
            $stationId = $payload['station_id'] ?? null;
            $apiKey = $payload['api_key'] ?? null;
            $task = $payload['task'] ?? null;
            $message = $payload['message'] ?? null;
            $params = $payload['params'] ?? [];

            if (!$stationId || !$apiKey || $task !== 'Configuration Update' || $message !== 'SEND') {
                return $this->createErrorResponse($stationId, $apiKey, 'Configuration Update', 'Invalid payload format');
            }

            $deviceConfig = $this->authenticateDevice($stationId, $apiKey);
            if (!$deviceConfig) {
                return $this->createErrorResponse($stationId, $apiKey, 'Configuration Update', 'Invalid device credentials');
            }

            // Update configuration_update status
            if (isset($params['configuration_update']) && $params['configuration_update'] === 'false') {
                DB::table('device_configurations')
                    ->where('station_id', $stationId)
                    ->update([
                        'configuration_update' => false,
                        'updated_at' => now()
                    ]);
            }

            // Get current configuration
            $config = DB::table('device_configurations')->where('station_id', $stationId)->first();

            // Create configuration response
            $response = [
                'station_id' => $stationId,
                'api_key' => $apiKey,
                'task' => 'Configuration Update',
                'message' => 'RECEIVED',
                'reply' => [
                    'success' => true,
                    'data_collection_time' => $config->data_collection_time ?? 30,
                    'data_interval' => $config->data_interval ?? 3
                ]
            ];

            Log::debug("Configuration update processed for device: {$stationId}");
            return $response;

        } catch (\Exception $e) {
            Log::error("Config request error: " . $e->getMessage());
            return $this->createErrorResponse($stationId ?? '', $apiKey ?? '', 'Configuration Update', 'Internal server error');
        }
    }

    /**
     * Handle data upload request from ESP32
     */
    public function handleDataUpload(array $payload): array
    {
        try {
            $stationId = $payload['station_id'] ?? null;
            $apiKey = $payload['api_key'] ?? null;
            $task = $payload['task'] ?? null;
            $message = $payload['message'] ?? null;
            $params = $payload['params'] ?? [];

            if (!$stationId || !$apiKey || $task !== 'Upload Data' || $message !== 'SEND') {
                return $this->createErrorResponse($stationId, $apiKey, 'Upload Data', 'Invalid payload format');
            }

            $deviceConfig = $this->authenticateDevice($stationId, $apiKey);
            if (!$deviceConfig) {
                return $this->createErrorResponse($stationId, $apiKey, 'Upload Data', 'Invalid device credentials');
            }

            // Save sensor reading (using station_id instead of device_id)
            DB::table('sensor_readings')->insert([
                'station_id' => $stationId,
                'temperature' => $params['temperature'] ?? null,
                'humidity' => $params['humidity'] ?? null,
                'rssi' => $params['rssi'] ?? null,
                'battery_voltage' => $params['battery_voltage'] ?? null,
                'reading_time' => now(),
                'web_triggered' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update device status
            DB::table('device_status')
                ->where('station_id', $stationId)
                ->update([
                    'request_update' => isset($params['update_request']) ? (bool) $params['update_request'] : false,
                    'last_seen' => now(),
                    'status' => 'online',
                    'updated_at' => now()
                ]);

            // Create data upload response
            $response = [
                'station_id' => $stationId,
                'api_key' => $apiKey,
                'task' => 'Upload Data',
                'message' => 'RECEIVED',
                'reply' => [
                    'success' => true
                ]
            ];

            Log::debug("Data upload processed for device: {$stationId}");
            return $response;

        } catch (\Exception $e) {
            Log::error("Data upload error: " . $e->getMessage());
            return $this->createErrorResponse($stationId ?? '', $apiKey ?? '', 'Upload Data', 'Internal server error');
        }
    }

    /**
     * Publish message to MQTT broker
     */
    public function publishToMqtt(string $topic, array $payload): bool
    {
        return $this->publish($topic, json_encode($payload), 1);
    }

    /**
     * Publish message to topic
     */
    public function publish(string $topic, string $message, int $qos = 0): bool
    {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return false;
            }

            $this->client->publish($topic, $message, $qos);
            
            Log::debug('MQTT message published', [
                'topic' => $topic,
                'message_length' => strlen($message),
                'qos' => $qos
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('MQTT publish failed: ' . $e->getMessage(), [
                'topic' => $topic,
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Request manual data update from device
     */
    public function requestDataUpdate(string $stationId): bool
    {
        try {
            $exists = DB::table('station_information')->where('station_id', $stationId)->exists();
            if (!$exists) {
                return false;
            }

            // Set request_update flag
            DB::table('device_status')
                ->where('station_id', $stationId)
                ->update([
                    'request_update' => true,
                    'updated_at' => now()
                ]);

            Log::debug("Data update requested for device: {$stationId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Request data update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Request configuration update from device
     */
    public function requestConfigUpdate(string $stationId): bool
    {
        try {
            $exists = DB::table('station_information')->where('station_id', $stationId)->exists();
            if (!$exists) {
                return false;
            }

            // Set configuration_update flag
            DB::table('device_configurations')
                ->where('station_id', $stationId)
                ->update([
                    'configuration_update' => true,
                    'updated_at' => now()
                ]);

            Log::debug("Configuration update requested for device: {$stationId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Request config update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate device using station_id and api_key
     */
    protected function authenticateDevice(string $stationId, string $apiKey): ?object
    {
        return DB::table('device_configurations as dc')
            ->join('station_information as si', 'dc.station_id', '=', 'si.station_id')
            ->where('dc.station_id', $stationId)
            ->where('dc.api_token', $apiKey)
            ->where('si.station_active', true)
            ->select('dc.*', 'si.station_active')
            ->first();
    }

    /**
     * Create error response
     */
    protected function createErrorResponse(string $stationId, string $apiKey, string $task, string $error): array
    {
        return [
            'station_id' => $stationId,
            'api_key' => $apiKey,
            'task' => $task,
            'message' => 'ERROR',
            'success' => false,
            'error' => $error
        ];
    }

    /**
     * Parse MQTT topic to extract station_id and action
     */
    public function parseMqttTopic(string $topic): array
    {
        // Topic format: iot/{station_id}/{action}/{type}
        // Example: iot/KL001/heartbeat/request
        $parts = explode('/', $topic);
        
        if (count($parts) !== 4 || $parts[0] !== 'iot') {
            return ['station_id' => null, 'action' => null, 'type' => null];
        }

        return [
            'station_id' => $parts[1],
            'action' => $parts[2],
            'type' => $parts[3]
        ];
    }

    /**
     * Generate MQTT topic for response
     */
    public function generateResponseTopic(string $stationId, string $action): string
    {
        return "iot/{$stationId}/{$action}/response";
    }
}