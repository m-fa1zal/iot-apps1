<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Legacy\ESP32Controller;
use App\Http\Controllers\Api\MqttConfigController;
use App\Http\Controllers\Api\MqttDataController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
});

// ESP32 Device Endpoints (API Token Authentication) - DEPRECATED: Migrating to MQTT
Route::post('/config', [ESP32Controller::class, 'getConfig'])->name('api.legacy.config');
Route::post('/upload', [ESP32Controller::class, 'uploadData'])->name('api.legacy.upload');

// MQTT-based ESP32 Device Endpoints (New Process Flow)
Route::prefix('mqtt')->group(function () {
    // Step 5-6: Heartbeat request/response (HTTP fallback)
    Route::post('/heartbeat/request', [MqttConfigController::class, 'handleHeartbeat'])->name('api.mqtt.heartbeat.request');
    
    // Step 7: Configuration request/response (HTTP fallback)
    Route::post('/config/request', [MqttConfigController::class, 'handleConfigRequest'])->name('api.mqtt.config.request');
    Route::get('/config/get', [MqttConfigController::class, 'getConfig'])->name('api.mqtt.config.get');
    
    // Step 9: Data upload request/response (HTTP fallback)
    Route::post('/data/request', [MqttDataController::class, 'receiveData'])->name('api.mqtt.data.request');
    Route::post('/data/manual-request', [MqttDataController::class, 'requestData'])->name('api.mqtt.data.manual-request');
    Route::get('/data/latest', [MqttDataController::class, 'getLatestData'])->name('api.mqtt.data.latest');
});
