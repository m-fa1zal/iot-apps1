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
                        <p class="text-muted mb-0">Create a new IoT monitoring station</p>
                    </div>
                </div>
                <a href="{{ route('stations.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Stations
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Create Station Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('stations.store') }}" id="createStationForm">
                        @csrf

                        <!-- Station Information Section -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Station Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="station_name" class="form-label">Station Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('station_name') is-invalid @enderror" 
                                               id="station_name" name="station_name" value="{{ old('station_name') }}" 
                                               placeholder="Enter station name" required>
                                        @error('station_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mac_address" class="form-label">MAC Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('mac_address') is-invalid @enderror" 
                                               id="mac_address" name="mac_address" value="{{ old('mac_address') }}" 
                                               placeholder="AA:BB:CC:DD:EE:FF" pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$" required>
                                        @error('mac_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Format: AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information Section -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Location Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="state_id" class="form-label">State <span class="text-danger">*</span></label>
                                        <select class="form-select @error('state_id') is-invalid @enderror" 
                                                id="state_id" name="state_id" required>
                                            <option value="">Select State</option>
                                            @foreach($states as $state)
                                                <option value="{{ $state->id }}" {{ old('state_id') == $state->id ? 'selected' : '' }}>
                                                    {{ $state->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('state_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="district_id" class="form-label">District <span class="text-danger">*</span></label>
                                        <select class="form-select @error('district_id') is-invalid @enderror" 
                                                id="district_id" name="district_id" required>
                                            <option value="">Select District</option>
                                        </select>
                                        @error('district_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="2" 
                                          placeholder="Enter full address (optional)">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gps_latitude" class="form-label">GPS Latitude</label>
                                        <input type="number" class="form-control @error('gps_latitude') is-invalid @enderror" 
                                               id="gps_latitude" name="gps_latitude" value="{{ old('gps_latitude') }}" 
                                               step="0.000001" min="-90" max="90" placeholder="e.g., 3.1390">
                                        @error('gps_latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gps_longitude" class="form-label">GPS Longitude</label>
                                        <input type="number" class="form-control @error('gps_longitude') is-invalid @enderror" 
                                               id="gps_longitude" name="gps_longitude" value="{{ old('gps_longitude') }}" 
                                               step="0.000001" min="-180" max="180" placeholder="e.g., 101.6869">
                                        @error('gps_longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Device Configuration Section -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cog me-2"></i>Device Configuration
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="data_interval" class="form-label">Data Interval (minutes) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('data_interval') is-invalid @enderror" 
                                               id="data_interval" name="data_interval" value="{{ old('data_interval', 30) }}" 
                                               min="1" max="60" required>
                                        @error('data_interval')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">How often the device collects sensor readings (1-60 minutes)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="data_collection_time" class="form-label">Data Collection Time (minutes) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('data_collection_time') is-invalid @enderror" 
                                               id="data_collection_time" name="data_collection_time" value="{{ old('data_collection_time', 60) }}" 
                                               min="10" max="300" required>
                                        @error('data_collection_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Duration for each data collection session (10-300 minutes)</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('stations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Create Station
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // State/District dependency
    const stateSelect = document.getElementById('state_id');
    const districtSelect = document.getElementById('district_id');
    
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        
        // Clear districts
        districtSelect.innerHTML = '<option value="">Select District</option>';
        districtSelect.disabled = !stateId;
        
        if (stateId) {
            // Show loading
            districtSelect.innerHTML = '<option value="">Loading districts...</option>';
            
            fetch(`/api/districts?state_id=${stateId}`)
                .then(response => response.json())
                .then(districts => {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        // Restore old selection if available
                        if ('{{ old("district_id") }}' == district.id) {
                            option.selected = true;
                        }
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading districts:', error);
                    districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                });
        }
    });

    // Trigger change event if state is pre-selected (for old input)
    if (stateSelect.value) {
        stateSelect.dispatchEvent(new Event('change'));
    }

    // MAC address formatting
    const macInput = document.getElementById('mac_address');
    macInput.addEventListener('input', function() {
        let value = this.value.replace(/[^a-fA-F0-9]/g, ''); // Remove non-hex characters
        let formatted = '';
        
        for (let i = 0; i < value.length && i < 12; i += 2) {
            if (i > 0) formatted += ':';
            formatted += value.substr(i, 2);
        }
        
        this.value = formatted.toUpperCase();
    });

    // Form submission handling
    const form = document.getElementById('createStationForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Station...';
    });
});
</script>
@endpush
@endsection