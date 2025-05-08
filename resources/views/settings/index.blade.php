@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">System Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control" id="app_name" name="app_name" value="{{ $appName }}" required>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="font-weight-bold">Email Settings</h6>
                        <div class="mb-3">
                            <label for="mail_from_name" class="form-label">From Name</label>
                            <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{ $emailSettings['from_name'] }}" required>
                            <small class="form-text text-muted">The name that will appear in the From field of emails.</small>
                        </div>
                        <div class="mb-3">
                            <label for="mail_from_address" class="form-label">From Address</label>
                            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="{{ $emailSettings['from_address'] }}" required>
                            <small class="form-text text-muted">The email address that will be used to send emails.</small>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notification Settings</h6>
                </div>
                <div class="card-body">
                    <p>Configure which alerts will trigger notifications and who will receive them.</p>
                    
                    <a href="{{ route('settings.notifications') }}" class="btn btn-primary">
                        <i class="fas fa-bell me-1"></i> Configure Notifications
                    </a>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Environment</h6>
                        <p class="text-muted mb-1">
                            <span class="fw-semibold">Mode:</span> {{ ucfirst($appEnv) }}
                            <span class="ms-3 fw-semibold">Debug:</span> {{ $appDebug ? 'Enabled' : 'Disabled' }}
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Storage</h6>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-{{ $diskUsage['percent'] > 90 ? 'danger' : ($diskUsage['percent'] > 70 ? 'warning' : 'success') }}" 
                                role="progressbar" style="width: {{ $diskUsage['percent'] }}%;" 
                                aria-valuenow="{{ $diskUsage['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $diskUsage['percent'] }}%
                            </div>
                        </div>
                        <p class="text-muted mb-1">
                            <span class="fw-semibold">Used:</span> {{ $diskUsage['used'] }} / {{ $diskUsage['total'] }}
                            <span class="ms-3 fw-semibold">Free:</span> {{ $diskUsage['free'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
