@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);">
                    <i class="fas fa-microchip text-white"></i>
                </div>
                <div>
                    <h1 class="mb-0 fw-bold">Devices</h1>
                    <p class="text-muted mb-0">Manage your IoT devices</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Content -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-microchip me-2"></i>IoT Devices
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center py-5">
                        <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%); width: 80px; height: 80px;">
                            <i class="fas fa-microchip text-white fa-2x"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Devices Found</h4>
                        <p class="text-muted mb-4">You haven't added any IoT devices yet. Start by connecting your first device to the system.</p>
                        
                        @if(Auth::user()->isAdmin())
                        <button class="btn btn-primary-modern btn-modern" disabled>
                            <i class="fas fa-plus me-2"></i>Add New Device
                        </button>
                        @endif
                    </div>
                    
                    <!-- Device stats placeholder -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-muted">0</h3>
                                <small class="text-muted">Total Devices</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success">0</h3>
                                <small class="text-muted">Online</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning">0</h3>
                                <small class="text-muted">Offline</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">0</h3>
                                <small class="text-muted">Maintenance</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection