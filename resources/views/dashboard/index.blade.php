@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);">
                        <i class="fas fa-tachometer-alt text-white"></i>
                    </div>
                    <div>
                        <h1 class="mb-0 fw-bold">IoT Monitoring Dashboard</h1>
                        <p class="text-muted mb-0">Real-time station monitoring and control</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button id="refreshAll" class="btn btn-success btn-modern">
                        <i class="fas fa-sync-alt me-2"></i>Refresh All
                    </button>
                    <div class="badge bg-primary fs-6 px-3 py-2">
                        <i class="fas fa-circle me-1" id="connectionStatus"></i>
                        <span id="statusText">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-modern h-100">
                <div class="card-body text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); width: 60px; height: 60px;">
                        <i class="fas fa-check-circle text-white fa-lg"></i>
                    </div>
                    <h3 class="mb-1 text-success" id="onlineCount">-</h3>
                    <p class="text-muted mb-0">Online Stations</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern h-100">
                <div class="card-body text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); width: 60px; height: 60px;">
                        <i class="fas fa-times-circle text-white fa-lg"></i>
                    </div>
                    <h3 class="mb-1 text-danger" id="offlineCount">-</h3>
                    <p class="text-muted mb-0">Offline Stations</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern h-100">
                <div class="card-body text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); width: 60px; height: 60px;">
                        <i class="fas fa-tools text-white fa-lg"></i>
                    </div>
                    <h3 class="mb-1 text-warning" id="maintenanceCount">-</h3>
                    <p class="text-muted mb-0">Maintenance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern h-100">
                <div class="card-body text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); width: 60px; height: 60px;">
                        <i class="fas fa-microchip text-white fa-lg"></i>
                    </div>
                    <h3 class="mb-1 text-primary" id="totalCount">-</h3>
                    <p class="text-muted mb-0">Total Stations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-filter me-2"></i>Filter Stations
                    </h6>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllFilters()">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter" onchange="applyFilters()">
                                <option value="">All Status</option>
                                <option value="online">Online</option>
                                <option value="offline">Offline</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="stateFilter" class="form-label">State</label>
                            <select class="form-select" id="stateFilter" onchange="onStateChange()">
                                <option value="">All States</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="districtFilter" class="form-label">District</label>
                            <select class="form-select" id="districtFilter" onchange="applyFilters()">
                                <option value="">All Districts</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" data-state="{{ $district->state_id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="searchFilter" class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchFilter" 
                                   placeholder="Station name or ID..." onkeyup="applyFilters()">
                        </div>
                    </div>
                    
                    <!-- Active Filters Display -->
                    <div id="activeFilters" class="mt-3" style="display: none;">
                        <small class="text-muted">Active Filters:</small>
                        <div id="filterTags" class="mt-1"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stations Grid -->
    <div class="row" id="stationsGrid">
        <!-- Station cards will be dynamically loaded here -->
    </div>
</div>

<!-- Station Detail Modal -->
<div class="modal fade" id="stationDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Station Details - <span id="modalStationName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Station Info -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info me-2"></i>Station Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Station ID</label>
                                        <p class="mb-2" id="modalStationId">-</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Status</label>
                                        <p class="mb-2" id="modalStationStatus">-</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Location</label>
                                        <p class="mb-2" id="modalStationLocation">-</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">GPS Coordinates</label>
                                        <p class="mb-2" id="modalGpsCoordinates">-</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Last Seen</label>
                                        <p class="mb-2" id="modalLastSeen">-</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">MAC Address</label>
                                        <p class="mb-2" id="modalMacAddress">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Readings -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-thermometer-half me-2"></i>Current Readings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <i class="fas fa-thermometer-half text-danger fa-2x mb-2"></i>
                                            <h4 class="mb-1" id="modalTemperature">-</h4>
                                            <small class="text-muted">Temperature (°C)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <i class="fas fa-tint text-info fa-2x mb-2"></i>
                                            <h4 class="mb-1" id="modalHumidity">-</h4>
                                            <small class="text-muted">Humidity (%)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <i class="fas fa-battery-three-quarters text-success fa-2x mb-2"></i>
                                            <h4 class="mb-1" id="modalBatteryVoltage">-</h4>
                                            <small class="text-muted">Battery (V)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <i class="fas fa-signal text-primary fa-2x mb-2"></i>
                                            <h4 class="mb-1" id="modalRssi">-</h4>
                                            <small class="text-muted">RSSI (dBm)</small>
                                        </div>
                                    </div>
                                </div>
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

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bell me-2"></i>Notification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); width: 60px; height: 60px;">
                        <i class="fas fa-check-circle text-white fa-lg"></i>
                    </div>
                    <p class="mb-0" id="notificationMessage">Request sent successfully</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Historical Data Modal -->
<div class="modal fade" id="historicalModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line me-2"></i>Historical Data - <span id="histModalStationName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Date Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="histStartDate" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="histStartDate">
                    </div>
                    <div class="col-md-3">
                        <label for="histEndDate" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="histEndDate">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary" onclick="loadHistoricalCharts()">
                            <i class="fas fa-filter me-1"></i>Load Data
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportCurrentHistoricalData()">
                            <i class="fas fa-download me-1"></i>Export Data
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs" id="historyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="temp-humidity-tab" data-bs-toggle="tab" 
                                data-bs-target="#temp-humidity" type="button" role="tab">
                            <i class="fas fa-thermometer-half me-1"></i>Temperature & Humidity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="battery-tab" data-bs-toggle="tab" 
                                data-bs-target="#battery" type="button" role="tab">
                            <i class="fas fa-battery-three-quarters me-1"></i>Battery Voltage
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="historyTabContent">
                    <div class="tab-pane fade show active" id="temp-humidity" role="tabpanel">
                        <div class="mt-3">
                            <canvas id="tempHumidityChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="battery" role="tabpanel">
                        <div class="mt-3">
                            <canvas id="batteryChart" height="100"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let refreshInterval;
let currentStationId = null;
let tempHumidityChart = null;
let batteryChart = null;
let userRole = '{{ $user->role ?? "user" }}'; // Pass user role to JavaScript
let allStationsData = []; // Store all stations for filtering
let filteredStationsData = []; // Store filtered stations

// Initialize dashboard
$(document).ready(function() {
    loadDashboardData();
    startAutoRefresh();
    initializeDateFilters();
});

function loadDashboardData() {
    updateConnectionStatus('loading');
    
    // Add timeout to prevent hanging
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
    
    fetch('/dashboard/data', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId); // Clear timeout on successful response
        return response.json();
    })
    .then(data => {
        if (data.success) {
            allStationsData = data.stations; // Store all stations
            updateSummaryCards(data.summary);
            applyFilters(); // Apply current filters
            updateConnectionStatus('connected');
        } else {
            updateConnectionStatus('error');
            console.error('Failed to load dashboard data');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId); // Clear timeout on error
        if (error.name === 'AbortError') {
            console.log('Dashboard data loading timed out');
            updateConnectionStatus('timeout');
        } else {
            console.error('Error loading dashboard data:', error);
            updateConnectionStatus('error');
        }
    });
}

function updateSummaryCards(summary) {
    document.getElementById('onlineCount').textContent = summary.online || 0;
    document.getElementById('offlineCount').textContent = summary.offline || 0;
    document.getElementById('maintenanceCount').textContent = summary.maintenance || 0;
    document.getElementById('totalCount').textContent = summary.total || 0;
}

function renderStationCards(stations) {
    const grid = document.getElementById('stationsGrid');
    
    // Check if any dropdown is currently open - if so, don't re-render
    const hasOpenDropdown = document.querySelector('.dropdown-menu.show');
    if (hasOpenDropdown) {
        return; // Skip re-rendering to prevent dropdown flashing
    }
    
    grid.innerHTML = '';
    
    if (stations.length === 0) {
        grid.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="icon-wrapper mx-auto mb-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%); width: 80px; height: 80px;">
                        <i class="fas fa-microchip text-white fa-2x"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Active Stations</h4>
                    <p class="text-muted">No IoT stations are currently active for monitoring.</p>
                </div>
            </div>
        `;
        return;
    }
    
    stations.forEach(station => {
        const card = createStationCard(station);
        grid.appendChild(card);
    });
    
    // Simple Bootstrap dropdown initialization
    setTimeout(() => {
        // Initialize dropdowns using vanilla Bootstrap 5
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(dropdownToggle => {
            if (!dropdownToggle.hasAttribute('data-bs-dropdown-initialized')) {
                new bootstrap.Dropdown(dropdownToggle);
                dropdownToggle.setAttribute('data-bs-dropdown-initialized', 'true');
            }
        });
    }, 50);
}

function createStationCard(station) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6 mb-4';
    
    const statusColor = getStatusColor(station.status);
    const batteryLevel = getBatteryLevel(station.latest_reading?.battery_voltage);
    const signalStrength = getSignalStrength(station.latest_reading?.rssi);
    
    col.innerHTML = `
        <div class="card-modern h-100 station-card" data-station-id="${station.id}">
            <!-- Station Header -->
            <div class="station-header">
                <div class="station-info">
                    <h5 class="station-name mb-0">${station.station_name}</h5>
                    <div class="station-meta">
                        <span class="station-id">${station.station_id}</span>
                        <span class="status-badge badge bg-${statusColor}">${station.status}</span>
                    </div>
                </div>
                
                <!-- Station Menu -->
                <div class="station-menu">
                    <div class="dropdown">
                        <button class="btn btn-link p-0 station-menu-btn dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false" title="Station Menu"
                                id="stationDropdown${station.id}">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <i class="fas fa-microchip me-2"></i>${station.station_id}
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showStationDetail(${station.id})">
                                    <i class="fas fa-info-circle me-2"></i>Station Details
                                </a>
                            </li>
                            ${userRole === 'admin' ? `
                            <li>
                                <a class="dropdown-item" href="#" onclick="showHistoricalFromCard(${station.id}, '${station.station_name}', '${station.station_id}')">
                                    <i class="fas fa-chart-line me-2"></i>Historical Data
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            ` : ''}
                            <li>
                                <a class="dropdown-item text-success" href="#" onclick="getLatestUpdate(${station.id})">
                                    <i class="fas fa-sync-alt me-2"></i>Request Data
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Current Readings -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <i class="fas fa-thermometer-half text-danger mb-1"></i>
                            <div class="fw-bold">${station.latest_reading?.temperature || '-'}°C</div>
                            <small class="text-muted">Temperature</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <i class="fas fa-tint text-info mb-1"></i>
                            <div class="fw-bold">${station.latest_reading?.humidity || '-'}%</div>
                            <small class="text-muted">Humidity</small>
                        </div>
                    </div>
                </div>
                
                <!-- Status Indicators -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-battery-${batteryLevel.icon} text-${batteryLevel.color} me-2"></i>
                            <span class="small">
                                <span class="badge bg-${batteryLevel.color}">${batteryLevel.status}</span>
                                <div class="text-muted">${station.latest_reading?.battery_voltage || '-'}V</div>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-signal text-${signalStrength.color} me-2"></i>
                            <span class="small">
                                <span class="badge bg-${signalStrength.color}">${signalStrength.status}</span>
                                <div class="text-muted">${station.latest_reading?.rssi || '-'} dBm</div>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Location & Last Seen -->
                <div class="mb-3">
                    <small class="text-muted d-block">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        ${station.state_name || 'N/A'}, ${station.district_name || 'N/A'}
                    </small>
                    <small class="text-muted d-block">
                        <i class="fas fa-clock me-1"></i>
                        Last seen: ${station.last_seen_formatted || 'Never'}
                    </small>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

function getStatusColor(status) {
    switch(status) {
        case 'online': return 'success';
        case 'offline': return 'danger';
        case 'maintenance': return 'warning';
        default: return 'secondary';
    }
}

function getBatteryLevel(voltage) {
    if (!voltage) return { status: 'Unknown', color: 'secondary', icon: 'empty' };
    
    const v = parseFloat(voltage);
    if (v >= 3.7) return { status: 'Full', color: 'success', icon: 'full' };
    if (v >= 3.3) return { status: 'Medium', color: 'warning', icon: 'half' };
    return { status: 'Low', color: 'danger', icon: 'quarter' };
}

function getSignalStrength(rssi) {
    if (!rssi) return { status: 'Unknown', color: 'secondary' };
    
    const r = parseInt(rssi);
    if (r >= -70) return { status: 'Excellent', color: 'success' };
    if (r >= -85) return { status: 'Good', color: 'info' };
    if (r >= -100) return { status: 'Fair', color: 'warning' };
    return { status: 'Poor', color: 'danger' };
}

function updateConnectionStatus(status) {
    const statusEl = document.getElementById('connectionStatus');
    const textEl = document.getElementById('statusText');
    
    switch(status) {
        case 'loading':
            statusEl.className = 'fas fa-circle me-1 text-warning';
            textEl.textContent = 'Loading...';
            break;
        case 'connected':
            statusEl.className = 'fas fa-circle me-1 text-success';
            textEl.textContent = 'Connected';
            break;
        case 'error':
            statusEl.className = 'fas fa-circle me-1 text-danger';
            textEl.textContent = 'Connection Error';
            break;
    }
}

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        // Check if any dropdown is currently open or if user is interacting with modals
        const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
        const openModals = document.querySelectorAll('.modal.show');
        
        if (openDropdowns.length === 0 && openModals.length === 0) {
            // Only refresh if no dropdowns or modals are open
            loadDashboardData();
        } else {
            // Delay refresh if user is interacting
            console.log('Auto-refresh paused - user interaction detected');
        }
    }, 60000); // Refresh every 60 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function showStationDetail(stationId) {
    currentStationId = stationId;
    
    fetch(`/dashboard/station/${stationId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateStationModal(data.station);
            const modal = new bootstrap.Modal(document.getElementById('stationDetailModal'));
            modal.show();
        } else {
            alert('Failed to load station details');
        }
    })
    .catch(error => {
        console.error('Error loading station details:', error);
        alert('Error loading station details');
    });
}

function populateStationModal(station) {
    document.getElementById('modalStationName').textContent = station.station_name;
    document.getElementById('modalStationId').textContent = station.station_id;
    document.getElementById('modalStationStatus').innerHTML = getStatusBadge(station.status);
    document.getElementById('modalStationLocation').textContent = station.full_location;
    
    // Format GPS coordinates
    if (station.gps_latitude && station.gps_longitude) {
        const gpsText = `${parseFloat(station.gps_latitude).toFixed(6)}, ${parseFloat(station.gps_longitude).toFixed(6)}`;
        document.getElementById('modalGpsCoordinates').textContent = gpsText;
    } else {
        document.getElementById('modalGpsCoordinates').textContent = 'Not available';
    }
    
    document.getElementById('modalLastSeen').textContent = station.last_seen_formatted || 'Never';
    document.getElementById('modalMacAddress').textContent = station.mac_address || 'Not set';
    
    // Latest readings
    if (station.latest_reading) {
        document.getElementById('modalTemperature').textContent = station.latest_reading.temperature + '°C';
        document.getElementById('modalHumidity').textContent = station.latest_reading.humidity + '%';
        document.getElementById('modalBatteryVoltage').textContent = station.latest_reading.battery_voltage + 'V';
        document.getElementById('modalRssi').textContent = station.latest_reading.rssi + ' dBm';
    } else {
        document.getElementById('modalTemperature').textContent = 'No data';
        document.getElementById('modalHumidity').textContent = 'No data';
        document.getElementById('modalBatteryVoltage').textContent = 'No data';
        document.getElementById('modalRssi').textContent = 'No data';
    }
}

function getStatusBadge(status) {
    switch(status) {
        case 'online':
            return '<span class="badge bg-success"><i class="fas fa-circle me-1"></i>Online</span>';
        case 'offline':
            return '<span class="badge bg-danger"><i class="fas fa-circle me-1"></i>Offline</span>';
        case 'maintenance':
            return '<span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Maintenance</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function getLatestUpdate(stationId = null) {
    const targetStationId = stationId || currentStationId;
    if (!targetStationId) {
        alert('No station selected');
        return;
    }
    
    const btn = stationId ? 
        document.querySelector(`button[onclick="getLatestUpdate(${stationId})"]`) :
        document.querySelector('button[onclick="getLatestUpdate()"]');
    
    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 2000);
    }
    
    // Send MQTT update request to ESP32
    fetch(`/dashboard/station/${targetStationId}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show notification modal with data interval information
            const message = `Latest Data will update within ${data.data_interval} minutes`;
            document.getElementById('notificationMessage').textContent = message;
            
            const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
            notificationModal.show();
            
            // Don't refresh dashboard immediately - let auto-refresh handle it
            // This ensures Last Seen only updates on scheduled dashboard reload
            if (currentStationId === targetStationId) {
                // Refresh modal if it's open for this station
                showStationDetail(targetStationId);
            }
        } else {
            // Handle offline devices or other errors - log to console instead of popup
            let errorMessage = data.message || 'Failed to send update request';
            if (data.last_seen) {
                errorMessage += `\nLast seen: ${data.last_seen}`;
            }
            console.error('Request Data Error:', errorMessage);
        }
    })
    .catch(error => {
        console.error('Error requesting MQTT update:', error);
        // No popup message - silent failure, logged to console
    });
}

