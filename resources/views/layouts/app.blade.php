<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IoT Apps') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e40af;
            --secondary-color: #f1f5f9;
            --accent-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1e293b;
            --navbar-dark: #1e3a8a;
            --navbar-darker: #1e40af;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            color: var(--text-primary);
        }
        
        .navbar-modern {
            background: linear-gradient(135deg, var(--navbar-darker) 0%, var(--navbar-dark) 100%);
            box-shadow: var(--shadow);
            border: none;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 0 4px;
            padding: 8px 16px !important;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .card-modern {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
            background-color: white;
        }
        
        .card-modern:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .card-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            border: none;
            padding: 1.5rem;
        }
        
        .btn-modern {
            border-radius: 12px;
            font-weight: 500;
            padding: 12px 24px;
            transition: all 0.2s ease;
            border: none;
        }
        
        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            color: white;
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);
            color: white;
        }
        
        .stats-card {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 16px;
            background: white;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .stats-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: var(--secondary-color);
        }
        
        /* Readonly field styling for profile modal */
        #profileModal input.readonly-field,
        #profileModal textarea.readonly-field,
        #profileModal select.readonly-field {
            background-color: #fce7f3 !important;
            color: #be185d !important;
            cursor: not-allowed !important;
            border-color: #ced4da !important;
        }

        #profileModal input.readonly-field:focus,
        #profileModal textarea.readonly-field:focus,
        #profileModal select.readonly-field:focus {
            background-color: #fce7f3 !important;
            color: #be185d !important;
            box-shadow: 0 0 0 0.2rem rgba(236, 72, 153, 0.15) !important;
            border-color: #f9a8d4 !important;
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        @auth
        <!-- MODERN NAVIGATION BAR -->
        <nav class="navbar navbar-expand-lg navbar-dark navbar-modern" style="min-height: 70px; z-index: 1000;">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold" href="{{ url('/dashboard') }}">
                    <i class="fas fa-microchip me-2"></i>IoT Apps
                </a>
                
                <!-- Navigation links -->
                <div class="d-flex flex-wrap align-items-center">
                    <ul class="navbar-nav d-flex flex-row me-4">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ url('/dashboard') }}">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('users.index') }}">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ url('/devices') }}">
                                <i class="fas fa-microchip me-2"></i>Devices
                            </a>
                        </li>
                    </ul>
                    
                    <!-- User dropdown -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px; font-weight: 600;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="showProfileModal()">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="fas fa-user-cog me-2"></i>Profile Management
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Success Alert -->
                    <div class="alert alert-success alert-dismissible fade" role="alert" id="profileSuccessAlert" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="profileSuccessMessage">Profile updated successfully!</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_name" class="form-label">Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="profile_name" name="name" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="profile_email" name="email" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5 class="text-muted mb-3"><i class="fas fa-key me-1"></i>Change Password (Optional)</h5>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="profile_current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="profile_current_password" name="current_password" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="profile_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="profile_password" name="password" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="profile_password_confirmation" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="profile_password_confirmation" name="password_confirmation" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="mt-3">
                        <small class="text-muted" id="profile_updated_at_text">Updated At: N/A</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editProfileBtn">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Profile Modal Scripts -->
    <script>
    // Profile Modal Functions
    function showProfileModal() {
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('profileModal'));
        modal.show();
        
        // Load profile data
        loadProfileData();
    }

    function loadProfileData() {
        // Hide any success alerts
        hideProfileSuccessAlert();
        
        // Set modal to view mode
        setProfileModalMode('view');
        
        // Load profile data
        populateProfileForm();
    }

    function populateProfileForm() {
        // Get current user data from server-side (passed via PHP)
        @auth
        document.getElementById('profile_name').value = "{{ Auth::user()->name }}";
        document.getElementById('profile_email').value = "{{ Auth::user()->email }}";
        
        // Update updated at text at bottom
        const updatedAtText = "{{ Auth::user()->updated_at ? Auth::user()->updated_at->format('M j, Y H:i') : 'N/A' }}";
        document.getElementById('profile_updated_at_text').textContent = `Updated At: ${updatedAtText}`;
        @endauth
        
        // Clear password fields
        document.getElementById('profile_current_password').value = '';
        document.getElementById('profile_password').value = '';
        document.getElementById('profile_password_confirmation').value = '';
    }

    function setProfileModalMode(mode) {
        console.log('Setting profile modal mode to:', mode);
        const form = document.getElementById('profileForm');
        const editBtn = document.getElementById('editProfileBtn');
        
        // Hide success alert when switching modes
        if (mode === 'edit') {
            hideProfileSuccessAlert();
        }
        
        if (mode === 'view') {
            // Set all form fields to readonly - NO highlighting in view mode
            form.querySelectorAll('input').forEach(input => {
                input.readOnly = true;
                input.classList.remove('readonly-field');
            });
            
            // Show edit button
            editBtn.style.display = 'block';
            editBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Edit Profile';
            editBtn.onclick = () => setProfileModalMode('edit');
        } else if (mode === 'edit') {
            // Enable form fields except email (usually not editable)
            // ONLY highlight fields that cannot be edited in edit mode
            form.querySelectorAll('input').forEach(input => {
                if (input.name === 'email') {
                    input.readOnly = true;
                    input.classList.add('readonly-field');
                    console.log('Made readonly (pink highlight):', input.name);
                } else {
                    input.readOnly = false;
                    input.classList.remove('readonly-field');
                    console.log('Made editable:', input.name);
                }
            });
            
            // Change button to save
            editBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
            editBtn.onclick = saveProfileData;
        }
    }

    function saveProfileData() {
        const form = document.getElementById('profileForm');
        const formData = new FormData(form);
        const allData = Object.fromEntries(formData);
        
        // Filter data to only include expected fields
        const data = {
            name: allData.name,
            email: allData.email,
            current_password: allData.current_password,
            password: allData.password,
            password_confirmation: allData.password_confirmation
        };
        
        console.log('Sending profile data:', data);
        
        // Show loading state
        const editBtn = document.getElementById('editProfileBtn');
        const originalText = editBtn.innerHTML;
        editBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        editBtn.disabled = true;
        
        // Send update request
        fetch('/profile', {
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
                // Close modal and return to main page
                const modal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
                modal.hide();
                
                // Reload page to reflect changes
                location.reload();
            } else {
                let errorMessage = 'Unknown error';
                
                if (data.message) {
                    errorMessage = data.message;
                } else if (data.errors) {
                    const errors = Object.values(data.errors).flat();
                    errorMessage = errors.join(', ');
                }
                
                console.error('Profile update failed:', data);
                alert('Error updating profile: ' + errorMessage);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error updating profile. Please check console for details.');
        })
        .finally(() => {
            // Reset button
            editBtn.innerHTML = originalText;
            editBtn.disabled = false;
        });
    }

    function showProfileSuccessAlert(message) {
        const alert = document.getElementById('profileSuccessAlert');
        const messageSpan = document.getElementById('profileSuccessMessage');
        
        messageSpan.textContent = message;
        alert.style.display = 'block';
        alert.classList.add('show');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            hideProfileSuccessAlert();
        }, 5000);
    }

    function hideProfileSuccessAlert() {
        const alert = document.getElementById('profileSuccessAlert');
        alert.style.display = 'none';
        alert.classList.remove('show');
    }
    </script>
    
    @stack('scripts')
</body>
</html>