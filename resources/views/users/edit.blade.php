@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-edit me-2"></i>Edit User: {{ $user->name }}</h1>
                <div>
                    <a href="{{ route('users.show', $user) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i>View
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Update User Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', $user->name) }}" required autofocus>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email', $user->email) }}" required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telegram_chat_id" class="form-label">{{ __('Telegram Chat ID') }} <small class="text-muted">(Optional)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-telegram"></i></span>
                                        <input id="telegram_chat_id" type="text" class="form-control @error('telegram_chat_id') is-invalid @enderror" 
                                               name="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}">
                                    </div>
                                    <small class="form-text text-muted">
                                        Enter the Telegram chat ID to enable notifications.
                                    </small>
                                    @error('telegram_chat_id')
                                        <div class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">{{ __('User Role') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                        <select id="role" class="form-select @error('role') is-invalid @enderror" name="role" required>
                                            <option value="">Choose role...</option>
                                            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </div>
                                    <small class="form-text text-muted">
                                        Admin: Full access | User: Dashboard & Devices only
                                    </small>
                                    @error('role')
                                        <div class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-muted mb-3"><i class="fas fa-key me-1"></i>Change Password <small class="text-muted">(Leave blank to keep current password)</small></h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('New Password') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                               name="password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-toggle"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Minimum 8 characters (leave blank to keep current password).</small>
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password-confirm" class="form-label">{{ __('Confirm New Password') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input id="password-confirm" type="password" class="form-control" 
                                               name="password_confirmation">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password-confirm')">
                                            <i class="fas fa-eye" id="password-confirm-toggle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y at g:i A') }} 
                            ({{ $user->updated_at->diffForHumans() }})
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save me-1"></i>Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>User ID:</strong> #{{ $user->id }}<br>
                            <strong>Created:</strong> {{ $user->created_at->format('M d, Y at g:i A') }}<br>
                            <strong>Email Verified:</strong> 
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Yes</span> ({{ $user->email_verified_at->format('M d, Y') }})
                            @else
                                <span class="badge bg-warning">No</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y at g:i A') }}<br>
                            <strong>Total Devices:</strong> <span class="badge bg-primary">0</span><br>
                            <strong>API Tokens:</strong> <span class="badge bg-secondary">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}
</script>
@endpush
@endsection