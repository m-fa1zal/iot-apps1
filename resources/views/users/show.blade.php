@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user me-2"></i>User Details: {{ $user->name }}</h1>
                <div>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <div class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 80px; height: 80px; font-size: 24px; font-weight: bold;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h5 class="mb-0">{{ $user->name }}</h5>
                    @if($user->id === auth()->id())
                        <small class="badge bg-light text-primary">Current User</small>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong><i class="fas fa-envelope me-2 text-muted"></i>Email:</strong><br>
                        <span class="text-muted">{{ $user->email }}</span>
                        @if($user->email_verified_at)
                            <i class="fas fa-check-circle text-success ms-1" title="Verified"></i>
                        @else
                            <i class="fas fa-times-circle text-danger ms-1" title="Not Verified"></i>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong><i class="fab fa-telegram me-2 text-muted"></i>Telegram Chat ID:</strong><br>
                        @if($user->telegram_chat_id)
                            <span class="badge bg-success">{{ $user->telegram_chat_id }}</span>
                        @else
                            <span class="badge bg-secondary">Not Configured</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-calendar me-2 text-muted"></i>Member Since:</strong><br>
                        <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                        <small class="d-block text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-clock me-2 text-muted"></i>Last Activity:</strong><br>
                        @if($user->updated_at->ne($user->created_at))
                            <span class="text-muted">{{ $user->updated_at->format('M d, Y') }}</span>
                            <small class="d-block text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                        @else
                            <span class="text-muted">No recent activity</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Basic Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>User ID:</strong></td>
                                    <td>#{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email Address:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email Status:</strong></td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Verified</span>
                                            <small class="d-block text-muted">{{ $user->email_verified_at->format('M d, Y') }}</small>
                                        @else
                                            <span class="badge bg-warning">Not Verified</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Account Statistics</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Account Created:</strong></td>
                                    <td>
                                        {{ $user->created_at->format('M d, Y g:i A') }}
                                        <small class="d-block text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>
                                        {{ $user->updated_at->format('M d, Y g:i A') }}
                                        <small class="d-block text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Devices:</strong></td>
                                    <td><span class="badge bg-primary">0</span></td>
                                </tr>
                                <tr>
                                    <td><strong>API Tokens:</strong></td>
                                    <td><span class="badge bg-secondary">0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telegram Configuration -->
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fab fa-telegram me-2"></i>Telegram Configuration</h6>
                </div>
                <div class="card-body">
                    @if($user->telegram_chat_id)
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Telegram notifications are enabled!</strong><br>
                            Chat ID: <code>{{ $user->telegram_chat_id }}</code>
                        </div>
                        <p class="text-muted">This user will receive Telegram notifications for their IoT device activities.</p>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Telegram notifications are not configured.</strong><br>
                            The user won't receive any Telegram notifications for their devices.
                        </div>
                        <p class="text-muted">To enable Telegram notifications, edit the user profile and add their Telegram chat ID.</p>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Configure Telegram
                        </a>
                    @endif
                </div>
            </div>

            <!-- Activity Timeline (Placeholder) -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No recent activity to display.</p>
                        <small class="text-muted">Device activities and system events will appear here.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-edit me-1"></i>Edit User
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" disabled>
                                <i class="fas fa-devices me-1"></i>Manage Devices
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success w-100" disabled>
                                <i class="fas fa-chart-line me-1"></i>View Analytics
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            @if($user->id !== auth()->id())
                                <button type="button" class="btn btn-outline-danger w-100" 
                                        onclick="confirmDelete('{{ $user->name }}', '{{ route('users.destroy', $user) }}')">
                                    <i class="fas fa-trash me-1"></i>Delete User
                                </button>
                            @else
                                <button class="btn btn-outline-secondary w-100" disabled title="Cannot delete your own account">
                                    <i class="fas fa-ban me-1"></i>Delete User
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
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
                <p class="text-muted">This action cannot be undone and will also delete:</p>
                <ul class="text-muted">
                    <li>All associated IoT devices</li>
                    <li>All sensor data history</li>
                    <li>All API tokens</li>
                </ul>
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

@push('scripts')
<script>
function confirmDelete(userName, deleteUrl) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = deleteUrl;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush
@endsection