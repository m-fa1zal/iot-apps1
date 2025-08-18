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
    
    // Station Management - accessible by both admin and user (with restrictions)
    Route::get('/stations', [App\Http\Controllers\StationController::class, 'index'])->name('stations.index');
    Route::get('/stations/{stationId}', [App\Http\Controllers\StationController::class, 'show'])->name('stations.show');
    
    // Legacy device routes redirect to stations
    Route::get('/devices', function() { return redirect()->route('stations.index'); });
    Route::get('/devices/{device}', function($device) { return redirect()->route('stations.show', $device); });
    
    // Admin-only station management routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/stations/create', [App\Http\Controllers\StationController::class, 'create'])->name('stations.create');
        Route::post('/stations', [App\Http\Controllers\StationController::class, 'store'])->name('stations.store');
        Route::delete('/stations/{stationId}', [App\Http\Controllers\StationController::class, 'destroy'])->name('stations.destroy');
        Route::put('/stations/{stationId}/update-info', [App\Http\Controllers\StationController::class, 'updateInfo'])->name('stations.update-info');
        Route::put('/stations/{stationId}/update-config', [App\Http\Controllers\StationController::class, 'updateConfig'])->name('stations.update-config');
        Route::post('/stations/{stationId}/regenerate-token', [App\Http\Controllers\StationController::class, 'regenerateToken'])->name('stations.regenerate-token');
        Route::get('/stations/{stationId}/historical-data', [App\Http\Controllers\StationController::class, 'getHistoricalData'])->name('stations.historical-data');
        Route::get('/stations/{stationId}/task-logs', [App\Http\Controllers\StationController::class, 'getTaskLogs'])->name('stations.task-logs');
        Route::get('/stations/{stationId}/export-data', [App\Http\Controllers\StationController::class, 'exportData'])->name('stations.export-data');
    });
    
    Route::get('/api/districts', [App\Http\Controllers\StationController::class, 'getDistricts'])->name('districts.by-state');
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
        
        // API endpoint for user modal
        Route::get('/api/users/{user}', function ($userId) {
            try {
                $user = \App\Models\User::findOrFail($userId);
                return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    });
    
    // API endpoint for station information (accessible by all authenticated users)
    Route::get('/api/stations/{stationId}', function ($stationId) {
        try {
            $station = \App\Models\StationInformation::where('station_id', $stationId)->firstOrFail();
            return response()->json([
                'station_id' => $station->station_id,
                'station_name' => $station->station_name,
                'state_id' => $station->state_id,
                'district_id' => $station->district_id,
                'gps_latitude' => $station->gps_latitude,
                'gps_longitude' => $station->gps_longitude,
                'station_active' => $station->station_active,
                'created_at' => $station->created_at,
                'updated_at' => $station->updated_at,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});
