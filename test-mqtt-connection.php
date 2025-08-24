<?php

require_once 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

echo "===============================\n";
echo "Railway MQTT Connection Test\n";
echo "===============================\n\n";

// MQTT connection details
$host = $_ENV['MQTT_HOST'] ?? 'maglev.proxy.rlwy.net';
$port = intval($_ENV['MQTT_PORT'] ?? 49225);
$username = $_ENV['MQTT_USERNAME'] ?? 'root';
$password = $_ENV['MQTT_PASSWORD'] ?? '';
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
    $client = new MqttClient($host, $port, $clientId);
    
    // Try TLS connection settings for Railway's MQTT broker
    $connectionSettings = (new ConnectionSettings())
        ->setKeepAliveInterval(60)
        ->setConnectTimeout(30)
        ->setSocketTimeout(30)
        ->setResendTimeout(10)
        ->setUseTls(true)                    // Enable TLS/SSL
        ->setTlsSelfSignedAllowed(true)      // Allow self-signed certificates
        ->setTlsVerifyPeer(false)            // Don't verify peer certificate
        ->setTlsVerifyPeerName(false);       // Don't verify peer name
    
    echo "   TLS/SSL enabled with relaxed certificate verification\n";
    
    if (!empty($username)) {
        echo "   Setting username: '$username'\n";
        echo "   Setting password: '" . substr($password, 0, 5) . "...'\n";
        $connectionSettings
            ->setUsername($username)
            ->setPassword($password);
    }
    
    echo "   Connection settings configured\n";
    
    echo "   Attempting MQTT connection with clean session=true...\n";
    try {
        $client->connect($connectionSettings, true);
        echo "   ✅ MQTT connection successful!\n";
    } catch (Exception $e1) {
        echo "   ❌ Clean session=true failed: " . $e1->getMessage() . "\n";
        echo "   Trying with clean session=false...\n";
        
        // Try without clean session
        $client = new MqttClient($host, $port, $clientId);
        $client->connect($connectionSettings, false);
        echo "   ✅ MQTT connection successful with clean session=false!\n";
    }
    
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