function showHistoricalData() {
    if (!currentStationId) {
        alert('No station selected');
        return;
    }
    
    document.getElementById('histModalStationName').textContent = 
        document.getElementById('modalStationName').textContent;
    
    const modal = new bootstrap.Modal(document.getElementById('historicalModal'));
    modal.show();
    
    // Load initial data for today
    loadHistoricalCharts();
}

function showHistoricalFromCard(stationId, stationName, stationIdText) {
    currentStationId = stationId;
    
    document.getElementById('histModalStationName').textContent = `${stationName} (${stationIdText})`;
    
    const modal = new bootstrap.Modal(document.getElementById('historicalModal'));
    modal.show();
    
    // Load initial data for today
    loadHistoricalCharts();
}

function initializeDateFilters() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('histStartDate').value = today;
    document.getElementById('histEndDate').value = today;
}

function loadHistoricalCharts() {
    if (!currentStationId) return;
    
    const startDate = document.getElementById('histStartDate').value;
    const endDate = document.getElementById('histEndDate').value;
    
    fetch(`/dashboard/station/${currentStationId}/historical?from_date=${startDate}&to_date=${endDate}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            createTempHumidityChart(data.readings);
            createBatteryChart(data.readings);
        } else {
            alert('Failed to load historical data');
        }
    })
    .catch(error => {
        console.error('Error loading historical data:', error);
        alert('Error loading historical data');
    });
}

function createTempHumidityChart(readings) {
    const ctx = document.getElementById('tempHumidityChart').getContext('2d');
    
    if (tempHumidityChart) {
        tempHumidityChart.destroy();
    }
    
    const labels = readings.map(r => new Date(r.reading_time).toLocaleTimeString());
    const temperatureData = readings.map(r => r.temperature);
    const humidityData = readings.map(r => r.humidity);
    
    tempHumidityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Temperature (°C)',
                data: temperatureData,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                yAxisID: 'y'
            }, {
                label: 'Humidity (%)',
                data: humidityData,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Humidity (%)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

function createBatteryChart(readings) {
    const ctx = document.getElementById('batteryChart').getContext('2d');
    
    if (batteryChart) {
        batteryChart.destroy();
    }
    
    const labels = readings.map(r => new Date(r.reading_time).toLocaleTimeString());
    const batteryData = readings.map(r => r.battery_voltage);
    
    batteryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Battery Voltage (V)',
                data: batteryData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Voltage (V)'
                    },
                    min: 0,
                    max: 5
                }
            }
        }
    });
}

// Refresh all stations
document.getElementById('refreshAll').addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    loadDashboardData();
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }, 2000);
});

function exportStationData() {
    if (!currentStationId) {
        alert('No station selected');
        return;
    }
    
    // Get date range from historical modal or use default
    const startDate = document.getElementById('histStartDate').value || new Date().toISOString().split('T')[0];
    const endDate = document.getElementById('histEndDate').value || new Date().toISOString().split('T')[0];
    
    // Get the actual station_id from the current station data
    const currentStation = allStationsData.find(s => s.id == currentStationId);
    const stationId = currentStation ? currentStation.station_id : currentStationId;
    
    // Create export URL - using stations route with station_id
    const exportUrl = `/stations/${stationId}/export-data?from_date=${startDate}&to_date=${endDate}`;
    
    // Trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportCurrentHistoricalData() {
    if (!currentStationId) {
        alert('No station selected');
        return;
    }
    
    // Get the selected date range from the historical modal
    const startDate = document.getElementById('histStartDate').value || new Date().toISOString().split('T')[0];
    const endDate = document.getElementById('histEndDate').value || new Date().toISOString().split('T')[0];
    
    // Get station name from modal title
    const stationName = document.getElementById('histModalStationName').textContent;
    
    // Get the actual station_id from the current station data
    const currentStation = allStationsData.find(s => s.id == currentStationId);
    const stationId = currentStation ? currentStation.station_id : currentStationId;
    
    // Create export URL with selected date range - using stations route with station_id
    const exportUrl = `/stations/${stationId}/export-data?from_date=${startDate}&to_date=${endDate}`;
    
    // Trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show feedback with date range
    const startFormatted = new Date(startDate).toLocaleDateString();
    const endFormatted = new Date(endDate).toLocaleDateString();
    alert(`Exporting data for ${stationName} from ${startFormatted} to ${endFormatted}`);
}


// Filter Functions
function applyFilters() {
    if (!allStationsData.length) return;
    
    // Don't apply filters if dropdowns are open to prevent flashing
    const hasOpenDropdown = document.querySelector('.dropdown-menu.show');
    if (hasOpenDropdown) {
        setTimeout(() => applyFilters(), 1000); // Retry after dropdown closes
        return;
    }
    
    const statusFilter = document.getElementById('statusFilter').value;
    const stateFilter = document.getElementById('stateFilter').value;
    const districtFilter = document.getElementById('districtFilter').value;
    const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
    
    filteredStationsData = allStationsData.filter(station => {
        // Status filter
        if (statusFilter && station.status !== statusFilter) {
            return false;
        }
        
        // State filter (station data includes state info directly)
        if (stateFilter && (!station.state_id || station.state_id != stateFilter)) {
            return false;
        }
        
        // District filter (station data includes district info directly)
        if (districtFilter && (!station.district_id || station.district_id != districtFilter)) {
            return false;
        }
        
        // Search filter
        if (searchFilter) {
            const searchText = station.station_name.toLowerCase() + ' ' + station.station_id.toLowerCase();
            if (!searchText.includes(searchFilter)) {
                return false;
            }
        }
        
        return true;
    });
    
    renderStationCards(filteredStationsData);
    updateFilterSummary();
    showActiveFilters();
}

function onStateChange() {
    const stateFilter = document.getElementById('stateFilter').value;
    const districtSelect = document.getElementById('districtFilter');
    
    // Reset district filter when state changes
    districtSelect.value = '';
    
    // Show/hide district options based on selected state
    const districtOptions = districtSelect.querySelectorAll('option');
    districtOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block'; // Always show "All Districts"
        } else {
            const optionState = option.getAttribute('data-state');
            option.style.display = (!stateFilter || optionState === stateFilter) ? 'block' : 'none';
        }
    });
    
    applyFilters();
}

function clearAllFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('stateFilter').value = '';
    document.getElementById('districtFilter').value = '';
    document.getElementById('searchFilter').value = '';
    
    // Reset district options visibility
    const districtOptions = document.getElementById('districtFilter').querySelectorAll('option');
    districtOptions.forEach(option => {
        option.style.display = 'block';
    });
    
    applyFilters();
}

function updateFilterSummary() {
    const totalStations = allStationsData.length;
    const filteredStations = filteredStationsData.length;
    
    if (filteredStations !== totalStations) {
        // Update summary cards to reflect filtered data
        const filteredSummary = {
            total: filteredStations,
            online: filteredStationsData.filter(s => s.status === 'online').length,
            offline: filteredStationsData.filter(s => s.status === 'offline').length,
            maintenance: filteredStationsData.filter(s => s.status === 'maintenance').length
        };
        updateSummaryCards(filteredSummary);
    } else {
        // Show original summary when no filters applied
        loadDashboardData();
    }
}

function showActiveFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const stateFilter = document.getElementById('stateFilter').value;
    const districtFilter = document.getElementById('districtFilter').value;
    const searchFilter = document.getElementById('searchFilter').value;
    
    const activeFiltersDiv = document.getElementById('activeFilters');
    const filterTagsDiv = document.getElementById('filterTags');
    
    const filters = [];
    
    if (statusFilter) {
        filters.push(`<span class="badge bg-primary me-2">Status: ${statusFilter}</span>`);
    }
    if (stateFilter) {
        const stateName = document.querySelector(`#stateFilter option[value="${stateFilter}"]`).textContent;
        filters.push(`<span class="badge bg-info me-2">State: ${stateName}</span>`);
    }
    if (districtFilter) {
        const districtName = document.querySelector(`#districtFilter option[value="${districtFilter}"]`).textContent;
        filters.push(`<span class="badge bg-success me-2">District: ${districtName}</span>`);
    }
    if (searchFilter) {
        filters.push(`<span class="badge bg-warning me-2">Search: "${searchFilter}"</span>`);
    }
    
    if (filters.length > 0) {
        filterTagsDiv.innerHTML = filters.join('');
        activeFiltersDiv.style.display = 'block';
    } else {
        activeFiltersDiv.style.display = 'none';
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
    if (tempHumidityChart) tempHumidityChart.destroy();
    if (batteryChart) batteryChart.destroy();
});
</script>

