@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);">
                        <i class="fas fa-broadcast-tower text-white"></i>
                    </div>
                    <div>
                        <h1 class="mb-0 fw-bold">Station Management</h1>
                        <p class="text-muted mb-0">Manage IoT monitoring stations and MQTT devices</p>
                    </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card-modern mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('stations.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Station name or ID...">
                    </div>
                    <div class="col-md-2">
                        <label for="state_id" class="form-label">State</label>
                        <select class="form-select" id="state_id" name="state_id">
                            <option value="">All States</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="district_id" class="form-label">District</label>
                        <select class="form-select" id="district_id" name="district_id">
                            <option value="">All Districts</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                                    {{ $district->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                        <a href="{{ route('stations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stations Table -->
    <div class="card-modern">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Stations List</h5>
                @if(Auth::user()->isAdmin())
                    <button type="button" class="btn btn-primary" onclick="openCreateStationModal()">
                        <i class="fas fa-plus me-2"></i>New Station
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($stations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Station Info</th>
                                <th>Location</th>
                                <th>Device Status</th>
                                <th>Last Seen</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stations as $station)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="station-icon me-3">
                                            <i class="fas fa-broadcast-tower text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-semibold">{{ $station->station_name }}</h6>
                                            <small class="text-muted">ID: {{ $station->station_id }}</small>
                                            @if($station->mac_address)
                                                <br><small class="text-muted">MAC: {{ $station->mac_address }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $station->state_name }}</strong>
                                        <br><small class="text-muted">{{ $station->district_name }}</small>
                                        @if($station->address)
                                            <br><small class="text-muted">{{ Str::limit($station->address, 30) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($station->status == 'online')
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle me-1"></i>Online
                                        </span>
                                    @elseif($station->status == 'offline')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-circle me-1"></i>Offline
                                        </span>
                                    @elseif($station->status == 'maintenance')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-tools me-1"></i>Maintenance
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-question me-1"></i>Unknown
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($station->last_seen)
                                        <div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($station->last_seen)->diffForHumans() }}</small>
                                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($station->last_seen)->format('M j, Y H:i') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 align-items-center actions-container">
                                        <!-- Station Information Button -->
                                        <button type="button" class="btn btn-outline-info btn-icon" 
                                                onclick="showStationInfo('{{ $station->station_id }}')" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Station Information">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        
                                        <!-- Device Configuration Button -->
                                        <button type="button" class="btn btn-outline-warning btn-icon" 
                                                onclick="showDeviceConfig('{{ $station->station_id }}')" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Device Configuration">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        
                                        <!-- Historical Data Button -->
                                        <button type="button" class="btn btn-outline-primary btn-icon" 
                                                onclick="showHistoricalData('{{ $station->station_id }}')" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Historical Data">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                        
                                        <!-- Task Log Button -->
                                        <button type="button" class="btn btn-outline-secondary btn-icon" 
                                                onclick="showTaskLog('{{ $station->station_id }}')" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="MQTT Task Log">
                                            <i class="fas fa-list-alt"></i>
                                        </button>
                                        
                                        <!-- Deactivate Button (Admin Only) -->
                                        @if(Auth::user()->isAdmin())
                                            <form method="POST" action="{{ route('stations.destroy', $station->station_id) }}" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this station?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-icon" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="Deactivate Station">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $stations->firstItem() ?? 0 }} to {{ $stations->lastItem() ?? 0 }} of {{ $stations->total() }} results
                        </div>
                        {{ $stations->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="icon-wrapper mx-auto mb-3" style="width: 80px; height: 80px; background: var(--light-gray);">
                        <i class="fas fa-broadcast-tower text-muted fs-2"></i>
                    </div>
                    <h5 class="text-muted mb-2">No Stations Found</h5>
                    <p class="text-muted mb-3">There are no monitoring stations matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>




<!-- Create New Station Modal -->
<div class="modal fade" id="createStationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Create New Station
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createStationForm">
                <div class="modal-body">
                    <!-- Step 1: Station Information -->
                    <div id="step1" class="step-content">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Station Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_station_name" class="form-label">Station Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_station_name" name="station_name" required>
                                    <div class="invalid-feedback" id="create_station_name_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_state_id" class="form-label">State <span class="text-danger">*</span></label>
                                    <select class="form-select" id="create_state_id" name="state_id" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="create_state_id_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_address" class="form-label">Address</label>
                                    <textarea class="form-control" id="create_address" name="address" rows="3" placeholder="Enter detailed address..."></textarea>
                                    <div class="invalid-feedback" id="create_address_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_district_id" class="form-label">District <span class="text-danger">*</span></label>
                                    <select class="form-select" id="create_district_id" name="district_id" required disabled>
                                        <option value="">Select District</option>
                                    </select>
                                    <div class="invalid-feedback" id="create_district_id_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i>Station Location</h6>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_gps_latitude" class="form-label">GPS Latitude</label>
                                    <input type="number" class="form-control" id="create_gps_latitude" name="gps_latitude" 
                                           step="0.000001" min="-90" max="90" placeholder="e.g., 3.1390">
                                    <div class="invalid-feedback" id="create_gps_latitude_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_gps_longitude" class="form-label">GPS Longitude</label>
                                    <input type="number" class="form-control" id="create_gps_longitude" name="gps_longitude" 
                                           step="0.000001" min="-180" max="180" placeholder="e.g., 101.6869">
                                    <div class="invalid-feedback" id="create_gps_longitude_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Device Configuration -->
                    <div id="step2" class="step-content" style="display: none;">
                        <h6 class="mb-3"><i class="fas fa-cog me-2"></i>Device Configuration</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="create_mac_address" class="form-label">MAC Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_mac_address" name="mac_address" 
                                           placeholder="AA:BB:CC:DD:EE:FF" required>
                                    <div class="form-text">Format: AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF</div>
                                    <div class="invalid-feedback" id="create_mac_address_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_data_interval" class="form-label">Data Interval (minutes) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="create_data_interval" name="data_interval" 
                                           value="30" min="1" max="60" required>
                                    <div class="form-text">How often the device collects sensor readings (1-60 minutes)</div>
                                    <div class="invalid-feedback" id="create_data_interval_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_data_collection_time" class="form-label">Data Collection Time (minutes) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="create_data_collection_time" name="data_collection_time" 
                                           value="60" min="10" max="300" required>
                                    <div class="form-text">Duration for each data collection session (10-300 minutes)</div>
                                    <div class="invalid-feedback" id="create_data_collection_time_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Station ID and API Token will be automatically generated after creation
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="nextStepBtn">
                        Next <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="prevStepBtn" style="display: none;">
                        <i class="fas fa-arrow-left me-1"></i> Previous
                    </button>
                    <button type="submit" class="btn btn-success" id="createStationBtn" style="display: none;">
                        <i class="fas fa-save me-1"></i>Create New Station
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Device Configuration Modal -->
<div class="modal fade" id="deviceConfigModal" tabindex="-1" aria-labelledby="deviceConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceConfigModalLabel">
                    <i class="fas fa-cog me-2"></i>Device Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deviceConfigForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="config_station_id" class="form-label">Station ID</label>
                                <input type="text" class="form-control readonly-field" id="config_station_id" name="station_id" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="config_api_token" class="form-label">API Token</label>
                        <div class="input-group">
                            <input type="text" class="form-control readonly-field" id="config_api_token" name="api_token" readonly>
                            <button type="button" class="btn btn-outline-secondary" id="copyTokenBtn" onclick="copyApiToken()" data-bs-toggle="tooltip" title="Copy API Token">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="regenerateTokenBtn" onclick="regenerateApiToken()" data-bs-toggle="tooltip" title="Regenerate API Token" style="display: none;">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="config_data_collection_time" class="form-label">Data Collection Time (minutes)</label>
                                <input type="number" class="form-control" id="config_data_collection_time" name="data_collection_time" min="1" max="60">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="config_data_interval" class="form-label">Data Interval (minutes)</label>
                                <input type="number" class="form-control" id="config_data_interval" name="data_interval" min="1" max="60">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="config_mac_address" class="form-label">MAC Address</label>
                                <input type="text" class="form-control" id="config_mac_address" name="mac_address" maxlength="17" placeholder="AA:BB:CC:DD:EE:FF">
                            </div>
                        </div>
                    </div>
                </form>
                <div class="mt-3">
                    <small class="text-muted" id="config_last_updated_text">Last Updated: N/A</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editConfigBtn">
                    <i class="fas fa-edit me-2"></i>Edit Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Station Information Modal -->
<div class="modal fade" id="stationInfoModal" tabindex="-1" aria-labelledby="stationInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stationInfoModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Station Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Success Alert -->
                <div class="alert alert-success alert-dismissible fade" role="alert" id="stationSuccessAlert" style="display: none;">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="stationSuccessMessage">Station information updated successfully!</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                
                <form id="stationInfoForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_station_name" class="form-label">Station Name</label>
                                <input type="text" class="form-control" id="view_station_name" name="station_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_station_id" class="form-label">Station ID</label>
                                <input type="text" class="form-control readonly-field" id="view_station_id" name="station_id" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_state_id" class="form-label">State</label>
                                <select class="form-select" id="view_state_id" name="state_id">
                                    <option value="">Select State</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_district_id" class="form-label">District</label>
                                <select class="form-select" id="view_district_id" name="district_id">
                                    <option value="">Select District</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_status" class="form-label">Current Status</label>
                                <input type="text" class="form-control" id="view_status" name="status" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_last_seen" class="form-label">Last Seen</label>
                                <input type="text" class="form-control" id="view_last_seen" name="last_seen" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="view_address" class="form-label">Address</label>
                        <textarea class="form-control" id="view_address" name="address" rows="2" readonly></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_gps_latitude" class="form-label">GPS Latitude</label>
                                <input type="number" class="form-control" id="view_gps_latitude" name="gps_latitude" step="0.000001" min="-90" max="90" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_gps_longitude" class="form-label">GPS Longitude</label>
                                <input type="number" class="form-control" id="view_gps_longitude" name="gps_longitude" step="0.000001" min="-180" max="180" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_station_active" class="form-label">Station Active</label>
                                <input type="text" class="form-control" id="view_station_active" name="station_active" readonly>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="mt-3">
                    <small class="text-muted" id="station_updated_at_text">Updated At: N/A</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editStationBtn">
                    <i class="fas fa-edit me-2"></i>Edit Information
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MQTT Task Log Modal -->
<div class="modal fade" id="mqttTaskLogModal" tabindex="-1" aria-labelledby="mqttTaskLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="mqttTaskLogModalLabel">
                    <i class="fas fa-list-alt me-2"></i>MQTT Task Log
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Station Information Header -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>Station Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Station ID:</strong></td>
                                        <td id="log_station_id">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Station Name:</strong></td>
                                        <td id="log_station_name">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Filtering -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Date Filtering</h6>
                    </div>
                    <div class="card-body">
                        <form id="logDateFilterForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="log_from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="log_from_date" name="from_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="log_to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="log_to_date" name="to_date">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary me-2" onclick="filterTaskLogData()">
                                        <i class="fas fa-search me-1"></i>Filter Data
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearLogDateFilter()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MQTT Task Log Table -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-table me-2"></i>MQTT Task Logs <span id="log_count_badge" class="badge bg-dark text-light ms-2">0 records</span></h6>
                            <button type="button" class="btn btn-dark btn-sm" onclick="exportTaskLogData()">
                                <i class="fas fa-download me-1"></i>Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="taskLogTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Received At</th>
                                        <th>Station ID</th>
                                        <th>Task Type</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Response Time (ms)</th>
                                    </tr>
                                </thead>
                                <tbody id="taskLogBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-clock mb-2" style="font-size: 2rem;"></i>
                                                <h6 class="mb-1">Ready to load logs</h6>
                                                <small class="text-muted">Select a station to view MQTT task logs</small>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing <span id="log_showing_from">0</span> to <span id="log_showing_to">0</span> of <span id="log_total_records">0</span> records
                            </div>
                            <div class="btn-group" role="group" id="logPaginationControls">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="previousLogPage()" id="logPrevBtn" disabled>
                                    <i class="fas fa-chevron-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="nextLogPage()" id="logNextBtn" disabled>
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Historical Data Modal -->
<div class="modal fade" id="historicalDataModal" tabindex="-1" aria-labelledby="historicalDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="historicalDataModalLabel">
                    <i class="fas fa-chart-line me-2"></i>Historical Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Station Information Header -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>Station Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Station ID:</strong></td>
                                        <td id="hist_station_id">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Station Name:</strong></td>
                                        <td id="hist_station_name">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Filtering -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Date Filtering</h6>
                    </div>
                    <div class="card-body">
                        <form id="dateFilterForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="from_date" name="from_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="to_date" name="to_date">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary me-2" onclick="filterHistoricalData()">
                                        <i class="fas fa-search me-1"></i>Filter Data
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearDateFilter()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historical Data Table -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Sensor Readings <span id="data_count_badge" class="badge bg-light text-dark ms-2">0 records</span></h6>
                            <button type="button" class="btn btn-light btn-sm" onclick="exportHistoricalData()">
                                <i class="fas fa-download me-1"></i>Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="historicalDataTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Reading Time</th>
                                        <th>Station ID</th>
                                        <th>Humidity (%)</th>
                                        <th>Temperature (°C)</th>
                                        <th>RSSI (dBm)</th>
                                        <th>Battery Voltage (V)</th>
                                    </tr>
                                </thead>
                                <tbody id="historicalDataBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-clock mb-2" style="font-size: 2rem;"></i>
                                                <h6 class="mb-1">Ready to load data</h6>
                                                <small class="text-muted">Select a station to view historical sensor readings</small>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing <span id="showing_from">0</span> to <span id="showing_to">0</span> of <span id="total_records">0</span> records
                            </div>
                            <div class="btn-group" role="group" id="paginationControls">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="previousPage()" id="prevBtn" disabled>
                                    <i class="fas fa-chevron-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="nextPage()" id="nextBtn" disabled>
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Validation styles for create station modal */
.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    background-color: #fff5f5;
}

.form-control.is-invalid:focus,
.form-select.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-control.is-valid,
.form-select.is-valid {
    border-color: #198754;
    background-color: #f8fff8;
}

.form-control.is-valid:focus,
.form-select.is-valid:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.valid-feedback {
    display: block;
    color: #198754;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
.btn-icon {
    padding: 0.25rem;
    font-size: 0.8rem;
    line-height: 1;
    border-radius: 0.25rem;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease-in-out;
    flex-shrink: 0;
}

.btn-icon:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.gap-1 {
    gap: 0.2rem !important;
}

/* Ensure buttons stay in one line */
.actions-container {
    white-space: nowrap;
    overflow: visible;
}

/* Modal styling */
.info-group label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.info-group p {
    font-size: 0.95rem;
    color: #333;
}

.modal-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.modal-title {
    color: #495057;
    font-weight: 600;
}

/* Custom Tab Styling - Force Override Bootstrap */
#createStationTabs .nav-link.custom-tab {
    height: 64px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: #ffffff !important;
    color: #000000 !important;
    border: 1px solid #dee2e6 !important;
    transition: all 0.3s ease !important;
}

#createStationTabs .nav-link.custom-tab.active {
    background-color: #000000 !important;
    color: #ffffff !important;
    border-color: #000000 !important;
}

#createStationTabs .nav-link.custom-tab:hover:not(.active) {
    background-color: #f8f9fa !important;
    color: #000000 !important;
}

/* Ensure Bootstrap nav-tabs styles don't override our custom styles */
#createStationTabs .nav-tabs .nav-link.custom-tab.active,
#createStationTabs .nav-tabs .nav-link.custom-tab.active:focus,
#createStationTabs .nav-tabs .nav-link.custom-tab.active:hover {
    background-color: #000000 !important;
    color: #ffffff !important;
    border-color: #000000 !important;
}

/* Reduce spacing in Device Configuration tab */
#device-config-pane {
    padding-top: 1rem !important;
}



/* Readonly field styling - pink background for non-editable fields in edit mode */
#stationInfoModal input.readonly-field,
#stationInfoModal textarea.readonly-field,
#stationInfoModal select.readonly-field,
#deviceConfigModal input.readonly-field,
#deviceConfigModal textarea.readonly-field,
#deviceConfigModal select.readonly-field,
.modal input.readonly-field,
.modal textarea.readonly-field,
.modal select.readonly-field {
    background-color: #fce7f3 !important;
    color: #be185d !important;
    cursor: not-allowed !important;
    border-color: #ced4da !important;
}

#stationInfoModal input.readonly-field:focus,
#stationInfoModal textarea.readonly-field:focus,
#stationInfoModal select.readonly-field:focus,
#deviceConfigModal input.readonly-field:focus,
#deviceConfigModal textarea.readonly-field:focus,
#deviceConfigModal select.readonly-field:focus,
.modal input.readonly-field:focus,
.modal textarea.readonly-field:focus,
.modal select.readonly-field:focus {
    background-color: #fce7f3 !important;
    color: #be185d !important;
    box-shadow: 0 0 0 0.2rem rgba(236, 72, 153, 0.15) !important;
    border-color: #f9a8d4 !important;
}

/* Ensure normal disabled fields don't get grey background unless explicitly marked */
select:disabled:not(.readonly-field),
input:disabled:not(.readonly-field) {
    background-color: #ffffff !important;
}

/* Remove underlines from station information tables */
#historicalDataModal .table td,
#mqttTaskLogModal .table td {
    text-decoration: none !important;
}

#hist_station_id,
#hist_station_name,
#log_station_id,
#log_station_name {
    text-decoration: none !important;
}

/* Modern Tab Container */
.modern-tab-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 2rem 2rem 1rem 2rem;
    position: relative;
    overflow: hidden;
}

.modern-tab-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0d6efd, #6f42c1, #d63384, #dc3545, #fd7e14, #ffc107, #198754, #20c997);
    background-size: 400% 400%;
    animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Progress Line */
.progress-line {
    position: absolute;
    top: 50%;
    left: 2rem;
    right: 2rem;
    height: 3px;
    background: #e9ecef;
    border-radius: 2px;
    transform: translateY(-50%);
    z-index: 1;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #6f42c1);
    border-radius: 2px;
    width: 0%;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.progress-bar::after {
    content: '';
    position: absolute;
    right: -2px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background: #0d6efd;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
}

/* Modern Tabs */
.modern-tabs {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}

.tab-item {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
    min-width: 300px;
    max-width: 350px;
}

.tab-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(13, 110, 253, 0.1), transparent);
    transition: left 0.6s ease;
}

.tab-item:hover::before {
    left: 100%;
}

.tab-item:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    border-color: rgba(13, 110, 253, 0.3);
}

.tab-item.active {
    background: linear-gradient(135deg, #0d6efd 0%, #6f42c1 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(13, 110, 253, 0.3);
    border-color: #0d6efd;
}

.tab-item.completed {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    color: white;
    box-shadow: 0 6px 25px rgba(25, 135, 84, 0.3);
}

.tab-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(13, 110, 253, 0.1);
    margin-right: 1rem;
    transition: all 0.3s ease;
    position: relative;
}

.tab-item.active .tab-icon,
.tab-item.completed .tab-icon {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.tab-icon i {
    font-size: 1.5rem;
    color: #0d6efd;
    transition: all 0.3s ease;
}

.tab-item.active .tab-icon i,
.tab-item.completed .tab-icon i {
    color: white;
    transform: scale(1.1);
}

.tab-content-text {
    flex: 1;
}

.tab-step {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.7;
    margin-bottom: 0.25rem;
}

.tab-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.tab-subtitle {
    font-size: 0.875rem;
    opacity: 0.8;
    line-height: 1.3;
}

.tab-check {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e9ecef;
    margin-left: 1rem;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.tab-item.completed .tab-check {
    opacity: 1;
    transform: scale(1);
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.tab-check i {
    color: white;
    font-size: 0.875rem;
}

/* Tab Connector */
.tab-connector {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex: 1;
    max-width: 120px;
    min-width: 80px;
}

.connector-line {
    width: 100%;
    height: 2px;
    background: #e9ecef;
    position: relative;
    overflow: hidden;
}

.connector-line::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, #0d6efd, transparent);
    animation: connectorPulse 2s ease-in-out infinite;
}

@keyframes connectorPulse {
    0%, 100% { left: -100%; }
    50% { left: 100%; }
}

.connector-arrow {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: white;
    border: 3px solid #e9ecef;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.connector-arrow i {
    color: #6c757d;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.tab-item.active ~ .tab-connector .connector-arrow {
    border-color: #0d6efd;
    background: #0d6efd;
    transform: scale(1.1);
}

.tab-item.active ~ .tab-connector .connector-arrow i {
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .modern-tabs {
        flex-direction: column;
        gap: 2rem;
    }
    
    .tab-connector {
        transform: rotate(90deg);
        max-width: 60px;
        min-width: 60px;
    }
    
    .tab-item {
        min-width: 280px;
        max-width: 100%;
    }
    
    .progress-line {
        display: none;
    }
}


/* Compact Tab Container */
.compact-tab-container {
    background: #ffffff;
    padding: 0;
    width: 100%;
}

/* Progress Container */
.progress-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    position: relative;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.progress-step.active .step-circle {
    background: #0d6efd;
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

.progress-step.completed .step-circle {
    background: #198754;
    border-color: #198754;
    box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.2);
}

.step-number {
    font-weight: 700;
    color: #6c757d;
    font-size: 1rem;
}

.progress-step.active .step-number {
    color: white;
}

.step-check {
    display: none;
    color: white;
    font-size: 1.2rem;
}

.progress-step.completed .step-number {
    display: none;
}

.progress-step.completed .step-check {
    display: block;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
    text-align: center;
}

.progress-step.active .step-label {
    color: #0d6efd;
}

.progress-step.completed .step-label {
    color: #198754;
}

.progress-line {
    position: absolute;
    top: 25px;
    left: 50%;
    right: 50%;
    height: 3px;
    background: #e9ecef;
    border-radius: 2px;
    transform: translateX(-50%);
    width: 150px;
    z-index: 1;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #198754);
    border-radius: 2px;
    width: 0%;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Laravel Telescope Style Tabs - 50% each */
.compact-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 0;
    margin: 0;
    width: 100%;
}

.tab-button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 16px !important;
    border: none;
    background: #e9ecef;
    color: #6c757d;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s ease;
    position: relative;
    border-radius: 0;
    flex: 1;
    width: 50%;
    gap: 8px;
    min-height: 64px !important;
}

.tab-button:not(:last-child) {
    border-right: 1px solid #dee2e6;
}

.tab-button:hover {
    background: #dee2e6;
    color: #495057;
}

.tab-button.active {
    background: #dc3545;
    color: #ffffff;
}

.tab-button.completed {
    background: #28a745;
    color: #ffffff;
}

.tab-icon {
    font-size: 1rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tab-text {
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.2;
}

/* Responsive adjustments for Laravel Telescope style tabs */
@media (max-width: 768px) {
    .tab-button {
        padding: 8px 12px;
        font-size: 0.8rem;
        gap: 6px;
    }
    
    .tab-text {
        font-size: 0.8rem;
    }
    
    .tab-icon {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .tab-button {
        padding: 6px 8px;
        font-size: 0.75rem;
        gap: 4px;
        flex-direction: column;
    }
    
    .tab-text {
        font-size: 0.7rem;
        text-align: center;
        line-height: 1.1;
    }
    
    .tab-icon {
        font-size: 0.85rem;
        margin-bottom: 2px;
    }
}

@media (max-width: 480px) {
    .tab-button {
        padding: 8px 4px;
    }
    
    .tab-text {
        display: none;
    }
    
    .tab-icon {
        font-size: 1.2rem;
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

});


// Global variables for modals
let currentStationId = null;

// Station Information Modal
function showStationInfo(stationId) {
    currentStationId = stationId;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('stationInfoModal'));
    modal.show();
    
    // Load station information
    loadStationInfo(stationId);
}

function loadStationInfo(stationId) {
    // Hide any success alerts
    hideStationSuccessAlert();
    
    // Set modal to view mode
    setModalMode('view');
    
    // Load station data and populate form fields
    populateStationForm(stationId);
}

function showStationSuccessAlert(message) {
    const alert = document.getElementById('stationSuccessAlert');
    const messageSpan = document.getElementById('stationSuccessMessage');
    
    messageSpan.textContent = message;
    alert.style.display = 'block';
    alert.classList.add('show');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideStationSuccessAlert();
    }, 5000);
}

function hideStationSuccessAlert() {
    const alert = document.getElementById('stationSuccessAlert');
    alert.style.display = 'none';
    alert.classList.remove('show');
}

function setModalMode(mode) {
    console.log('Setting modal mode to:', mode);
    const form = document.getElementById('stationInfoForm');
    const editBtn = document.getElementById('editStationBtn');
    
    // Hide success alert when switching modes
    if (mode === 'edit') {
        hideStationSuccessAlert();
    }
    
    if (mode === 'view') {
        // Set all form fields to readonly/disabled - NO highlighting in view mode
        form.querySelectorAll('input').forEach(input => {
            input.readOnly = true;
            input.classList.remove('readonly-field');
        });
        form.querySelectorAll('select').forEach(select => {
            select.disabled = true;
            select.classList.remove('readonly-field');
        });
        form.querySelectorAll('textarea').forEach(textarea => {
            textarea.readOnly = true;
            textarea.classList.remove('readonly-field');
        });
        
        // Show edit button
        editBtn.style.display = 'block';
        editBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Edit Information';
        editBtn.onclick = () => setModalMode('edit');
    } else if (mode === 'edit') {
        // Enable form fields except status and read-only fields
        // ONLY highlight fields that cannot be edited in edit mode
        form.querySelectorAll('input').forEach(input => {
            if (input.name !== 'status' && input.name !== 'station_id' && input.name !== 'last_seen' && input.name !== 'station_active') {
                input.readOnly = false;
                input.classList.remove('readonly-field');
                console.log('Made editable:', input.name);
            } else {
                input.readOnly = true;
                input.classList.add('readonly-field');
                console.log('Made readonly (pink highlight):', input.name);
            }
        });
        form.querySelectorAll('select').forEach(select => {
            // State and district should be editable, no highlighting
            select.disabled = false;
            select.classList.remove('readonly-field');
        });
        form.querySelectorAll('textarea').forEach(textarea => {
            // Address should be editable, no highlighting
            textarea.readOnly = false;
            textarea.classList.remove('readonly-field');
        });
        
        // Change button to save
        editBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
        editBtn.onclick = saveStationInfoInline;
    }
}

function populateStationForm(stationId) {
    // Fetch station data from backend
    fetch(`/stations/${stationId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch station data');
            }
            return response.json();
        })
        .then(station => {
            // Populate form fields with fetched data
            document.getElementById('view_station_name').value = station.station_name || '';
            document.getElementById('view_station_id').value = station.station_id || '';
            document.getElementById('view_status').value = station.status || 'offline';
            document.getElementById('view_last_seen').value = station.last_seen ? new Date(station.last_seen).toLocaleString() : 'Never';
            document.getElementById('view_address').value = station.address || '';
            document.getElementById('view_station_active').value = station.station_active ? 'Active' : 'Inactive';
            document.getElementById('view_gps_latitude').value = station.gps_latitude || '';
            document.getElementById('view_gps_longitude').value = station.gps_longitude || '';
            
            // Update updated at text at bottom
            const updatedAtText = station.updated_at ? new Date(station.updated_at).toLocaleString() : 'N/A';
            document.getElementById('station_updated_at_text').textContent = `Updated At: ${updatedAtText}`;
            
            // Set state
            const stateSelect = document.getElementById('view_state_id');
            if (station.state_id) {
                stateSelect.value = station.state_id;
                
                // Load districts for this state
                loadDistrictsForState(station.state_id, station.district_id);
            }
        })
        .catch(error => {
            console.error('Error loading station data:', error);
            
            // Fallback to parsing from DOM if API fails
            populateFromDOM(stationId);
        });
}

function populateFromDOM(stationId) {
    // Fallback method - parse data from the current page DOM
    const stationRow = document.querySelector(`button[onclick="showStationInfo('${stationId}')"]`).closest('tr');
    const stationName = stationRow.querySelector('h6').textContent;
    const stationIdText = stationRow.querySelector('small').textContent.replace('ID: ', '');
    const status = stationRow.querySelector('.badge').textContent.trim();
    const lastSeenText = stationRow.querySelectorAll('td')[3].textContent.trim();
    
    // Populate basic fields
    document.getElementById('view_station_name').value = stationName;
    document.getElementById('view_station_id').value = stationIdText;
    document.getElementById('view_status').value = status;
    document.getElementById('view_last_seen').value = lastSeenText;
    document.getElementById('view_station_active').value = 'Active';
    
    // Update updated at text at bottom (fallback)
    document.getElementById('station_updated_at_text').textContent = 'Updated At: N/A';
}

function loadDistrictsForState(stateId, selectedDistrictId = null) {
    const districtSelect = document.getElementById('view_district_id');
    
    // Clear existing options
    districtSelect.innerHTML = '<option value="">Select District</option>';
    
    if (stateId) {
        fetch(`/api/districts?state_id=${stateId}`)
            .then(response => response.json())
            .then(districts => {
                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    if (selectedDistrictId && district.id == selectedDistrictId) {
                        option.selected = true;
                    }
                    districtSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading districts:', error));
    }
}

function saveStationInfoInline() {
    const form = document.getElementById('stationInfoForm');
    const formData = new FormData(form);
    const allData = Object.fromEntries(formData);
    
    // Filter data to only include expected fields
    const data = {
        station_name: allData.station_name,
        state_id: allData.state_id,
        district_id: allData.district_id,
        address: allData.address,
        gps_latitude: allData.gps_latitude,
        gps_longitude: allData.gps_longitude
    };
    
    console.log('Sending data:', data);
    
    // Show loading state
    const editBtn = document.getElementById('editStationBtn');
    const originalText = editBtn.innerHTML;
    editBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    editBtn.disabled = true;
    
    // Send update request
    fetch(`/stations/${currentStationId}/update-info`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json().then(data => ({ status: response.status, data }));
    })
    .then(({ status, data }) => {
        console.log('Response data:', data);
        
        if (data.success) {
            // Close modal and return to step 1 (station list view)
            const modal = bootstrap.Modal.getInstance(document.getElementById('stationInfoModal'));
            modal.hide();
            
            // Show success message in main page (optional)
            // You can add a toast or alert here if needed
            
            // Reload page to reflect changes in the station list
            location.reload();
        } else {
            let errorMessage = 'Unknown error';
            
            if (data.message) {
                errorMessage = data.message;
            } else if (data.errors) {
                const errors = Object.values(data.errors).flat();
                errorMessage = errors.join(', ');
            }
            
            console.error('Update failed:', data);
            alert('Error updating station: ' + errorMessage);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error updating station. Please check console for details.');
    })
    .finally(() => {
        // Reset button
        editBtn.innerHTML = originalText;
        editBtn.disabled = false;
    });
}

// Handle state change in view modal (for edit mode)
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('view_state_id').addEventListener('change', function() {
        const stateId = this.value;
        const districtSelect = document.getElementById('view_district_id');
        
        // Clear districts
        districtSelect.innerHTML = '<option value="">Select District</option>';
        
        if (stateId) {
            fetch(`/api/districts?state_id=${stateId}`)
                .then(response => response.json())
                .then(districts => {
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading districts:', error));
        }
    });
});

// Device Configuration Modal
let currentConfigStationId = null;

function showDeviceConfig(stationId) {
    currentConfigStationId = stationId;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('deviceConfigModal'));
    modal.show();
    
    // Load device configuration
    loadDeviceConfig(stationId);
}

function loadDeviceConfig(stationId) {
    // Set modal to view mode initially
    setConfigModalMode('view');
    
    // Load device configuration data
    populateDeviceConfigForm(stationId);
}

function setConfigModalMode(mode) {
    console.log('Setting device config modal mode to:', mode);
    const form = document.getElementById('deviceConfigForm');
    const editBtn = document.getElementById('editConfigBtn');
    const regenerateBtn = document.getElementById('regenerateTokenBtn');
    
    if (mode === 'view') {
        // Set all form fields to readonly/disabled - NO highlighting in view mode
        form.querySelectorAll('input').forEach(input => {
            input.readOnly = true;
            input.classList.remove('readonly-field');
        });
        
        // Hide regenerate token button in view mode
        regenerateBtn.style.display = 'none';
        
        // Show edit button
        editBtn.style.display = 'block';
        editBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Edit Configuration';
        editBtn.onclick = () => setConfigModalMode('edit');
    } else if (mode === 'edit') {
        // Enable form fields except readonly fields
        // ONLY highlight fields that cannot be edited in edit mode
        form.querySelectorAll('input').forEach(input => {
            if (input.name === 'mac_address' || input.name === 'data_interval' || input.name === 'data_collection_time') {
                input.readOnly = false;
                input.classList.remove('readonly-field');
                console.log('Made editable:', input.name);
            } else {
                input.readOnly = true;
                input.classList.add('readonly-field');
                console.log('Made readonly (pink highlight):', input.name);
            }
        });
        
        // Show regenerate token button in edit mode
        regenerateBtn.style.display = 'inline-block';
        
        // Change button to save
        editBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
        editBtn.onclick = saveDeviceConfig;
    }
}

function populateDeviceConfigForm(stationId) {
    // Fetch device configuration data from backend
    fetch(`/stations/${stationId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch device configuration');
            }
            return response.json();
        })
        .then(station => {
            // Populate form fields with fetched data
            document.getElementById('config_station_id').value = station.station_id || '';
            document.getElementById('config_api_token').value = station.api_token || 'Not Available';
            document.getElementById('config_mac_address').value = station.mac_address || '';
            document.getElementById('config_data_interval').value = station.data_interval || '';
            document.getElementById('config_data_collection_time').value = station.data_collection_time || '';
            
            // Update last updated text at bottom
            const lastUpdatedText = station.updated_at ? new Date(station.updated_at).toLocaleString() : 'N/A';
            document.getElementById('config_last_updated_text').textContent = `Last Updated: ${lastUpdatedText}`;
        })
        .catch(error => {
            console.error('Error loading device configuration:', error);
            alert('Error loading device configuration. Please try again.');
        });
}

function saveDeviceConfig() {
    const form = document.getElementById('deviceConfigForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Show loading state
    const editBtn = document.getElementById('editConfigBtn');
    const originalText = editBtn.innerHTML;
    editBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    editBtn.disabled = true;
    
    // Send update request
    fetch(`/stations/${currentConfigStationId}/update-config`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and return to step 1 (station list view)
            const modal = bootstrap.Modal.getInstance(document.getElementById('deviceConfigModal'));
            modal.hide();
            
            // Reload page to reflect changes in the station list
            location.reload();
        } else {
            alert('Error updating device configuration: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating device configuration. Please try again.');
    })
    .finally(() => {
        // Reset button
        editBtn.innerHTML = originalText;
        editBtn.disabled = false;
    });
}

