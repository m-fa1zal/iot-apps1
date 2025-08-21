<?php
/**
 * Production MQTT Listener for IoT Device Communication
 * Handles heartbeat, configuration, and data upload requests
 */

require_once 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

// Load Laravel environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuration from .env
$mqttConfig = [
    'host' => $_ENV['MQTT_HOST'],
    'port' => (int)$_ENV['MQTT_PORT'], 
    'username' => $_ENV['MQTT_USERNAME'],
    'password' => $_ENV['MQTT_PASSWORD'],
    'client_id' => $_ENV['MQTT_CLIENT_ID'] . '_' . uniqid()
];

$dbConfig = [
    'host' => $_ENV['DB_HOST'],
    'dbname' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD']
];

// Database connection
try {
    $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", 
                   $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "🚀 Starting IoT MQTT Listener Service\n";
echo "Configuration:\n";
echo "  MQTT: {$mqttConfig['host']}:{$mqttConfig['port']}\n";
echo "  Client ID: {$mqttConfig['client_id']}\n";
echo "  Database: {$dbConfig['dbname']}\n";

// MQTT Connection
try {
    $mqtt = new MqttClient($mqttConfig['host'], $mqttConfig['port'], $mqttConfig['client_id']);
    
    $connectionSettings = (new ConnectionSettings())
        ->setUsername($mqttConfig['username'])
        ->setPassword($mqttConfig['password'])
        ->setKeepAliveInterval(60);
        
    $mqtt->connect($connectionSettings, true);
    echo "✅ MQTT broker connected\n";
    
    // Subscribe to IoT device topics
    $topics = [
        'iot/+/heartbeat/request' => 'handleHeartbeat',
        'iot/+/config/request' => 'handleConfig', 
        'iot/+/data/request' => 'handleData'
    ];
    
    foreach ($topics as $topic => $handler) {
        echo "📡 Subscribing to: {$topic}\n";
        $mqtt->subscribe($topic, function ($topic, $message) use ($mqtt, $pdo, $handler) {
            handleMqttMessage($topic, $message, $mqtt, $pdo, $handler);
        }, 0);
    }
    
    echo "\n🎯 MQTT Listener Service is active\n";
    echo "Listening for IoT device messages...\n\n";
    
    // Keep listening
    while (true) {
        $mqtt->loop(1);
        usleep(100000); // 0.1 second
    }
    
} catch (Exception $e) {
    echo "❌ MQTT Error: " . $e->getMessage() . "\n";
}

/**
 * Handle incoming MQTT messages
 */
function handleMqttMessage($topic, $message, $mqtt, $pdo, $handler) {
    echo "\n🔔 [" . date('Y-m-d H:i:s') . "] Message received\n";
    echo "   Topic: {$topic}\n";
    
    // Parse topic to extract station ID and action
    if (preg_match('/^iot\/([^\/]+)\/([^\/]+)\/request$/', $topic, $matches)) {
        $stationId = $matches[1];
        $action = $matches[2];
        
        echo "   Station: {$stationId}, Action: {$action}\n";
        
        // Route to appropriate handler
        switch ($action) {
            case 'heartbeat':
                $response = handleHeartbeat($stationId, $message, $pdo);
                break;
            case 'config':
                $response = handleConfig($stationId, $message, $pdo);
                break;
            case 'data':
                $response = handleData($stationId, $message, $pdo);
                break;
            default:
                echo "   ⚠️  Unknown action: {$action}\n";
                return;
        }
        
        // Send response
        $responseTopic = "iot/{$stationId}/{$action}/response";
        $mqtt->publish($responseTopic, json_encode($response), 0);
        echo "   ✅ Response sent to: {$responseTopic}\n";
    }
}

/**
 * Handle heartbeat requests
 */
function handleHeartbeat($stationId, $message, $pdo) {
    // Get device status flags from database
    try {
        $statusQuery = $pdo->prepare("SELECT request_update FROM device_status WHERE station_id = ?");
        $statusQuery->execute([$stationId]);
        $deviceStatus = $statusQuery->fetch(PDO::FETCH_ASSOC);
        $requestUpdate = $deviceStatus ? (bool)$deviceStatus['request_update'] : false;
        
        $configQuery = $pdo->prepare("SELECT configuration_update FROM device_configurations WHERE station_id = ?");
        $configQuery->execute([$stationId]);
        $deviceConfig = $configQuery->fetch(PDO::FETCH_ASSOC);
        $configurationUpdate = $deviceConfig ? (bool)$deviceConfig['configuration_update'] : false;
        
        echo "   DB Status: request_update={$requestUpdate}, config_update={$configurationUpdate}\n";
        
    } catch (PDOException $e) {
        echo "   ⚠️  DB Error: " . $e->getMessage() . "\n";
        $requestUpdate = false;
        $configurationUpdate = false;
    }
    
    return [
        'station_id' => $stationId,
        'api_key' => 'gVJA4hJ5GiihV9jYud1VebhZ3QZOgLmMZWFRoMyTz8bS3Eno8fb3SlElG8AiWLub',
        'task' => 'heartbeat',
        'message' => 'RECEIVED',
        'success' => true,
        'reply' => [
            'current_date' => time(),
            'request_update' => $requestUpdate,
            'configuration_update' => $configurationUpdate
        ]
    ];
}

/**
 * Handle configuration requests
 */
function handleConfig($stationId, $message, $pdo) {
    // Get configuration from database
    try {
        $configQuery = $pdo->prepare("SELECT data_interval, data_collection_time FROM device_configurations WHERE station_id = ?");
        $configQuery->execute([$stationId]);
        $config = $configQuery->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            $dataInterval = (int)$config['data_interval'];
            $dataCollectionTime = (int)$config['data_collection_time'];
        } else {
            // Default values
            $dataInterval = 3;
            $dataCollectionTime = 30;
        }
        
        echo "   Config: data_interval={$dataInterval}min, data_collection_time={$dataCollectionTime}min\n";
        
    } catch (PDOException $e) {
        echo "   ⚠️  DB Error: " . $e->getMessage() . "\n";
        $dataInterval = 3;
        $dataCollectionTime = 30;
    }
    
    return [
        'station_id' => $stationId,
        'api_key' => 'gVJA4hJ5GiihV9jYud1VebhZ3QZOgLmMZWFRoMyTz8bS3Eno8fb3SlElG8AiWLub',
        'task' => 'Configuration Update',
        'message' => 'RECEIVED',
        'reply' => [
            'success' => true,
            'data_collection_time' => $dataCollectionTime,
            'data_interval' => $dataInterval
        ]
    ];
}

