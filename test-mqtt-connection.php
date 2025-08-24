<?php

require_once 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

echo "===============================\n";
echo "Railway MQTT Connection Test\n";
echo "===============================\n\n";

// MQTT connection details - Railway Mosquitto template
$host = 'maglev.proxy.rlwy.net';
$port = 49225;
$username = $_ENV['MOSQUITTO_USERNAME'] ?? '';
$password = $_ENV['MOSQUITTO_PASSWORD'] ?? '';

// Use correct Railway credentials  
if (empty($username)) {
    echo "Using correct Railway credentials...\n";
    $username = 'iot-apps1';
    $password = '0wk5cr8jvezzhv2qmqlf3vh1eumc4uek';
}
$clientId = 'test_client_' . uniqid();

echo "Connection Details:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? 'NOT SET' : '***SET***') . "\n";
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
    
    // Only try without TLS (matching disabled TLS in MqttService.php)
    $tlsConfigs = [
        'no_tls' => false
    ];
    
    $connectionSettings = null;
    $success = false;
    
    foreach ($tlsConfigs as $configName => $useTls) {
        echo "   Trying connection: $configName...\n";
        
        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(30)
            ->setSocketTimeout(30)
            ->setResendTimeout(10)
            ->setUseTls($useTls);
            
        if ($useTls) {
            $connectionSettings
                ->setTlsVerifyPeer(false)
                ->setTlsVerifyPeerName(false)
                ->setTlsSelfSignedAllowed(true);
        }
        
        echo "   TLS: " . ($useTls ? 'enabled' : 'disabled') . "\n";
        
        if (!empty($username)) {
            echo "   Setting auth: username='$username'\n";
            $connectionSettings
                ->setUsername($username)
                ->setPassword($password);
        } else {
            echo "   Using anonymous connection\n";
        }
        
        try {
            $testClient = new MqttClient($host, $port, $clientId . '_' . $configName, MqttClient::MQTT_3_1_1);
            $testClient->connect($connectionSettings, true);
            echo "   ✅ Connection successful with $configName!\n";
            $client = $testClient;
            $success = true;
            break;
        } catch (Exception $e) {
            echo "   ❌ $configName failed: " . $e->getMessage() . "\n";
        }
    }
    
    if (!$success) {
        throw new Exception("All connection attempts failed");
    }
    
    echo "   Using username: '$username'\n";
    echo "   Using password: '" . substr($password, 0, 5) . "...'\n";
    
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
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (method_exists($e, 'getPrevious') && $e->getPrevious()) {
        $prev = $e->getPrevious();
        echo "   Previous: " . $prev->getMessage() . "\n";
    }
    
    echo "\n   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    
    exit(1);
}

echo "\n===============================\n";
echo "All tests passed! MQTT is working.\n";
echo "===============================\n";