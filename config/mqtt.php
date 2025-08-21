<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MQTT Broker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MQTT broker connection used by the IoT device
    | communication system.
    |
    */

    'host' => env('MQTT_HOST', 'localhost'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME', ''),
    'password' => env('MQTT_PASSWORD', ''),
    'client_id' => env('MQTT_CLIENT_ID', 'iot-server-' . \Illuminate\Support\Str::random(8)),

    /*
    |--------------------------------------------------------------------------
    | MQTT Topics
    |--------------------------------------------------------------------------
    |
    | Topic patterns used for device communication
    |
    */

    'topics' => [
        'heartbeat_request' => 'iot/{station_id}/heartbeat/request',
        'heartbeat_response' => 'iot/{station_id}/heartbeat/response',
        'config_request' => 'iot/{station_id}/config/request',
        'config_response' => 'iot/{station_id}/config/response',
        'data_request' => 'iot/{station_id}/data/request',
        'data_response' => 'iot/{station_id}/data/response',
    ],

    /*
    |--------------------------------------------------------------------------
    | MQTT Quality of Service
    |--------------------------------------------------------------------------
    |
    | QoS levels for different message types
    | 0 = At most once delivery
    | 1 = At least once delivery
    | 2 = Exactly once delivery
    |
    */

    'qos' => [
        'heartbeat' => 0,
        'config' => 1,
        'data' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Device Configuration
    |--------------------------------------------------------------------------
    |
    | Default configuration values for devices
    |
    */

    'device_defaults' => [
        'data_interval' => 3, // minutes
        'data_collection_time' => 30, // minutes
        'heartbeat_interval' => 60, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeouts and Retry Settings
    |--------------------------------------------------------------------------
    |
    | Connection and message timeout settings
    |
    */

    'timeouts' => [
        'connection' => 30, // seconds
        'keep_alive' => 60, // seconds
        'message' => 10, // seconds
    ],

    'retries' => [
        'max_attempts' => 3,
        'delay' => 5, // seconds between retries
    ],
];