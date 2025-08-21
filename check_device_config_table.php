<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking device_configurations table structure...\n\n";

// Get table structure
$columns = DB::select('DESCRIBE device_configurations');

echo "Current columns:\n";
foreach($columns as $col) {
    echo "- {$col->Field} ({$col->Type})" . ($col->Null === 'YES' ? ' - Nullable' : ' - Required') . "\n";
}

// Check if api_key column exists
$hasApiKey = false;
foreach($columns as $col) {
    if ($col->Field === 'api_key') {
        $hasApiKey = true;
        break;
    }
}

echo "\nAPI Key column exists: " . ($hasApiKey ? 'Yes' : 'No') . "\n";

// Show current config for KL001
echo "\nCurrent configuration for KL001:\n";
$config = DB::table('device_configurations')
    ->where('station_id', 'KL001')
    ->first();

if ($config) {
    foreach($config as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
} else {
    echo "No configuration found for KL001\n";
}