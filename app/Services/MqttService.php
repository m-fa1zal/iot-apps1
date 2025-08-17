<?php

namespace App\Services;

use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Log;

class MqttService
{
    private $connectionName = 'default';
    private $isConnected = false;

    /**
     * Get MQTT connection
     */
    private function getConnection()
    {
        return MQTT::connection($this->connectionName);
    }

    /**
     * Connect to MQTT broker
     */
    public function connect(): bool
    {
        try {
            if (!$this->isConnected) {
                $this->getConnection()->connect();
                $this->isConnected = true;
                Log::info('MQTT Service connected successfully');
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
                $this->getConnection()->disconnect();
                $this->isConnected = false;
                Log::info('MQTT Service disconnected');
            }
        } catch (\Exception $e) {
            Log::error('MQTT disconnect error: ' . $e->getMessage());
        }
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

            $this->getConnection()->publish($topic, $message, $qos);
            
            Log::info('MQTT message published', [
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
     * Subscribe to topic with callback
     */
    public function subscribe(string $topic, callable $callback, int $qos = 0): bool
    {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return false;
            }

            $this->getConnection()->subscribe($topic, $callback, $qos);
            
            Log::info('MQTT subscribed to topic', [
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
    public function loop(int $timeoutSeconds = 60): void
    {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return;
            }

            $this->getConnection()->loop(true, true, $timeoutSeconds);
        } catch (\Exception $e) {
            Log::error('MQTT loop error: ' . $e->getMessage());
        }
    }

    /**
     * Send configuration to ESP32 device
     */
    public function sendConfigToDevice(array $config): bool
    {
        $topic = 'iot/config/request';
        $message = json_encode($config);
        
        return $this->publish($topic, $message);
    }

    /**
     * Send data upload response to ESP32 device
     */
    public function sendDataResponse(array $response): bool
    {
        $topic = 'iot/data/response';
        $message = json_encode($response);
        
        return $this->publish($topic, $message);
    }

    /**
     * Subscribe to data upload topic
     */
    public function subscribeToDataUpload(callable $callback): bool
    {
        $topic = 'iot/data/upload';
        return $this->subscribe($topic, $callback);
    }

    /**
     * Subscribe to config response topic
     */
    public function subscribeToConfigResponse(callable $callback): bool
    {
        $topic = 'iot/config/response';
        return $this->subscribe($topic, $callback);
    }

    /**
     * Check if client is connected
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    /**
     * Get connection instance
     */
    public function getClient()
    {
        return $this->getConnection();
    }
}