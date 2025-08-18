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
                <div class="card-header bg-primary text-white">
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
                            <div class="col-md-12">
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


                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y at g:i A') }} 
                            ({{ $user->updated_at->diffForHumans() }})
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
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
                            <h6 class="text-muted mb-3">Basic Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>User ID:</strong></td>
                                    <td>#{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email Status:</strong></td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Verified</span><br>
                                            <small class="text-muted">{{ $user->email_verified_at->format('M d, Y') }}</small>
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
                                        {{ $user->created_at->format('M d, Y g:i A') }}<br>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>
                                        {{ $user->updated_at->format('M d, Y g:i A') }}<br>
                                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @if($user->last_login_at)
                                <tr>
                                    <td><strong>Last Login:</strong></td>
                                    <td>
                                        {{ $user->last_login_at->format('M d, Y g:i A') }}<br>
                                        <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection