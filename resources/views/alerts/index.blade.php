@extends('layouts.app')

@section('title', 'Alerts & Notifications')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Alerts & Notifications</h1>
    <div>
        <div class="btn-group" role="group">
            <a href="{{ route('alerts.check.expiring') }}" class="btn btn-warning btn-rounded">
                <i class="fas fa-calendar-times fa-sm me-2"></i> Check Expiring Products
            </a>
            <a href="{{ route('alerts.check.low-stock') }}" class="btn btn-danger btn-rounded">
                <i class="fas fa-exclamation-triangle fa-sm me-2"></i> Check Stock Levels
            </a>
            <a href="{{ route('sensible-categories.index') }}" class="btn btn-info btn-rounded">
                <i class="fas fa-bell fa-sm me-2"></i> Category Monitoring
            </a>
            <a href="{{ route('settings.notifications') }}" class="btn btn-primary btn-rounded">
                <i class="fas fa-envelope fa-sm me-2"></i> Email Settings
            </a>
        </div>
        <form action="{{ route('alerts.mark-all-as-read') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-secondary btn-rounded">
                <i class="fas fa-check-double fa-sm me-2"></i> Mark All as Read
            </button>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Filter Alerts</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('alerts.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="type" class="form-label">Alert Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Information Alerts</option>
                                    <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Warning Alerts</option>
                                    <option value="danger" {{ request('type') == 'danger' ? 'selected' : '' }}>Danger Alerts</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="is_read" class="form-label">Status</label>
                                <select class="form-select" id="is_read" name="is_read">
                                    <option value="">All Statuses</option>
                                    <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Unread Only</option>
                                    <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Read Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="mb-3 w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Alerts</h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($alerts->count() > 0)
            <div class="list-group">
                @foreach($alerts as $alert)
                    <div class="list-group-item list-group-item-action {{ $alert->is_read ? 'bg-light' : 'border-left-primary' }} d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    @if(!$alert->is_read)
                                        <span class="badge bg-primary me-2">New</span>
                                    @endif
                                    {{ $alert->title }}
                                </h5>
                                <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $alert->message }}</p>
                            
                            @if($alert->product)
                            <div class="mt-2">
                                <small class="text-muted">Product: </small>
                                <a href="{{ route('products.show', $alert->product_id) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $alert->product->name }}
                                </a>
                                
                                @if($alert->type == 'expiry' && $alert->product->expiry_date)
                                    <small class="text-muted ms-2">Expires: {{ $alert->product->expiry_date->format('M d, Y') }}</small>
                                @endif
                                
                                @if($alert->type == 'low_stock' || $alert->type == 'out_of_stock')
                                    <small class="text-muted ms-2">Current Stock: {{ $alert->product->current_stock }}</small>
                                    <small class="text-muted ms-2">Min Stock: {{ $alert->product->min_stock }}</small>
                                @endif
                            </div>
                            @endif
                        </div>
                        
                        <div class="ms-2 d-flex align-items-center">
                            @if(!$alert->is_read)
                                <form action="{{ route('alerts.mark-as-read', $alert->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary">Read</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-3">
                {{ $alerts->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-gray-300 mb-3"></i>
                <p class="text-gray-500 mb-0">No alerts found</p>
                <p class="text-muted">
                    @if(request()->has('type') || request()->has('is_read'))
                        Try changing your filter settings.
                    @else
                        All alerts will appear here when they are generated.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection 