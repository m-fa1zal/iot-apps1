@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);">
                        <i class="fas fa-microchip text-white"></i>
                    </div>
                    <div>
                        <h1 class="mb-0 fw-bold">Device Management</h1>
                        <p class="text-muted mb-0">Manage IoT stations and monitoring devices</p>
                    </div>
                </div>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('devices.create') }}" class="btn btn-primary-modern btn-modern">
                    <i class="fas fa-plus me-2"></i>Add New Device
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card-modern mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('devices.index') }}">
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
                    <div class="col-md-3">
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-modern btn-modern me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Device List -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-microchip me-2"></i>Registered Devices
                <span class="badge bg-light text-primary ms-2">{{ $devices->total() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($devices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="devicesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Station ID</th>
                                <th>Station Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Last Seen</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devices as $device)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                                <i class="fas fa-microchip"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $device->station_id }}</div>
                                                <small class="text-muted">{{ $device->created_at->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $device->station_name }}</div>
                                        @if($device->address)
                                            <small class="text-muted">{{ Str::limit($device->address, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-primary">{{ $device->state->name ?? 'N/A' }}</span>
                                            <span class="badge bg-secondary">{{ $device->district->name ?? 'N/A' }}</span>
                                        </div>
                                        @if($device->district && $device->district->district_code)
                                            <small class="text-muted d-block">{{ $device->district->district_code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($device->status)
                                            @case('online')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-circle me-1"></i>Online
                                                </span>
                                                @break
                                            @case('offline')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-circle me-1"></i>Offline
                                                </span>
                                                @break
                                            @case('maintenance')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-tools me-1"></i>Maintenance
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($device->last_seen)
                                            <div>{{ $device->last_seen->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $device->last_seen->format('H:i') }}</small>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <!-- Station Information - visible to all users -->
                                            <button type="button" class="btn btn-outline-info btn-sm" title="Station Information" 
                                                    onclick="showStationInfo({{ $device->id }}, '{{ $device->station_id }}', '{{ addslashes($device->station_name) }}', '{{ addslashes($device->address ?? '') }}', '{{ $device->gps_latitude }}', '{{ $device->gps_longitude }}', '{{ $device->status }}', {{ $device->state_id }}, {{ $device->district_id }})">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                            
                                            <!-- Admin-only buttons -->
                                            @if(Auth::user()->isAdmin())
                                            <button type="button" class="btn btn-outline-primary btn-sm" title="Device Configuration" 
                                                    onclick="showDeviceConfig({{ $device->id }}, '{{ $device->api_token }}', '{{ $device->mac_address }}', {{ $device->data_interval_minutes }}, {{ $device->data_collection_time_minutes }})">
                                                <i class="fas fa-cogs"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm" title="Historical Data" 
                                                    onclick="showHistoricalData({{ $device->id }}, '{{ $device->station_id }}', '{{ addslashes($device->station_name) }}')">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm" title="Deactivate" 
                                                    onclick="confirmDeactivate('{{ $device->station_name }}', '{{ route('devices.destroy', $device) }}')">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4">
                    {{ $devices->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%); width: 80px; height: 80px;">
                        <i class="fas fa-microchip text-white fa-2x"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Devices Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'state_id', 'district_id', 'status']))
                            No devices match your current filters. Try adjusting your search criteria.
                        @else
                            You haven't added any IoT devices yet. Start by adding your first monitoring station.
                        @endif
                    </p>
                    <a href="{{ route('devices.create') }}" class="btn btn-primary-modern btn-modern">
                        <i class="fas fa-plus me-2"></i>Add First Device
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Deactivate Confirmation Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deactivation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate station <strong id="deactivateDeviceName"></strong>?</p>
                <p class="text-muted">This action will:</p>
                <ul class="text-muted">
                    <li>Hide the station from the active stations list</li>
                    <li>Stop the station from accepting new data</li>
                    <li>Preserve all existing data and settings</li>
                </ul>
                <p class="text-info"><i class="fas fa-info-circle me-1"></i>The station can be reactivated later if needed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deactivateForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-power-off me-1"></i>Deactivate Station
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Station Information Modal -->
<div class="modal fade" id="stationInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Station Information
                    @if(!Auth::user()->isAdmin())
                        <span class="badge bg-secondary ms-2">View Only</span>
                    @endif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Success Message -->
            <div id="stationSuccessMessage" class="alert alert-success alert-dismissible mx-3 mt-3 mb-0" style="display: none;">
                <i class="fas fa-check-circle me-2"></i>
                <span id="stationSuccessText"></span>
                <button type="button" class="btn-close" onclick="hideStationSuccessMessage()"></button>
            </div>
            <form id="stationInfoForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- View Mode -->
                    <div id="viewMode">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Station ID</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewStationId"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Station Name</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewStationName"></p>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewAddress"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">GPS Latitude</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewLatitude"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">GPS Longitude</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewLongitude"></p>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-success btn-sm" id="viewOnMapsBtn" onclick="openGoogleMaps()" style="display: none;">
                                    <i class="fas fa-map-marker-alt me-1"></i>View on Google Maps
                                </button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext" id="viewStatus"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="editMode" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Station ID</label>
                                <input type="text" class="form-control" id="editStationId" readonly>
                                <small class="text-muted">Station ID cannot be changed</small>
                            </div>
                            <div class="col-md-6">
                                <label for="editStationName" class="form-label fw-bold required">Station Name</label>
                                <input type="text" class="form-control" id="editStationName" name="station_name" required>
                            </div>
                            <div class="col-12">
                                <label for="editAddress" class="form-label fw-bold">Address</label>
                                <textarea class="form-control" id="editAddress" name="address" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="editLatitude" class="form-label fw-bold">GPS Latitude</label>
                                <input type="number" step="any" class="form-control" id="editLatitude" name="gps_latitude" min="-90" max="90">
                            </div>
                            <div class="col-md-6">
                                <label for="editLongitude" class="form-label fw-bold">GPS Longitude</label>
                                <input type="number" step="any" class="form-control" id="editLongitude" name="gps_longitude" min="-180" max="180">
                            </div>
                            <div class="col-md-6">
                                <label for="editStatus" class="form-label fw-bold required">Status</label>
                                <select class="form-select" id="editStatus" name="status" required>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editState" class="form-label fw-bold required">State</label>
                                <select class="form-select" id="editState" name="state_id" required>
                                    <option value="">Select State</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editDistrict" class="form-label fw-bold required">District</label>
                                <select class="form-select" id="editDistrict" name="district_id" required>
                                    <option value="">Select District</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="viewModeButtons">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @if(Auth::user()->isAdmin())
                        <button type="button" class="btn btn-primary" onclick="toggleEditMode()">
                            <i class="fas fa-edit me-1"></i>Edit Station
                        </button>
                        @endif
                    </div>
                    <div id="editModeButtons" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Device Configuration Modal -->
<div class="modal fade" id="deviceConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-cogs me-2"></i>Device Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Success Message -->
            <div id="deviceSuccessMessage" class="alert alert-success alert-dismissible mx-3 mt-3 mb-0" style="display: none;">
                <i class="fas fa-check-circle me-2"></i>
                <span id="deviceSuccessText"></span>
                <button type="button" class="btn-close" onclick="hideDeviceSuccessMessage()"></button>
            </div>
            <form id="deviceConfigForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- View Mode -->
                    <div id="deviceViewMode">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">API Token</label>
                                <div class="input-group">
                                    <input type="text" class="form-control-plaintext border p-2 bg-light rounded" id="viewApiToken" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('viewApiToken')" title="Copy to clipboard">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Used for device authentication with the server</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">MAC Address</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewMacAddress"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Data Interval</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewDataInterval"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Data Collection Time</label>
                                <p class="form-control-plaintext border p-2 bg-light rounded" id="viewDataCollectionTime"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="deviceEditMode" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">API Token</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="editApiToken" name="api_token" readonly>
                                    <button type="button" class="btn btn-warning" onclick="generateNewToken()" title="Generate new token">
                                        <i class="fas fa-refresh me-1"></i>Generate New
                                    </button>
                                </div>
                                <small class="text-muted">Click "Generate New" to create a new API token. This will invalidate the current token.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="editMacAddress" class="form-label fw-bold required">MAC Address</label>
                                <input type="text" class="form-control" id="editMacAddress" name="mac_address" 
                                       placeholder="e.g., AA:BB:CC:DD:EE:FF" pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$" 
                                       maxlength="17" required>
                                <small class="text-muted">Format: XX:XX:XX:XX:XX:XX</small>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <label for="editDataInterval" class="form-label fw-bold required">Data Interval (Minutes)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="editDataInterval" name="data_interval_minutes" 
                                           min="1" max="1440" required>
                                    <span class="input-group-text">min</span>
                                </div>
                                <small class="text-muted">How often the device sends data (1-1440 minutes)</small>
                            </div>
                            <div class="col-md-6">
                                <label for="editDataCollectionTime" class="form-label fw-bold required">Data Collection Time (Minutes)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="editDataCollectionTime" name="data_collection_time_minutes" 
                                           min="1" max="60" required>
                                    <span class="input-group-text">min</span>
                                </div>
                                <small class="text-muted">How long each data collection session lasts (1-60 minutes)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="deviceViewModeButtons">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="toggleDeviceEditMode()">
                            <i class="fas fa-edit me-1"></i>Edit Configuration
                        </button>
                    </div>
                    <div id="deviceEditModeButtons" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="cancelDeviceEdit()">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Save Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Historical Data Modal -->
<div class="modal fade" id="historicalDataModal" tabindex="-1" aria-labelledby="historicalDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historicalDataModalLabel">
                    <i class="fas fa-chart-line me-2"></i>Historical Data - <span id="historicalStationName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date">
                    </div>
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="endDate" name="end_date">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" onclick="validateAndLoadData()">
                            <i class="fas fa-filter me-1"></i>Refresh
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportToExcel()" id="exportBtn" disabled>
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-refresh me-1"></i>Reset
                        </button>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading historical data...</p>
                </div>

                <!-- Data Table -->
                <div id="dataTableContainer">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="historicalDataTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Reading Time</th>
                                    <th>Temperature (°C)</th>
                                    <th>Humidity (%)</th>
                                    <th>RSSI (dBm)</th>
                                    <th>Battery Voltage (V)</th>
                                    <th>Type</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody id="historicalDataBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                        
                        <!-- No Data Message inside table -->
                        <div id="noDataMessage" class="text-center py-4" style="display: none;">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <h6 class="text-muted mb-1">No Data Available</h6>
                            <small class="text-muted">No sensor readings found for the selected date range.</small>
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

@push('scripts')
<script>
let currentDeviceId = null;

function confirmDeactivate(deviceName, deactivateUrl) {
    document.getElementById('deactivateDeviceName').textContent = deviceName;
    document.getElementById('deactivateForm').action = deactivateUrl;
    
    const deactivateModal = new bootstrap.Modal(document.getElementById('deactivateModal'));
    deactivateModal.show();
}

function showStationInfo(deviceId, stationId, stationName, address, latitude, longitude, status, stateId, districtId) {
    currentDeviceId = deviceId;
    
    // Store GPS coordinates for Google Maps
    currentStationLatitude = latitude;
    currentStationLongitude = longitude;
    
    // Populate view mode
    document.getElementById('viewStationId').textContent = stationId;
    document.getElementById('viewStationName').textContent = stationName;
    document.getElementById('viewAddress').textContent = address || 'Not provided';
    document.getElementById('viewLatitude').textContent = latitude || 'Not provided';
    document.getElementById('viewLongitude').textContent = longitude || 'Not provided';
    
    // Show/hide Google Maps button based on GPS availability
    const mapsBtn = document.getElementById('viewOnMapsBtn');
    if (latitude && longitude && latitude !== '' && longitude !== '') {
        mapsBtn.style.display = 'inline-block';
    } else {
        mapsBtn.style.display = 'none';
    }
    
    // Format status badge
    let statusBadge = '';
    switch(status) {
        case 'online':
            statusBadge = '<span class="badge bg-success"><i class="fas fa-circle me-1"></i>Online</span>';
            break;
        case 'offline':
            statusBadge = '<span class="badge bg-danger"><i class="fas fa-circle me-1"></i>Offline</span>';
            break;
        case 'maintenance':
            statusBadge = '<span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Maintenance</span>';
            break;
    }
    document.getElementById('viewStatus').innerHTML = statusBadge;
    
    // Populate edit mode
    document.getElementById('editStationId').value = stationId;
    document.getElementById('editStationName').value = stationName;
    document.getElementById('editAddress').value = address || '';
    document.getElementById('editLatitude').value = latitude || '';
    document.getElementById('editLongitude').value = longitude || '';
    document.getElementById('editStatus').value = status;
    document.getElementById('editState').value = stateId;
    
    // Load districts for the selected state
    if (stateId) {
        loadDistrictsForEdit(stateId, districtId);
    }
    
    // Hide any existing success message
    hideStationSuccessMessage();
    
    // Show modal in view mode
    document.getElementById('viewMode').style.display = 'block';
    document.getElementById('editMode').style.display = 'none';
    document.getElementById('viewModeButtons').style.display = 'block';
    document.getElementById('editModeButtons').style.display = 'none';
    
    const stationModal = new bootstrap.Modal(document.getElementById('stationInfoModal'));
    stationModal.show();
}

function toggleEditMode() {
    // Check if user is admin - only admins can edit
    var userRole = '{{ Auth::user()->role }}';
    if (userRole !== 'admin') {
        alert('You do not have permission to edit station information.');
        return;
    }
    
    document.getElementById('viewMode').style.display = 'none';
    document.getElementById('editMode').style.display = 'block';
    document.getElementById('viewModeButtons').style.display = 'none';
    document.getElementById('editModeButtons').style.display = 'block';
}

function cancelEdit() {
    document.getElementById('viewMode').style.display = 'block';
    document.getElementById('editMode').style.display = 'none';
    document.getElementById('viewModeButtons').style.display = 'block';
    document.getElementById('editModeButtons').style.display = 'none';
}

function loadDistrictsForEdit(stateId, selectedDistrictId = null) {
    const districtSelect = document.getElementById('editDistrict');
    
    // Clear existing options
    districtSelect.innerHTML = '<option value="">Select District</option>';
    
    if (!stateId) {
        return;
    }
    
    // Show loading state
    districtSelect.innerHTML = '<option value="">Loading districts...</option>';
    districtSelect.disabled = true;
    
    // Fetch districts for selected state
    fetch(`{{ route('districts.by-state') }}?state_id=${stateId}`)
        .then(response => response.json())
        .then(districts => {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            districts.forEach(district => {
                const option = new Option(district.name, district.id);
                if (selectedDistrictId == district.id) {
                    option.selected = true;
                }
                districtSelect.add(option);
            });
            districtSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading districts:', error);
            districtSelect.innerHTML = '<option value="">Error loading districts</option>';
            districtSelect.disabled = false;
        });
}

// Cascading dropdown functionality
function loadDistricts(stateId, selectedDistrictId = null) {
    const districtSelect = document.getElementById('district_id');
    
    // Clear existing options except "All Districts"
    districtSelect.innerHTML = '<option value="">All Districts</option>';
    
    if (!stateId) {
        // If no state selected, show all districts
        @foreach($districts as $district)
            const option{{ $district->id }} = new Option("{{ $district->name }}", "{{ $district->id }}");
            if (selectedDistrictId == "{{ $district->id }}") {
                option{{ $district->id }}.selected = true;
            }
            districtSelect.add(option{{ $district->id }});
        @endforeach
        return;
    }
    
    // Fetch districts for selected state
    fetch(`{{ route('districts.by-state') }}?state_id=${stateId}`)
        .then(response => response.json())
        .then(districts => {
            districts.forEach(district => {
                const option = new Option(district.name, district.id);
                if (selectedDistrictId == district.id) {
                    option.selected = true;
                }
                districtSelect.add(option);
            });
        })
        .catch(error => {
            console.error('Error loading districts:', error);
        });
}

function showDeviceConfig(deviceId, apiToken, macAddress, dataInterval, dataCollectionTime) {
    currentDeviceId = deviceId;
    
    // Populate view mode
    document.getElementById('viewApiToken').value = apiToken;
    document.getElementById('viewMacAddress').textContent = macAddress || 'Not set';
    document.getElementById('viewDataInterval').textContent = dataInterval + ' minutes';
    document.getElementById('viewDataCollectionTime').textContent = dataCollectionTime + ' minutes';
    
    // Populate edit mode
    document.getElementById('editApiToken').value = apiToken;
    document.getElementById('editMacAddress').value = macAddress || '';
    document.getElementById('editDataInterval').value = dataInterval;
    document.getElementById('editDataCollectionTime').value = dataCollectionTime;
    
    // Hide any existing success message
    hideDeviceSuccessMessage();
    
    // Show modal in view mode
    document.getElementById('deviceViewMode').style.display = 'block';
    document.getElementById('deviceEditMode').style.display = 'none';
    document.getElementById('deviceViewModeButtons').style.display = 'block';
    document.getElementById('deviceEditModeButtons').style.display = 'none';
    
    const deviceModal = new bootstrap.Modal(document.getElementById('deviceConfigModal'));
    deviceModal.show();
}

function toggleDeviceEditMode() {
    document.getElementById('deviceViewMode').style.display = 'none';
    document.getElementById('deviceEditMode').style.display = 'block';
    document.getElementById('deviceViewModeButtons').style.display = 'none';
    document.getElementById('deviceEditModeButtons').style.display = 'block';
}

function cancelDeviceEdit() {
    document.getElementById('deviceViewMode').style.display = 'block';
    document.getElementById('deviceEditMode').style.display = 'none';
    document.getElementById('deviceViewModeButtons').style.display = 'block';
    document.getElementById('deviceEditModeButtons').style.display = 'none';
}

function generateNewToken() {
    if (!confirm('Are you sure you want to generate a new API token? This will invalidate the current token and may require updating device configurations.')) {
        return;
    }
    
    const generateBtn = document.querySelector('button[onclick="generateNewToken()"]');
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
    generateBtn.disabled = true;
    
    fetch(`/devices/${currentDeviceId}/generate-token`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the token field
            document.getElementById('editApiToken').value = data.token;
            alert(data.message);
        } else {
            alert('Failed to generate new token. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating the new token.');
    })
    .finally(() => {
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    });
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.value || element.textContent;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success feedback
        const copyBtn = document.querySelector(`button[onclick="copyToClipboard('${elementId}')"]`);
        const originalIcon = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalIcon;
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy to clipboard');
    });
}

// MAC address formatting for device config
function formatMacAddressConfig(input) {
    let value = input.value.replace(/[^0-9A-Fa-f]/g, ''); // Remove non-hex characters
    
    // Add colons every 2 characters
    if (value.length > 0) {
        value = value.match(/.{1,2}/g).join(':');
        if (value.length > 17) {
            value = value.substring(0, 17);
        }
    }
    
    input.value = value.toUpperCase();
}

// Handle form submission for station info update
document.getElementById('stationInfoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const updateUrl = `/devices/${currentDeviceId}/update-info`;
    
    // Show loading state
    const submitBtn = document.querySelector('#editModeButtons button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    submitBtn.disabled = true;
    
    fetch(updateUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message at top of modal
            showStationSuccessMessage(data.message);
            
            // Switch back to view mode
            document.getElementById('viewMode').style.display = 'block';
            document.getElementById('editMode').style.display = 'none';
            document.getElementById('viewModeButtons').style.display = 'block';
            document.getElementById('editModeButtons').style.display = 'none';
            
            // Update view mode with new data
            document.getElementById('viewStationName').textContent = document.getElementById('editStationName').value;
            document.getElementById('viewAddress').textContent = document.getElementById('editAddress').value || 'Not provided';
            document.getElementById('viewLatitude').textContent = document.getElementById('editLatitude').value || 'Not provided';
            document.getElementById('viewLongitude').textContent = document.getElementById('editLongitude').value || 'Not provided';
            
            // Update GPS coordinates for Google Maps
            currentStationLatitude = document.getElementById('editLatitude').value;
            currentStationLongitude = document.getElementById('editLongitude').value;
            
            // Update Google Maps button visibility
            const mapsBtn = document.getElementById('viewOnMapsBtn');
            if (currentStationLatitude && currentStationLongitude && currentStationLatitude !== '' && currentStationLongitude !== '') {
                mapsBtn.style.display = 'inline-block';
            } else {
                mapsBtn.style.display = 'none';
            }
            
            // Update status badge
            const statusValue = document.getElementById('editStatus').value;
            let statusBadge = '';
            switch(statusValue) {
                case 'online':
                    statusBadge = '<span class="badge bg-success"><i class="fas fa-circle me-1"></i>Online</span>';
                    break;
                case 'offline':
                    statusBadge = '<span class="badge bg-danger"><i class="fas fa-circle me-1"></i>Offline</span>';
                    break;
                case 'maintenance':
                    statusBadge = '<span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Maintenance</span>';
                    break;
            }
            document.getElementById('viewStatus').innerHTML = statusBadge;
        } else {
            // Show validation errors
            let errorMessage = 'Please fix the following errors:\n';
            for (let field in data.errors) {
                errorMessage += '- ' + data.errors[field].join('\n- ') + '\n';
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the station information.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle state change in edit mode
document.getElementById('editState').addEventListener('change', function() {
    const stateId = this.value;
    loadDistrictsForEdit(stateId);
});

// Handle form submission for device configuration update
document.getElementById('deviceConfigForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const updateUrl = `/devices/${currentDeviceId}/update-config`;
    
    // Show loading state
    const submitBtn = document.querySelector('#deviceEditModeButtons button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    submitBtn.disabled = true;
    
    fetch(updateUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message at top of modal
            showDeviceSuccessMessage(data.message);
            
            // Switch back to view mode
            document.getElementById('deviceViewMode').style.display = 'block';
            document.getElementById('deviceEditMode').style.display = 'none';
            document.getElementById('deviceViewModeButtons').style.display = 'block';
            document.getElementById('deviceEditModeButtons').style.display = 'none';
            
            // Update view mode with new data
            document.getElementById('viewApiToken').value = document.getElementById('editApiToken').value;
            document.getElementById('viewMacAddress').textContent = document.getElementById('editMacAddress').value || 'Not set';
            document.getElementById('viewDataInterval').textContent = document.getElementById('editDataInterval').value + ' minutes';
            document.getElementById('viewDataCollectionTime').textContent = document.getElementById('editDataCollectionTime').value + ' minutes';
        } else {
            // Show validation errors
            let errorMessage = 'Please fix the following errors:\n';
            for (let field in data.errors) {
                errorMessage += '- ' + data.errors[field].join('\n- ') + '\n';
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the device configuration.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

let currentHistoricalDeviceId = null;

function showHistoricalData(deviceId, stationId, stationName) {
    currentHistoricalDeviceId = deviceId;
    
    // Set modal title
    document.getElementById('historicalStationName').textContent = stationName + ' (' + stationId + ')';
    
    // Set default date (today)
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').value = today;
    document.getElementById('endDate').value = today;
    
    // Show modal
    const historicalModal = new bootstrap.Modal(document.getElementById('historicalDataModal'));
    historicalModal.show();
    
    // Load data for today by default
    loadHistoricalData();
}

function loadHistoricalData() {
    if (!currentHistoricalDeviceId) {
        alert('No device selected');
        return;
    }
    
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Show loading indicator and ensure table is visible
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('dataTableContainer').style.display = 'block';
    document.getElementById('noDataMessage').style.display = 'none';
    document.getElementById('exportBtn').disabled = true;
    
    // Clear existing table data
    document.getElementById('historicalDataBody').innerHTML = '';
    
    // Build URL with filters
    let url = `/devices/${currentHistoricalDeviceId}/historical-data`;
    const params = new URLSearchParams();
    
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading indicator
        document.getElementById('loadingIndicator').style.display = 'none';
        
        if (data.success && data.data.length > 0) {
            // Populate table with data
            populateHistoricalTable(data.data);
            document.getElementById('noDataMessage').style.display = 'none';
            document.getElementById('exportBtn').disabled = false;
        } else {
            // Show no data message (table headers remain visible)
            document.getElementById('noDataMessage').style.display = 'block';
            document.getElementById('exportBtn').disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loadingIndicator').style.display = 'none';
        document.getElementById('noDataMessage').style.display = 'block';
        document.getElementById('exportBtn').disabled = true;
        alert('An error occurred while loading historical data.');
    });
}

function populateHistoricalTable(data) {
    const tbody = document.getElementById('historicalDataBody');
    tbody.innerHTML = '';
    
    data.forEach(reading => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${reading.reading_time}</td>
            <td>${reading.temperature}</td>
            <td>${reading.humidity}</td>
            <td>${reading.rssi}</td>
            <td>${reading.battery_voltage}</td>
            <td>
                <span class="badge ${reading.type === 'Manual' ? 'bg-warning' : 'bg-info'}">
                    ${reading.type}
                </span>
            </td>
            <td>${reading.created_at}</td>
        `;
        tbody.appendChild(row);
    });
}

function resetFilters() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').value = today;
    document.getElementById('endDate').value = today;
    loadHistoricalData();
}

function exportToExcel() {
    if (!currentHistoricalDeviceId) {
        alert('No device selected');
        return;
    }
    
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Build export URL
    let url = `/devices/${currentHistoricalDeviceId}/export-data`;
    const params = new URLSearchParams();
    
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Create a temporary link and trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function validateDateRange() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // If either date is empty, it's valid (will use defaults)
    if (!startDate || !endDate) {
        return true;
    }
    
    // Convert to Date objects for comparison
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    // Check if start date is later than end date
    if (start > end) {
        alert('Start date cannot be later than end date. Please select a valid date range.');
        return false;
    }
    
    return true;
}

function validateAndLoadData() {
    if (validateDateRange()) {
        loadHistoricalData();
    }
}

let currentStationLatitude = null;
let currentStationLongitude = null;

function openGoogleMaps() {
    if (currentStationLatitude && currentStationLongitude) {
        const url = `https://www.google.com/maps?q=${currentStationLatitude},${currentStationLongitude}`;
        window.open(url, '_blank');
    } else {
        alert('GPS coordinates are not available for this station.');
    }
}

function showStationSuccessMessage(message) {
    document.getElementById('stationSuccessText').textContent = message;
    document.getElementById('stationSuccessMessage').style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideStationSuccessMessage();
    }, 5000);
}

function hideStationSuccessMessage() {
    document.getElementById('stationSuccessMessage').style.display = 'none';
}

function showDeviceSuccessMessage(message) {
    document.getElementById('deviceSuccessText').textContent = message;
    document.getElementById('deviceSuccessMessage').style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideDeviceSuccessMessage();
    }, 5000);
}

function hideDeviceSuccessMessage() {
    document.getElementById('deviceSuccessMessage').style.display = 'none';
}

// Initialize DataTable if available and devices exist
$(document).ready(function() {
    if ($.fn.DataTable && $('#devicesTable tbody tr').length > 0) {
        $('#devicesTable').DataTable({
            "pageLength": 15,
            "order": [[ 0, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [5] } // Disable sorting on Actions column
            ]
        });
    }
    
    // Add MAC address formatting to device config modal
    document.getElementById('editMacAddress').addEventListener('input', function() {
        formatMacAddressConfig(this);
    });
    
    // Add auto-update for historical data date inputs with validation
    document.getElementById('startDate').addEventListener('change', function() {
        if (currentHistoricalDeviceId && validateDateRange()) {
            loadHistoricalData();
        }
    });
    
    document.getElementById('endDate').addEventListener('change', function() {
        if (currentHistoricalDeviceId && validateDateRange()) {
            loadHistoricalData();
        }
    });
    
    // Handle state selection change
    $('#state_id').on('change', function() {
        const stateId = this.value;
        const currentDistrictId = '{{ request("district_id") }}';
        
        // Load districts based on selected state
        loadDistricts(stateId);
    });
    
    // Initialize districts on page load if state is already selected
    const currentStateId = '{{ request("state_id") }}';
    const currentDistrictId = '{{ request("district_id") }}';
    
    if (currentStateId) {
        loadDistricts(currentStateId, currentDistrictId);
    }
});
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-control-plaintext.border {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.375rem;
}

#stationInfoModal .modal-dialog {
    max-width: 800px;
}

#stationInfoModal .btn-group {
    gap: 0.5rem;
}
</style>
@endpush
@endsection