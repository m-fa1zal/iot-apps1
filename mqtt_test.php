<?php

require_once 'vendor/autoload.php';

use App\Services\MqttService;

$mqtt = new MqttService();
$mqtt->connect();

$testMessage = json_encode([
    'station_id' => 'KL001',
    'success' => true,
    'test' => 'simple_response'
]);

echo "Sending test message to: iot/KL001/heartbeat/response\n";
echo "Message: " . $testMessage . "\n";

$result = $mqtt->publish('iot/KL001/heartbeat/response', $testMessage);
echo "Publish result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

$mqtt->disconnect();