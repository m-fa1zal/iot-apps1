@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users me-2"></i>User Management</h1>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add New User
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Users List ({{ $users->total() }} total)</h5>
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="usersTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $user->name }}</div>
                                                        @if($user->id === auth()->id())
                                                            <small class="text-muted">(You)</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $user->email }}
                                                @if($user->email_verified_at)
                                                    <i class="fas fa-check-circle text-success ms-1" title="Verified"></i>
                                                @else
                                                    <i class="fas fa-times-circle text-danger ms-1" title="Not Verified"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->role === 'admin')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-crown me-1"></i>Admin
                                                    </span>
                                                @else
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-user me-1"></i>User
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->last_login_at)
                                                    <small class="text-muted">{{ $user->last_login_at->format('M d, Y') }}</small><br>
                                                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                                @else
                                                    <small class="text-muted">Never logged in</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 align-items-center actions-container">
                                                    <!-- View User Button -->
                                                    <button type="button" class="btn btn-outline-info btn-icon" 
                                                            onclick="showUserProfile({{ $user->id }})"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top" 
                                                            title="View User">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Edit User Button -->
                                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning btn-icon" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <!-- Delete User Button (not for current user) -->
                                                    @if($user->id !== auth()->id())
                                                        <button type="button" class="btn btn-outline-danger btn-icon" 
                                                                onclick="confirmDelete('{{ $user->name }}', '{{ route('users.destroy', $user) }}')" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="top" 
                                                                title="Delete User">
                                                            <i class="fas fa-trash"></i>
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
                        <div class="d-flex justify-content-center mt-3">
                            {{ $users->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No users found</h5>
                            <p class="text-muted">Start by adding your first user.</p>
                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First User
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Profile Modal -->
<div class="modal fade" id="userProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Account Details -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Basic Information</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>User ID:</strong></td>
                                                <td id="userId"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Full Name:</strong></td>
                                                <td id="fullName"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email Address:</strong></td>
                                                <td id="emailAddress"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Role:</strong></td>
                                                <td id="userRole"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email Status:</strong></td>
                                                <td id="emailStatus"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Account Statistics</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Account Created:</strong></td>
                                                <td id="accountCreated"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Updated:</strong></td>
                                                <td id="lastUpdated"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Login:</strong></td>
                                                <td id="lastLoginStats"></td>
                                            </tr>
                                        </table>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(userName, deleteUrl) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = deleteUrl;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function showUserProfile(userId) {
    // Show loading in modal while fetching data
    const modal = new bootstrap.Modal(document.getElementById('userProfileModal'));
    modal.show();
    
    // Fetch user data
    fetch(`/api/users/${userId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.json();
        })
        .then(user => {
            // Basic information table
            document.getElementById('userId').textContent = '#' + user.id;
            document.getElementById('fullName').textContent = user.name;
            document.getElementById('emailAddress').textContent = user.email;
            
            // User role
            const userRole = document.getElementById('userRole');
            if (user.role === 'admin') {
                userRole.innerHTML = '<span class="badge bg-danger"><i class="fas fa-crown me-1"></i>Admin</span>';
            } else {
                userRole.innerHTML = '<span class="badge bg-primary"><i class="fas fa-user me-1"></i>User</span>';
            }
            
            // Email status
            const emailStatus = document.getElementById('emailStatus');
            if (user.email_verified_at) {
                const verifiedDate = new Date(user.email_verified_at);
                const verifiedDateStr = verifiedDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric'
                });
                emailStatus.innerHTML = `<span class="badge bg-success">Verified</span><br><small class="text-muted">${verifiedDateStr}</small>`;
            } else {
                emailStatus.innerHTML = '<span class="badge bg-warning">Not Verified</span>';
            }
            
            // Account statistics
            const accountCreated = new Date(user.created_at);
            const accountCreatedStr = accountCreated.toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            });
            const accountCreatedTimeAgo = getTimeAgo(accountCreated);
            document.getElementById('accountCreated').innerHTML = `${accountCreatedStr}<br><small class="text-muted">${accountCreatedTimeAgo}</small>`;
            
            const lastUpdated = new Date(user.updated_at);
            const lastUpdatedStr = lastUpdated.toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            });
            const lastUpdatedTimeAgo = getTimeAgo(lastUpdated);
            document.getElementById('lastUpdated').innerHTML = `${lastUpdatedStr}<br><small class="text-muted">${lastUpdatedTimeAgo}</small>`;
            
            // Last login stats
            const lastLoginStats = document.getElementById('lastLoginStats');
            if (user.last_login_at) {
                const loginDate = new Date(user.last_login_at);
                const formattedDate = loginDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: 'numeric', minute: '2-digit', hour12: true
                });
                const timeAgo = getTimeAgo(loginDate);
                lastLoginStats.innerHTML = `${formattedDate}<br><small class="text-muted">${timeAgo}</small>`;
            } else {
                lastLoginStats.innerHTML = '<span class="badge bg-secondary">Never logged in</span>';
            }
            
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
            console.error('Full error details:', error.message);
            alert(`Error loading user data: ${error.message}. Please try again.`);
            modal.hide();
        });
}

function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`;
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    if (diffInSeconds < 31536000) return `${Math.floor(diffInSeconds / 2592000)} months ago`;
    return `${Math.floor(diffInSeconds / 31536000)} years ago`;
}

// Initialize tooltips and DataTable
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#usersTable').DataTable({
            "pageLength": 10,
            "order": [[ 0, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [5] } // Disable sorting on Actions column
            ]
        });
    }
});
</script>
@endpush
@endsection