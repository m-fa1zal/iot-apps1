<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking station KL001 configuration...\n\n";

// Check if station exists
$station = DB::table('station_information')
    ->where('station_id', 'KL001')
    ->first();

if (!$station) {
    echo "❌ Station KL001 not found in database!\n";
    echo "Creating default station configuration...\n";
    
    // Create station
    DB::table('station_information')->insert([
        'station_id' => 'KL001',
        'station_name' => 'ESP32 DHT11 Sensor',
        'location' => 'Test Location',
        'state_id' => 1,
        'district_id' => 1,
        'latitude' => 3.139,
        'longitude' => 101.6869,
        'station_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // Create device status
    DB::table('device_status')->insert([
        'station_id' => 'KL001',
        'status' => 'offline',
        'request_update' => false,
        'last_seen' => null,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // Create device configuration
    DB::table('device_configurations')->insert([
        'station_id' => 'KL001',
        'data_interval' => 3,
        'data_collection_time' => 30,
        'configuration_update' => false,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Station KL001 created successfully!\n";
} else {
    echo "✅ Station KL001 found:\n";
    echo "- Name: {$station->station_name}\n";
    echo "- Active: " . ($station->station_active ? 'Yes' : 'No') . "\n";
}

// Check device status
$status = DB::table('device_status')
    ->where('station_id', 'KL001')
    ->first();

if ($status) {
    echo "\n📊 Device Status:\n";
    echo "- Status: {$status->status}\n";
    echo "- Request Update: " . ($status->request_update ? 'Yes' : 'No') . "\n";
    echo "- Last Seen: " . ($status->last_seen ?: 'Never') . "\n";
}

// Check configuration
$config = DB::table('device_configurations')
    ->where('station_id', 'KL001')
    ->first();

if ($config) {
    echo "\n⚙️ Configuration:\n";
    echo "- Data Interval: {$config->data_interval} minutes\n";
    echo "- Collection Time: {$config->data_collection_time} minutes\n";
    echo "- Config Update: " . ($config->configuration_update ? 'Pending' : 'Complete') . "\n";
}

echo "\n🎯 Ready for ESP32 connection!\n";