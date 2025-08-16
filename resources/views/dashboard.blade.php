@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3" style="background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);">
                    <i class="fas fa-home text-white"></i>
                </div>
                <div>
                    <h1 class="mb-0 fw-bold">Dashboard</h1>
                    <p class="text-muted mb-0">Welcome to your IoT control center</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 justify-content-center">
        <div class="col-lg-4 col-md-6">
            <div class="stats-card">
                <div class="icon-wrapper mx-auto" style="background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);">
                    <i class="fas fa-microchip text-white"></i>
                </div>
                <div class="stats-number" style="color: var(--primary-color);">{{ $totalDevices ?? 0 }}</div>
                <div class="stats-label">Total Devices</div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="stats-card">
                <div class="icon-wrapper mx-auto" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);">
                    <i class="fas fa-wifi text-white"></i>
                </div>
                <div class="stats-number" style="color: var(--accent-color);">{{ $onlineDevices ?? 0 }}</div>
                <div class="stats-label">Online Devices</div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="stats-card">
                <div class="icon-wrapper mx-auto" style="background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
                <div class="stats-number" style="color: var(--warning-color);">{{ $offlineDevices ?? 0 }}</div>
                <div class="stats-label">Offline Devices</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <h3 class="mb-4">Quick Actions</h3>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('devices.index') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-microchip me-2"></i>Manage Devices
                </a>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    This is the legacy dashboard. The main dashboard now shows real-time monitoring.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection