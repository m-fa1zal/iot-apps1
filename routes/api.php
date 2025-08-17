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

// MQTT-based ESP32 Device Endpoints (API Token Authentication)
Route::prefix('mqtt')->group(function () {
    Route::post('/config/request', [MqttConfigController::class, 'requestConfig'])->name('api.mqtt.config.request');
    Route::post('/config/get', [MqttConfigController::class, 'getConfig'])->name('api.mqtt.config.get');
    Route::post('/data/receive', [MqttDataController::class, 'receiveData'])->name('api.mqtt.data.receive');
    Route::post('/data/request', [MqttDataController::class, 'requestData'])->name('api.mqtt.data.request');
});
