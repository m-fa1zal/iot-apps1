<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/password/reset', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard - accessible by both admin and user
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'realtime'])->name('dashboard');
    Route::get('/dashboard/legacy', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.legacy');
    Route::get('/dashboard/data', [App\Http\Controllers\DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/station/{id}', [App\Http\Controllers\DashboardController::class, 'getStationDetails'])->name('dashboard.station');
    Route::post('/dashboard/station/{id}/update', [App\Http\Controllers\DashboardController::class, 'requestUpdate'])->name('dashboard.station.update');
    
    // Admin-only dashboard routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/station/{id}/historical', [App\Http\Controllers\DashboardController::class, 'getHistoricalData'])->name('dashboard.station.historical');
        Route::post('/dashboard/simulate', [App\Http\Controllers\DashboardController::class, 'simulateData'])->name('dashboard.simulate');
    });
    
    // Profile - accessible by both admin and user
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    
    // Device Management - accessible by both admin and user (with restrictions)
    Route::get('/devices', [App\Http\Controllers\DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/{device}', [App\Http\Controllers\DeviceController::class, 'show'])->name('devices.show');
    
    // Admin-only device management routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/devices/create', [App\Http\Controllers\DeviceController::class, 'create'])->name('devices.create');
        Route::post('/devices', [App\Http\Controllers\DeviceController::class, 'store'])->name('devices.store');
        Route::get('/devices/{device}/edit', [App\Http\Controllers\DeviceController::class, 'edit'])->name('devices.edit');
        Route::put('/devices/{device}', [App\Http\Controllers\DeviceController::class, 'update'])->name('devices.update');
        Route::delete('/devices/{device}', [App\Http\Controllers\DeviceController::class, 'destroy'])->name('devices.destroy');
        Route::get('/devices/{device}/regenerate-token', [App\Http\Controllers\DeviceController::class, 'regenerateToken'])->name('devices.regenerate-token');
        Route::put('/devices/{device}/update-info', [App\Http\Controllers\DeviceController::class, 'updateInfo'])->name('devices.update-info');
        Route::put('/devices/{device}/update-config', [App\Http\Controllers\DeviceController::class, 'updateConfig'])->name('devices.update-config');
        Route::post('/devices/{device}/generate-token', [App\Http\Controllers\DeviceController::class, 'generateApiToken'])->name('devices.generate-token');
        Route::get('/devices/{device}/historical-data', [App\Http\Controllers\DeviceController::class, 'getHistoricalData'])->name('devices.historical-data');
        Route::get('/devices/{device}/export-data', [App\Http\Controllers\DeviceController::class, 'exportHistoricalData'])->name('devices.export-data');
    });
    
    Route::get('/api/districts', [App\Http\Controllers\DeviceController::class, 'getDistricts'])->name('districts.by-state');
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
    });
});