/**
 * Handle sensor data uploads
 */
function handleData($stationId, $message, $pdo) {
    $messageData = json_decode($message, true);
    
    if ($messageData && isset($messageData['params'])) {
        $params = $messageData['params'];
        
        // Extract sensor data
        $temperature = $params['temperature'] ?? null;
        $humidity = $params['humidity'] ?? null;
        $rssi = $params['rssi'] ?? null;
        $batteryVoltage = $params['battery_voltage'] ?? null;
        
        echo "   Data: T={$temperature}°C, H={$humidity}%, RSSI={$rssi}dBm, V={$batteryVoltage}V\n";
        
        // Save to database
        try {
            $insertQuery = $pdo->prepare("
                INSERT INTO sensor_readings 
                (station_id, temperature, humidity, rssi, battery_voltage, reading_time, web_triggered, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), 0, NOW(), NOW())
            ");
            
            $insertQuery->execute([$stationId, $temperature, $humidity, $rssi, $batteryVoltage]);
            echo "   ✅ Data saved to database\n";
            
            // Reset request_update flag after successful data upload
            $updateQuery = $pdo->prepare("UPDATE device_status SET request_update = 0, last_seen = NOW() WHERE station_id = ?");
            $updateQuery->execute([$stationId]);
            
        } catch (PDOException $e) {
            echo "   ⚠️  DB Save Error: " . $e->getMessage() . "\n";
        }
    }
    
    return [
        'station_id' => $stationId,
        'api_key' => 'gVJA4hJ5GiihV9jYud1VebhZ3QZOgLmMZWFRoMyTz8bS3Eno8fb3SlElG8AiWLub',
        'task' => 'Upload Data',
        'message' => 'RECEIVED',
        'reply' => [
            'success' => true
        ]
    ];
}