function copyApiToken() {
    const tokenInput = document.getElementById('config_api_token');
    const token = tokenInput.value;
    
    if (token && token !== 'Not Available') {
        navigator.clipboard.writeText(token).then(() => {
            // Show temporary success message
            const copyBtn = document.getElementById('copyTokenBtn');
            const originalIcon = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
            
            setTimeout(() => {
                copyBtn.innerHTML = originalIcon;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy token:', err);
            alert('Failed to copy token to clipboard');
        });
    } else {
        alert('No token available to copy');
    }
}

function regenerateApiToken() {
    if (!confirm('Are you sure you want to regenerate the API token? The old token will become invalid.')) {
        return;
    }
    
    const btn = document.getElementById('regenerateTokenBtn');
    const originalIcon = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/stations/${currentConfigStationId}/regenerate-token`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('New API token generated successfully!');
            // Reload configuration to show new token
            loadDeviceConfig(currentConfigStationId);
        } else {
            alert('Error regenerating token: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error regenerating token. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalIcon;
        btn.disabled = false;
    });
}

// Historical Data Modal Variables
let currentPage = 1;
let totalPages = 1;
let pageSize = 20;

function showHistoricalData(stationId) {
    currentStationId = stationId;
    currentPage = 1;
    
    // Get station information first
    fetch(`/api/stations/${stationId}`)
        .then(response => {
            console.log('Station API response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.json();
        })
        .then(station => {
            console.log('Station data received:', station);
            // Update modal header with station info
            document.getElementById('hist_station_id').textContent = station.station_id;
            document.getElementById('hist_station_name').textContent = station.station_name;
            
            // Set date filters to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('from_date').value = today;
            document.getElementById('to_date').value = today;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('historicalDataModal'));
            modal.show();
            
            // Load initial data (today's data)
            loadHistoricalData();
        })
        .catch(error => {
            console.error('Error fetching station info:', error);
            console.error('Full error details:', error.message);
            alert(`Error loading station information: ${error.message}`);
        });
}

function loadHistoricalData() {
    if (!currentStationId) return;
    
    // Show loading
    const tbody = document.getElementById('historicalDataBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-spinner fa-spin mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-1">Loading data...</h6>
                    <small class="text-muted">Please wait while we fetch the sensor readings</small>
                </div>
            </td>
        </tr>
    `;
    
    // Build URL with filters
    let url = `/stations/${currentStationId}/historical-data?page=${currentPage}&per_page=${pageSize}`;
    
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    
    if (fromDate) url += `&from_date=${fromDate}`;
    if (toDate) url += `&to_date=${toDate}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateHistoricalTable(data.data);
                updatePagination(data.total, data.current_page, data.per_page);
            } else {
                showNoDataMessage();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage();
        });
}

function populateHistoricalTable(readings) {
    const tbody = document.getElementById('historicalDataBody');
    
    if (readings.length === 0) {
        showNoDataMessage();
        return;
    }
    
    tbody.innerHTML = readings.map(reading => `
        <tr>
            <td>${formatDateTime(reading.reading_time)}</td>
            <td>${reading.station_id}</td>
            <td>${reading.humidity !== null ? reading.humidity + '%' : 'N/A'}</td>
            <td>${reading.temperature !== null ? reading.temperature + '°C' : 'N/A'}</td>
            <td>${reading.rssi !== null ? reading.rssi + ' dBm' : 'N/A'}</td>
            <td>${reading.battery_voltage !== null ? reading.battery_voltage + 'V' : 'N/A'}</td>
        </tr>
    `).join('');
}

function showNoDataMessage() {
    const tbody = document.getElementById('historicalDataBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-1">No data available</h6>
                    <small class="text-muted">No sensor readings found for the selected date range</small>
                </div>
            </td>
        </tr>
    `;
    updatePagination(0, 1, pageSize);
}

function showErrorMessage() {
    const tbody = document.getElementById('historicalDataBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-danger py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-1">Error loading data</h6>
                    <small class="text-muted">Please try again or contact support if the problem persists</small>
                </div>
            </td>
        </tr>
    `;
}

function updatePagination(total, current, perPage) {
    totalPages = Math.ceil(total / perPage);
    currentPage = current;
    
    // Update record count
    document.getElementById('data_count_badge').textContent = `${total} records`;
    
    const showingFrom = total > 0 ? ((current - 1) * perPage) + 1 : 0;
    const showingTo = Math.min(current * perPage, total);
    
    document.getElementById('showing_from').textContent = showingFrom;
    document.getElementById('showing_to').textContent = showingTo;
    document.getElementById('total_records').textContent = total;
    
    // Update pagination buttons
    document.getElementById('prevBtn').disabled = current <= 1;
    document.getElementById('nextBtn').disabled = current >= totalPages;
}

function filterHistoricalData() {
    currentPage = 1;
    loadHistoricalData();
}

function clearDateFilter() {
    document.getElementById('from_date').value = '';
    document.getElementById('to_date').value = '';
    currentPage = 1;
    loadHistoricalData();
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        loadHistoricalData();
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        loadHistoricalData();
    }
}

function exportHistoricalData() {
    if (!currentStationId) return;
    
    let url = `/stations/${currentStationId}/historical-data/export`;
    
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    
    const params = new URLSearchParams();
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Create temporary link to download
    const link = document.createElement('a');
    link.href = url;
    link.download = `station_${currentStationId}_historical_data.csv`;
    link.click();
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

// Task Log Modal Variables
let currentLogPage = 1;
let totalLogPages = 1;
let logPageSize = 20;

function showTaskLog(stationId) {
    currentStationId = stationId;
    currentLogPage = 1;
    
    // Get station information first
    fetch(`/api/stations/${stationId}`)
        .then(response => {
            console.log('Station API response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.json();
        })
        .then(station => {
            console.log('Station data received:', station);
            // Update modal header with station info
            document.getElementById('log_station_id').textContent = station.station_id;
            document.getElementById('log_station_name').textContent = station.station_name;
            
            // Set date filters to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('log_from_date').value = today;
            document.getElementById('log_to_date').value = today;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('mqttTaskLogModal'));
            modal.show();
            
            // Load initial data (today's logs)
            loadTaskLogData();
        })
        .catch(error => {
            console.error('Error fetching station info:', error);
            console.error('Full error details:', error.message);
            alert(`Error loading station information: ${error.message}`);
        });
}

function loadTaskLogData() {
    if (!currentStationId) return;
    
    // Show loading
    const tbody = document.getElementById('taskLogBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-spinner fa-spin mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-1">Loading logs...</h6>
                    <small class="text-muted">Please wait while we fetch the MQTT task logs</small>
                </div>
            </td>
        </tr>
    `;
    
    // Build URL with filters
    let url = `/stations/${currentStationId}/task-logs?page=${currentLogPage}&per_page=${logPageSize}`;
    
    const fromDate = document.getElementById('log_from_date').value;
    const toDate = document.getElementById('log_to_date').value;
    
    if (fromDate) url += `&from_date=${fromDate}`;
    if (toDate) url += `&to_date=${toDate}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateTaskLogTable(data.data);
                updateLogPagination(data.total, data.current_page, data.per_page);
            } else {
                showNoLogDataMessage();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showLogErrorMessage();
        });
}

function populateTaskLogTable(logs) {
    const tbody = document.getElementById('taskLogBody');
    
    if (logs.length === 0) {
        showNoLogDataMessage();
        return;
    }
    
    tbody.innerHTML = logs.map(log => `
        <tr>
            <td>${formatDateTime(log.received_at)}</td>
            <td>${log.station_id}</td>
            <td>${log.task_type || 'N/A'}</td>
            <td>
                ${log.status === 'success' ? '<span class="badge bg-success">Success</span>' : 
                  log.status === 'failed' ? '<span class="badge bg-danger">Failed</span>' : 
                  '<span class="badge bg-warning">Pending</span>'}
            </td>
            <td>${log.message || 'N/A'}</td>
            <td>${log.response_time !== null ? log.response_time + ' ms' : 'N/A'}</td>
        </tr>
    `).join('');
}

function showNoLogDataMessage() {
    const tbody = document.getElementById('taskLogBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-1">No logs available</h6>
                    <small class="text-muted">No MQTT task logs found for the selected date range</small>
                </div>
            </td>
        </tr>
    `;
    updateLogPagination(0, 1, logPageSize);
}

function showLogErrorMessage() {
    const tbody = document.getElementById('taskLogBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-danger py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-1">Error loading logs</h6>
                    <small class="text-muted">Please try again or contact support if the problem persists</small>
                </div>
            </td>
        </tr>
    `;
}

function updateLogPagination(total, current, perPage) {
    totalLogPages = Math.ceil(total / perPage);
    currentLogPage = current;
    
    // Update record count
    document.getElementById('log_count_badge').textContent = `${total} records`;
    
    const showingFrom = total > 0 ? ((current - 1) * perPage) + 1 : 0;
    const showingTo = Math.min(current * perPage, total);
    
    document.getElementById('log_showing_from').textContent = showingFrom;
    document.getElementById('log_showing_to').textContent = showingTo;
    document.getElementById('log_total_records').textContent = total;
    
    // Update pagination buttons
    document.getElementById('logPrevBtn').disabled = current <= 1;
    document.getElementById('logNextBtn').disabled = current >= totalLogPages;
}

function filterTaskLogData() {
    currentLogPage = 1;
    loadTaskLogData();
}

function clearLogDateFilter() {
    document.getElementById('log_from_date').value = '';
    document.getElementById('log_to_date').value = '';
    currentLogPage = 1;
    loadTaskLogData();
}

function previousLogPage() {
    if (currentLogPage > 1) {
        currentLogPage--;
        loadTaskLogData();
    }
}

function nextLogPage() {
    if (currentLogPage < totalLogPages) {
        currentLogPage++;
        loadTaskLogData();
    }
}

function exportTaskLogData() {
    if (!currentStationId) return;
    
    let url = `/stations/${currentStationId}/task-logs/export`;
    
    const fromDate = document.getElementById('log_from_date').value;
    const toDate = document.getElementById('log_to_date').value;
    
    const params = new URLSearchParams();
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Create temporary link to download
    const link = document.createElement('a');
    link.href = url;
    link.download = `station_${currentStationId}_task_logs.csv`;
    link.click();
}

// Create Station Modal Functions
function openCreateStationModal() {
    // Reset form and go to step 1
    resetCreateStationForm();
    showStep(1);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('createStationModal'));
    modal.show();
}


function resetCreateStationForm() {
    document.getElementById('createStationForm').reset();
    document.getElementById('create_district_id').disabled = true;
    document.getElementById('create_district_id').innerHTML = '<option value="">Select District</option>';
    
    // Clear all validation states
    clearValidationErrors([
        'create_station_name', 'create_state_id', 'create_district_id', 'create_address',
        'create_gps_latitude', 'create_gps_longitude', 'create_mac_address', 
        'create_data_interval', 'create_data_collection_time'
    ]);
}

function showStep(step) {
    // Hide all steps
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    
    // Show current step
    document.getElementById('step' + step).style.display = 'block';
    
    // Update buttons
    if (step === 1) {
        document.getElementById('nextStepBtn').style.display = 'inline-block';
        document.getElementById('prevStepBtn').style.display = 'none';
        document.getElementById('createStationBtn').style.display = 'none';
    } else if (step === 2) {
        document.getElementById('nextStepBtn').style.display = 'none';
        document.getElementById('prevStepBtn').style.display = 'inline-block';
        document.getElementById('createStationBtn').style.display = 'inline-block';
    }
}

// Handle state change for create modal
document.addEventListener('DOMContentLoaded', function() {
    // Initialize validation listeners
    addValidationListeners();
    // Next button click
    document.getElementById('nextStepBtn').addEventListener('click', function() {
        if (validateStep1()) {
            showStep(2);
        }
    });
    
    // Previous button click
    document.getElementById('prevStepBtn').addEventListener('click', function() {
        showStep(1);
    });
    
    // State change handler for create modal
    document.getElementById('create_state_id').addEventListener('change', function() {
        const stateId = this.value;
        const districtSelect = document.getElementById('create_district_id');
        
        // Clear and disable district select
        districtSelect.innerHTML = '<option value="">Select District</option>';
        districtSelect.disabled = !stateId;
        
        if (stateId) {
            fetch(`/api/districts?state_id=${stateId}`)
                .then(response => response.json())
                .then(districts => {
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading districts:', error));
        }
    });
    
    // Add click handler to create button for debugging
    document.getElementById('createStationBtn').addEventListener('click', function(e) {
        console.log('Create Station button clicked!');
    });
    
    // Form submission
    document.getElementById('createStationForm').addEventListener('submit', function(e) {
        console.log('Form submission triggered!');
        e.preventDefault();
        
        console.log('Starting Step 2 validation...');
        if (!validateStep2()) {
            console.log('Step 2 validation failed - form submission stopped');
            return;
        }
        console.log('Step 2 validation passed - proceeding with submission');
        
        // Show loading
        const createBtn = document.getElementById('createStationBtn');
        const originalText = createBtn.innerHTML;
        createBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
        createBtn.disabled = true;
        
        // Collect form data
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        console.log('Submitting data:', data);
        
        // Check CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('CSRF token element:', csrfToken);
        console.log('CSRF token value:', csrfToken ? csrfToken.getAttribute('content') : 'NOT FOUND');
        
        if (!csrfToken) {
            alert('CSRF token not found. Please reload the page.');
            return;
        }
        
        // Submit to backend
        fetch('/stations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));
            
            if (!response.ok) {
                // Check if response is JSON or HTML
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(err => {
                        console.log('Error response:', err);
                        return Promise.reject(err);
                    });
                } else {
                    // It's HTML (error page), get text instead
                    return response.text().then(html => {
                        console.log('HTML error response:', html);
                        return Promise.reject({
                            error: `Server error (${response.status}): Please check server logs`,
                            html: html
                        });
                    });
                }
            }
            
            // Check if successful response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(html => {
                    console.log('Unexpected HTML response:', html);
                    return Promise.reject({
                        error: 'Expected JSON response but got HTML',
                        html: html
                    });
                });
            }
        })
        .then(result => {
            console.log('Success response:', result);
            if (result.success) {
                // Close create modal
                bootstrap.Modal.getInstance(document.getElementById('createStationModal')).hide();
                
                // Show success message and reload page to show new station
                alert(result.message);
                
                // Open device config modal for the newly created station
                setTimeout(() => {
                    showDeviceConfig(result.station_id);
                }, 500);
            } else {
                alert('Error creating station: ' + (result.error || result.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Full error object:', error);
            console.error('Error keys:', Object.keys(error));
            
            // Handle different types of errors
            let errorMessage = 'Error creating station. Please try again.';
            
            if (error.error) {
                errorMessage = error.error;
            } else if (error.message) {
                errorMessage = error.message;
            } else if (error.errors) {
                // Handle validation errors
                const errors = Object.values(error.errors).flat();
                errorMessage = 'Validation errors: ' + errors.join(', ');
            } else if (typeof error === 'string') {
                errorMessage = error;
            }
            
            alert(errorMessage);
        })
        .finally(() => {
            // Reset button
            createBtn.innerHTML = originalText;
            createBtn.disabled = false;
        });
    });
});

function validateStep1() {
    let isValid = true;
    
    // Clear previous validation states
    clearValidationErrors(['create_station_name', 'create_state_id', 'create_district_id', 'create_gps_latitude', 'create_gps_longitude']);
    
    // Validate station name
    const stationName = document.getElementById('create_station_name').value.trim();
    if (!stationName) {
        showFieldError('create_station_name', 'Station name is required');
        isValid = false;
    } else if (stationName.length < 3) {
        showFieldError('create_station_name', 'Station name must be at least 3 characters');
        isValid = false;
    } else {
        showFieldSuccess('create_station_name');
    }
    
    // Validate state
    const stateId = document.getElementById('create_state_id').value;
    if (!stateId) {
        showFieldError('create_state_id', 'Please select a state');
        isValid = false;
    } else {
        showFieldSuccess('create_state_id');
    }
    
    // Validate district
    const districtId = document.getElementById('create_district_id').value;
    if (!districtId) {
        showFieldError('create_district_id', 'Please select a district');
        isValid = false;
    } else {
        showFieldSuccess('create_district_id');
    }
    
    // Validate GPS coordinates (optional but if provided must be valid)
    const latitude = document.getElementById('create_gps_latitude').value;
    const longitude = document.getElementById('create_gps_longitude').value;
    
    if (latitude && (isNaN(latitude) || latitude < -90 || latitude > 90)) {
        showFieldError('create_gps_latitude', 'Latitude must be between -90 and 90');
        isValid = false;
    } else if (latitude) {
        showFieldSuccess('create_gps_latitude');
    }
    
    if (longitude && (isNaN(longitude) || longitude < -180 || longitude > 180)) {
        showFieldError('create_gps_longitude', 'Longitude must be between -180 and 180');
        isValid = false;
    } else if (longitude) {
        showFieldSuccess('create_gps_longitude');
    }
    
    return isValid;
}

function validateStep2() {
    console.log('=== validateStep2 called ===');
    let isValid = true;
    
    // Clear previous validation states
    clearValidationErrors(['create_mac_address', 'create_data_interval', 'create_data_collection_time']);
    
    // Validate MAC address
    const macAddress = document.getElementById('create_mac_address').value.trim();
    if (!macAddress) {
        showFieldError('create_mac_address', 'MAC address is required');
        isValid = false;
    } else {
        // Basic MAC address validation
        const macPattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
        if (!macPattern.test(macAddress)) {
            showFieldError('create_mac_address', 'Invalid MAC address format. Use AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF');
            isValid = false;
        } else {
            // Check if field has error class (from uniqueness validation)
            const macField = document.getElementById('create_mac_address');
            console.log('MAC field classes:', macField.className);
            console.log('Has is-invalid class:', macField.classList.contains('is-invalid'));
            console.log('Has is-valid class:', macField.classList.contains('is-valid'));
            
            if (macField.classList.contains('is-invalid')) {
                console.log('MAC validation failed: field has is-invalid class');
                isValid = false; // MAC address has validation error (likely duplicate)
            } else {
                // If format is valid and field doesn't have is-invalid, allow submission
                // The uniqueness validation will prevent duplicates on the backend anyway
                console.log('MAC validation passed (format is valid)');
            }
        }
    }
    
    // Validate data interval
    const dataInterval = document.getElementById('create_data_interval').value;
    if (!dataInterval) {
        showFieldError('create_data_interval', 'Data interval is required');
        isValid = false;
    } else if (dataInterval < 1 || dataInterval > 60) {
        showFieldError('create_data_interval', 'Data interval must be between 1 and 60 minutes');
        isValid = false;
    } else {
        showFieldSuccess('create_data_interval');
    }
    
    // Validate data collection time
    const dataCollectionTime = document.getElementById('create_data_collection_time').value;
    if (!dataCollectionTime) {
        showFieldError('create_data_collection_time', 'Data collection time is required');
        isValid = false;
    } else if (dataCollectionTime < 10 || dataCollectionTime > 300) {
        showFieldError('create_data_collection_time', 'Data collection time must be between 10 and 300 minutes');
        isValid = false;
    } else {
        showFieldSuccess('create_data_collection_time');
    }
    
    console.log('=== validateStep2 result:', isValid, '===');
    return isValid;
}

// Helper functions for validation feedback
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + '_error');
    
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function showFieldSuccess(fieldId) {
    console.log('showFieldSuccess called for:', fieldId);
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + '_error');
    
    console.log('Field before success:', field ? field.className : 'FIELD NOT FOUND');
    
    if (field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        console.log('Field after success:', field.className);
    }
    
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function clearValidationErrors(fieldIds) {
    fieldIds.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + '_error');
        
        if (field) {
            field.classList.remove('is-invalid', 'is-valid');
        }
        
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    });
}

// Add real-time validation listeners
function addValidationListeners() {
    // Step 1 fields
    document.getElementById('create_station_name').addEventListener('blur', function() {
        const value = this.value.trim();
        if (value) {
            if (value.length < 3) {
                showFieldError('create_station_name', 'Station name must be at least 3 characters');
            } else {
                showFieldSuccess('create_station_name');
            }
        }
    });
    
    document.getElementById('create_state_id').addEventListener('change', function() {
        if (this.value) {
            showFieldSuccess('create_state_id');
        }
    });
    
    document.getElementById('create_district_id').addEventListener('change', function() {
        if (this.value) {
            showFieldSuccess('create_district_id');
        }
    });
    
    // Step 2 fields
    document.getElementById('create_mac_address').addEventListener('blur', function() {
        const value = this.value.trim();
        if (value) {
            console.log('MAC Address being validated:', value);
            console.log('MAC Address length:', value.length);
            console.log('MAC Address char codes:', value.split('').map(c => c.charCodeAt(0)));
            
            const macPattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
            const isValid = macPattern.test(value);
            console.log('MAC Pattern test result:', isValid);
            
            if (!isValid) {
                showFieldError('create_mac_address', 'Invalid MAC address format. Use AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF');
            } else {
                // Check if MAC address already exists
                validateMacAddressUniqueness(value);
            }
        }
    });
    
    document.getElementById('create_data_interval').addEventListener('blur', function() {
        const value = this.value;
        if (value && (value < 1 || value > 60)) {
            showFieldError('create_data_interval', 'Data interval must be between 1 and 60 minutes');
        } else if (value) {
            showFieldSuccess('create_data_interval');
        }
    });
    
    document.getElementById('create_data_collection_time').addEventListener('blur', function() {
        const value = this.value;
        if (value && (value < 10 || value > 300)) {
            showFieldError('create_data_collection_time', 'Data collection time must be between 10 and 300 minutes');
        } else if (value) {
            showFieldSuccess('create_data_collection_time');
        }
    });
}

// Validate MAC address uniqueness
function validateMacAddressUniqueness(macAddress) {
    console.log('Starting MAC uniqueness validation for:', macAddress);
    
    const field = document.getElementById('create_mac_address');
    
    // Show loading state
    field.classList.remove('is-valid', 'is-invalid');
    field.style.backgroundColor = '#f8f9fa';
    
    // Add loading indicator
    const errorDiv = document.getElementById('create_mac_address_error');
    if (errorDiv) {
        errorDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking MAC address...';
        errorDiv.style.display = 'block';
        errorDiv.style.color = '#6c757d';
    }
    
    fetch('/api/validate-mac-address', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ mac_address: macAddress })
    })
    .then(response => {
        console.log('MAC validation response status:', response.status);
        console.log('MAC validation response content-type:', response.headers.get('content-type'));
        return response.json();
    })
    .then(result => {
        console.log('MAC validation result:', result);
        if (result.available) {
            console.log('MAC address is available - setting field to valid');
            showFieldSuccess('create_mac_address');
            
            // Debug: Check if the class was actually added
            const field = document.getElementById('create_mac_address');
            console.log('After showFieldSuccess - MAC field classes:', field.className);
            console.log('After showFieldSuccess - Has is-valid class:', field.classList.contains('is-valid'));
        } else {
            console.log('MAC address already exists');
            showFieldError('create_mac_address', 'MAC address already exists in the system');
        }
    })
    .catch(error => {
        console.error('MAC validation error:', error);
        // On error, allow the field but show warning
        field.classList.remove('is-invalid');
        field.style.backgroundColor = '';
        if (errorDiv) {
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Could not verify MAC address uniqueness';
            errorDiv.style.color = '#fd7e14';
        }
    })
    .finally(() => {
        // Reset loading state
        field.style.backgroundColor = '';
    });
}

</script>
@endpush
@endsection