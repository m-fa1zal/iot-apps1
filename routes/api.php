<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Legacy\ESP32Controller;
use App\Http\Controllers\Api\MqttController;

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
    Route::post('/heartbeat/request', [MqttController::class, 'handleHeartbeat']);
    Route::post('/config/request', [MqttController::class, 'handleConfigRequest']);
    Route::post('/data/request', [MqttController::class, 'handleDataUpload']);
});
