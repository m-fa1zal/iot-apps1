<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use Illuminate\Support\Facades\Log;

class MqttListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mqtt:listen {--topics=* : Specific topics to listen to}';

    /**
     * The console command description.
     */
    protected $description = 'Listen to MQTT broker for IoT device messages';

    protected $mqttService;

    public function __construct(MqttService $mqttService)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MQTT listener...');
        
        // Default topics to subscribe to
        $defaultTopics = [
            'iot/+/heartbeat/request',
            'iot/+/config/request',
            'iot/+/data/request'
        ];

        $topics = $this->option('topics') ?: $defaultTopics;

        $this->info('Subscribing to topics: ' . implode(', ', $topics));

        try {
            $this->startMqttListener($topics);
            
        } catch (\Exception $e) {
            $this->error('MQTT listener error: ' . $e->getMessage());
            Log::error('MQTT listener error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Start actual MQTT listener
     */
    private function startMqttListener(array $topics)
    {
        // Debug: Print MQTT connection details
        $this->info('MQTT Configuration:');
        $this->info('  Host: ' . config('mqtt.host', 'NOT SET'));
        $this->info('  Port: ' . config('mqtt.port', 'NOT SET'));
        $this->info('  Username: ' . config('mqtt.username', 'NOT SET'));
        $this->info('  Password: ' . (config('mqtt.password') ? '***SET***' : 'NOT SET'));
        $this->info('  Client ID: ' . config('mqtt.client_id', 'NOT SET'));
        
        $this->info('Connecting to MQTT broker...');
        
        if (!$this->mqttService->connect()) {
            throw new \Exception('Failed to connect to MQTT broker');
        }
        
        $this->info('Connected to MQTT broker successfully!');
        $this->info('MQTT listener is running. Press Ctrl+C to stop.');
        
        // Subscribe to each topic
        foreach ($topics as $topic) {
            $this->info("Subscribing to: {$topic}");
            $this->mqttService->subscribe($topic, function ($topic, $message) {
                $this->handleMqttMessage($topic, $message);
            }, 0);
        }
        
        // Set up signal handling for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }
        
        // Keep listening for messages
        while (true) {
            $this->mqttService->loop(1);
            
            // Check for signals
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }
    }
    
    /**
     * Graceful shutdown handler
     */
    public function shutdown()
    {
        $this->info('Shutting down MQTT listener...');
        $this->mqttService->disconnect();
        exit(0);
    }

    /**
     * Handle incoming MQTT message
     */
    private function handleMqttMessage(string $topic, string $payload)
    {
        // Reduced logging - only show processing, not every received message
        // Log::debug("MQTT message received", ['topic' => $topic, 'payload_length' => strlen($payload)]);
        
        $requestStartTime = microtime(true); // Start timing

        try {
            $data = json_decode($payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->warn("Invalid JSON payload received on topic: {$topic}");
                return;
            }

            // Parse topic to determine action
            $topicParts = $this->mqttService->parseMqttTopic($topic);
            $stationId = $topicParts['station_id'];
            $action = $topicParts['action'];
            $type = $topicParts['type'];

            // $this->info("Processing: {$stationId}/{$action}/{$type}"); // Reduced console output

            if ($type !== 'request') {
                $this->info("Ignoring non-request message");
                return;
            }

            $response = null;

            switch ($action) {
                case 'heartbeat':
                    $this->info("Handling heartbeat request");
                    $response = $this->mqttService->handleHeartbeat($data);
                    break;
                    
                case 'config':
                    $this->info("Handling config request");
                    $response = $this->mqttService->handleConfigRequest($data);
                    break;
                    
                case 'data':
                    $response = $this->mqttService->handleDataUpload($data);
                    break;
                    
                default:
                    $this->warn("Unknown action: {$action}");
                    return;
            }

            if ($response) {
                // Check if response is successful (handle both direct success and nested success in reply)
                $isSuccessful = ($response['success'] ?? false) || ($response['reply']['success'] ?? false);
                
                if ($isSuccessful) {
                    // Publish response back to device
                    $responseTopic = $this->mqttService->generateResponseTopic($stationId, $action);
                    $publishResult = $this->mqttService->publishToMqtt($responseTopic, $response);
                    
                    if ($publishResult) {
                        // Calculate response time
                        $responseTime = round((microtime(true) - $requestStartTime) * 1000); // Convert to milliseconds
                        
                        // Log successful response using message field from response
                        $responseStatus = ($response['message'] === 'RECEIVED') ? 'RECEIVE' : 'SEND'; // Map RECEIVED->RECEIVE
                        $this->mqttService->logMqttTask($stationId, $responseTopic, $action, 'response', $responseStatus, $responseTime);
                    } else {
                        $this->warn("Failed to publish response for {$action} from {$stationId}");
                    }
                } else {
                    $this->warn("Response not successful for {$action} from {$stationId}");
                }
            } else {
                $this->error("No response generated for {$action} from {$stationId}");
            }

        } catch (\Exception $e) {
            $this->error("Error handling MQTT message: " . $e->getMessage());
            Log::error("Error handling MQTT message", [
                'topic' => $topic,
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);
        }
    }
}