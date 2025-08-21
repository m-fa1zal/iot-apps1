<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking active stations in database...\n\n";

$stations = DB::table('station_information')
    ->where('station_active', true)
    ->limit(5)
    ->get(['station_id', 'station_name', 'station_active']);

if ($stations->count() > 0) {
    echo "Active stations found:\n";
    foreach ($stations as $station) {
        echo "- Station ID: {$station->station_id}\n";
        echo "  Name: {$station->station_name}\n";
        echo "  Active: " . ($station->station_active ? 'Yes' : 'No') . "\n\n";
    }
} else {
    echo "No active stations found!\n";
    
    // Check if any stations exist at all
    $allStations = DB::table('station_information')->count();
    echo "Total stations in database: {$allStations}\n";
}

echo "Use one of these station IDs for testing ESP32 heartbeat.\n";