<style>
.station-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    will-change: transform;
}

.station-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Prevent card flickering during updates */
.station-card.updating {
    pointer-events: none;
}

.icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-modern {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.btn-modern {
    border-radius: 8px;
    font-weight: 500;
    padding: 8px 16px;
}

.bg-light {
    background-color: #f8f9fa !important;
}

#historicalModal .modal-dialog {
    max-width: 1200px;
}

.nav-tabs {
    min-height: 60px;
    align-items: center;
    display: flex;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    height: 60px;
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
}

.nav-tabs .nav-link.active {
    background-color: transparent;
    border-bottom: 2px solid var(--bs-primary);
    color: var(--bs-primary);
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
}

.dropdown-item {
    padding: 8px 16px;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(4px);
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.dropdown-divider {
    margin: 4px 0;
}

/* Station Header Styles */
.station-header {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-radius: 12px 12px 0 0;
    color: white;
}

.station-info {
    flex: 1;
}

.station-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.station-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.station-id {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.station-menu {
    margin-left: 1rem;
}

.station-menu-btn {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    border: none;
    background: none;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.station-menu-btn:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.15);
    transform: scale(1.1);
}

.station-menu-btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* Hide dropdown toggle arrow for station menu button */
.station-menu-btn.dropdown-toggle::after {
    display: none;
}

/* Ensure dropdown menu stays visible and positioned correctly */
.station-menu .dropdown-menu {
    z-index: 1055;
    min-width: 200px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: none;
    border-radius: 8px;
}

/* Prevent dropdown from closing too quickly */
.station-menu .dropdown-menu.show {
    display: block !important;
    opacity: 1;
    visibility: visible;
}

/* Prevent animation conflicts */
.station-menu .dropdown-menu {
    transition: none;
    animation: none;
}


/* Station Card Adjustments */
.station-card .card-body {
    padding: 1.25rem;
}

/* Dropdown Header Styling */
.dropdown-header {
    color: #495057;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    .station-header {
        padding: 0.875rem 1rem;
    }
    
    .station-name {
        font-size: 1rem;
    }
    
    .station-meta {
        gap: 0.5rem;
    }
    
    .station-id {
        font-size: 0.8rem;
    }
    
    .station-menu {
        margin-left: 0.5rem;
    }
}
</style>
@endpush
@endsection