@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3" style="background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);">
                        <i class="fas fa-plus text-white"></i>
                    </div>
                    <div>
                        <h1 class="mb-0 fw-bold">Add New Station</h1>
                        <p class="text-muted mb-0">Configure a new IoT monitoring station</p>
                    </div>
                </div>
                <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-modern">
                    <i class="fas fa-arrow-left me-2"></i>Back to Devices
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Station Form -->
    <form method="POST" action="{{ route('devices.store') }}" id="stationForm">
        @csrf
        
        <div class="card-modern">
            <!-- Navigation Tabs -->
            <div class="card-header-modern">
                <ul class="nav nav-tabs card-header-tabs" id="stationTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="station-info-tab" data-bs-toggle="tab" 
                                data-bs-target="#station-info" type="button" role="tab" 
                                aria-controls="station-info" aria-selected="true">
                            <i class="fas fa-info-circle me-2"></i>Station Information
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="device-config-tab" data-bs-toggle="tab" 
                                data-bs-target="#device-config" type="button" role="tab" 
                                aria-controls="device-config" aria-selected="false">
                            <i class="fas fa-cog me-2"></i>Device Configuration
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="stationTabsContent">
                    
                    <!-- Part A - Station Information -->
                    <div class="tab-pane fade show active" id="station-info" role="tabpanel" aria-labelledby="station-info-tab">
                        <div class="row g-4">
                            <!-- Station ID (Auto-generated) -->
                            <div class="col-md-6">
                                <label class="form-label">Station ID</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-microchip text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control" value="Auto-generated after creation" readonly>
                                    <span class="input-group-text">
                                        <i class="fas fa-info-circle text-muted" title="Station ID will be generated automatically based on district code"></i>
                                    </span>
                                </div>
                                <small class="form-text text-muted">Station ID will be generated automatically (e.g., KL01-1001)</small>
                            </div>

                            <!-- Station Name -->
                            <div class="col-md-6">
                                <label for="station_name" class="form-label required">Station Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-tag text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control @error('station_name') is-invalid @enderror" 
                                           id="station_name" name="station_name" value="{{ old('station_name') }}" 
                                           placeholder="Enter station name" required>
                                </div>
                                @error('station_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- State -->
                            <div class="col-md-6">
                                <label for="state_id" class="form-label required">State</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-map text-primary"></i>
                                    </span>
                                    <select class="form-select @error('state_id') is-invalid @enderror" 
                                            id="state_id" name="state_id" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}" {{ old('state_id') == $state->id ? 'selected' : '' }}>
                                                {{ $state->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('state_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- District -->
                            <div class="col-md-6">
                                <label for="district_id" class="form-label required">District</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                    </span>
                                    <select class="form-select @error('district_id') is-invalid @enderror" 
                                            id="district_id" name="district_id" required>
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                                @error('district_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-home text-primary"></i>
                                    </span>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" 
                                              placeholder="Enter complete address (optional)">{{ old('address') }}</textarea>
                                </div>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- GPS Location -->
                            <div class="col-md-6">
                                <label for="gps_latitude" class="form-label">GPS Latitude</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-crosshairs text-primary"></i>
                                    </span>
                                    <input type="number" step="any" class="form-control @error('gps_latitude') is-invalid @enderror" 
                                           id="gps_latitude" name="gps_latitude" value="{{ old('gps_latitude') }}" 
                                           placeholder="e.g., 3.1390" min="-90" max="90">
                                </div>
                                @error('gps_latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="gps_longitude" class="form-label">GPS Longitude</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-crosshairs text-primary"></i>
                                    </span>
                                    <input type="number" step="any" class="form-control @error('gps_longitude') is-invalid @enderror" 
                                           id="gps_longitude" name="gps_longitude" value="{{ old('gps_longitude') }}" 
                                           placeholder="e.g., 101.6869" min="-180" max="180">
                                </div>
                                @error('gps_longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-primary-modern btn-modern" onclick="switchToTab('device-config')">
                                Next: Device Configuration <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Part B - Device Configuration -->
                    <div class="tab-pane fade" id="device-config" role="tabpanel" aria-labelledby="device-config-tab">
                        <div class="row g-4">
                            <!-- MAC Address -->
                            <div class="col-md-6">
                                <label for="mac_address" class="form-label required">MAC Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-ethernet text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control @error('mac_address') is-invalid @enderror" 
                                           id="mac_address" name="mac_address" value="{{ old('mac_address') }}" 
                                           placeholder="e.g., AA:BB:CC:DD:EE:FF" pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$" 
                                           maxlength="17" required>
                                </div>
                                <small class="form-text text-muted">Format: XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX</small>
                                @error('mac_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Data Interval -->
                            <div class="col-md-6">
                                <label for="data_interval_minutes" class="form-label required">Data Interval (Minutes)</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-clock text-primary"></i>
                                    </span>
                                    <input type="number" class="form-control @error('data_interval_minutes') is-invalid @enderror" 
                                           id="data_interval_minutes" name="data_interval_minutes" 
                                           value="{{ old('data_interval_minutes', 2) }}" 
                                           placeholder="2" min="1" max="1440" required>
                                    <span class="input-group-text">min</span>
                                </div>
                                <small class="form-text text-muted">How often the device sends data (1-1440 minutes)</small>
                                @error('data_interval_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Data Collection Time -->
                            <div class="col-md-6">
                                <label for="data_collection_time_minutes" class="form-label required">Data Collection Time (Minutes)</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-stopwatch text-primary"></i>
                                    </span>
                                    <input type="number" class="form-control @error('data_collection_time_minutes') is-invalid @enderror" 
                                           id="data_collection_time_minutes" name="data_collection_time_minutes" 
                                           value="{{ old('data_collection_time_minutes', 30) }}" 
                                           placeholder="30" min="1" max="60" required>
                                    <span class="input-group-text">min</span>
                                </div>
                                <small class="form-text text-muted">How long each data collection session lasts (1-60 minutes)</small>
                                @error('data_collection_time_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-modern" onclick="switchToTab('station-info')">
                                <i class="fas fa-arrow-left me-2"></i>Back: Station Information
                            </button>
                            <button type="submit" class="btn btn-success btn-modern">
                                <i class="fas fa-save me-2"></i>Create Station
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Tab switching function
function switchToTab(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId + '-tab'));
    tab.show();
}

// Cascading dropdown functionality
function loadDistricts(stateId, selectedDistrictId = null) {
    const districtSelect = document.getElementById('district_id');
    
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

// MAC Address formatting
function formatMacAddress(input) {
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

// Form validation
function validateStationInfo() {
    const requiredFields = ['station_name', 'state_id', 'district_id'];
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

function validateDeviceConfig() {
    const requiredFields = ['mac_address', 'data_interval_minutes', 'data_collection_time_minutes'];
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validate MAC address format
    const macField = document.getElementById('mac_address');
    const macPattern = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i;
    if (macField.value && !macPattern.test(macField.value)) {
        macField.classList.add('is-invalid');
        isValid = false;
    }
    
    return isValid;
}

// Initialize when document is ready
$(document).ready(function() {
    // Handle state selection change
    $('#state_id').on('change', function() {
        const stateId = this.value;
        loadDistricts(stateId);
    });
    
    // MAC address formatting
    $('#mac_address').on('input', function() {
        formatMacAddress(this);
    });
    
    // Real-time validation
    $('#station_name, #state_id, #district_id').on('change blur', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
    
    $('#mac_address, #data_interval_minutes, #data_collection_time_minutes').on('change blur', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
    
    // Tab switching with validation
    $('#device-config-tab').on('click', function(e) {
        if (!validateStationInfo()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Show alert
            alert('Please fill in all required fields in Station Information before proceeding.');
            return false;
        }
    });
    
    // Form submission validation
    $('#stationForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate both tabs
        if (!validateStationInfo()) {
            isValid = false;
            switchToTab('station-info');
        }
        
        if (!validateDeviceConfig()) {
            isValid = false;
            if (validateStationInfo()) { // Only switch if station info is valid
                switchToTab('device-config');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });
    
    // Initialize districts if state is already selected (for validation errors)
    const selectedStateId = $('#state_id').val();
    const selectedDistrictId = '{{ old("district_id") }}';
    
    if (selectedStateId) {
        loadDistricts(selectedStateId, selectedDistrictId);
    }
});
</script>
@endpush

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.nav-tabs .nav-link {
    border-radius: 0.5rem 0.5rem 0 0;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);
    color: white;
    border-color: var(--accent-color);
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.form-control:focus, .form-select:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
}

.btn-modern {
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary-modern {
    background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);
    border: none;
    color: white;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    color: white;
}

.card-modern {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.card-header-modern {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 1rem 1rem 0 0;
    padding: 1.5rem 1.5rem 0 1.5rem;
}

.icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection