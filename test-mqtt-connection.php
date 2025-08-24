<?php

require_once 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

echo "===============================\n";
echo "Railway MQTT Connection Test\n";
echo "===============================\n\n";

// MQTT connection details - exact mosquitto_sub parameters
$host = 'maglev.proxy.rlwy.net';
$port = 49225;
$username = 'iot-apps1';
$password = '0wk5cr8jvezzhv2qmqlf3vh1eumc4uek';
$clientId = 'test_client_' . uniqid();

echo "Connection Details:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $username\n";
echo "Password: ***SET***\n";
echo "Client ID: $clientId\n\n";

// Test 1: Basic socket connection
echo "1. Testing basic TCP socket connection...\n";
$socket = @fsockopen($host, $port, $errno, $errstr, 10);
if ($socket) {
    echo "   ✅ TCP socket connection successful\n";
    fclose($socket);
} else {
    echo "   ❌ TCP socket connection failed: $errstr ($errno)\n";
    exit(1);
}

// Test 2: MQTT Client connection
echo "\n2. Testing MQTT client connection...\n";
try {
    $client = new MqttClient($host, $port, $clientId, MqttClient::MQTT_3_1_1);
    
    // Exact same settings as mosquitto_sub
    $connectionSettings = (new ConnectionSettings())
        ->setKeepAliveInterval(60)
        ->setConnectTimeout(60)
        ->setSocketTimeout(60)
        ->setUseTls(false)
        ->setUsername($username)
        ->setPassword($password);
    
    echo "   Connecting with mosquitto_sub compatible settings...\n";
    $client->connect($connectionSettings, true);
    echo "   ✅ MQTT connection successful!\n";
    
    // Test 3: Subscribe to test topic
    echo "\n3. Testing topic subscription...\n";
    $client->subscribe('test/railway', function ($topic, $message) {
        echo "   📨 Received message on $topic: $message\n";
    }, 0);
    
    echo "   ✅ Subscribed to test/railway\n";
    
    // Test 4: Publish test message
    echo "\n4. Testing message publish...\n";
    $client->publish('test/railway', 'Hello from Railway PHP!', 0);
    echo "   ✅ Published test message\n";
    
    // Listen for a few seconds
    echo "\n5. Listening for messages (5 seconds)...\n";
    $endTime = time() + 5;
    while (time() < $endTime) {
        $client->loop(1);
    }
    
    $client->disconnect();
    echo "\n✅ MQTT test completed successfully!\n";
    
} catch (Exception $e) {
    echo "   ❌ MQTT connection failed:\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    
    exit(1);
}

echo "\n===============================\n";
echo "All tests passed! MQTT is working.\n";
echo "===============================\n";