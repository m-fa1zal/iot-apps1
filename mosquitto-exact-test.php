<?php

require_once 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

echo "Testing exact mosquitto_sub parameters\n";
echo "=====================================\n";

// Exact same parameters as mosquitto_sub
$host = 'maglev.proxy.rlwy.net';
$port = 49225;
$username = 'iot-apps1';
$password = '0wk5cr8jvezzhv2qmqlf3vh1eumc4uek';
$clientId = 'php_test_' . uniqid();

echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $username\n";
echo "Password: " . substr($password, 0, 8) . "...\n";
echo "Client ID: $clientId\n\n";

try {
    // Create client with MQTT 3.1.1 (same as mosquitto_sub default)
    $client = new MqttClient($host, $port, $clientId, MqttClient::MQTT_3_1_1);
    
    // Minimal settings - match mosquitto_sub defaults
    $settings = (new ConnectionSettings())
        ->setKeepAliveInterval(60)
        ->setConnectTimeout(60)
        ->setSocketTimeout(60)
        ->setUseTls(false)
        ->setUsername($username)
        ->setPassword($password);
    
    echo "Connecting...\n";
    $client->connect($settings, true); // Clean session = true (mosquitto_sub default)
    
    echo "✅ SUCCESS! Connected to MQTT broker\n";
    echo "Subscribing to iot/# ...\n";
    
    $client->subscribe('iot/#', function ($topic, $message) {
        echo "[$topic] $message\n";
    }, 0);
    
    echo "Listening for 10 seconds...\n";
    $endTime = time() + 10;
    while (time() < $endTime) {
        $client->loop(1);
    }
    
    $client->disconnect();
    echo "\nDisconnected successfully.\n";
    
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}