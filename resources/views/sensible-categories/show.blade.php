@extends('layouts.app')

@section('title', 'Monitored Category Details')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Monitored Category Details</h1>
        <div>
            <a href="{{ route('sensible-categories.edit', $sensibleCategory->id) }}" class="btn btn-primary">
                <i class="fas fa-edit fa-sm me-2"></i> Edit
            </a>
            <a href="{{ route('sensible-categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left fa-sm me-2"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Category Monitoring Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Category Monitoring Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Category</h5>
                        <p class="mb-0">{{ $sensibleCategory->category->name ?? 'Not Available' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Status</h5>
                        <p class="mb-0">
                            @if($sensibleCategory->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Minimum Quantity</h5>
                        <p class="mb-0">{{ $sensibleCategory->min_quantity }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Current Stock Level</h5>
                        <p class="mb-0">
                            @if(isset($currentStock))
                                {{ $currentStock }}
                                @if($currentStock < $sensibleCategory->min_quantity)
                                    <span class="badge bg-danger">Below Threshold</span>
                                @else
                                    <span class="badge bg-success">Adequate</span>
                                @endif
                            @else
                                <span class="text-muted">Not Available</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Notification Email</h5>
                        <p class="mb-0">{{ $sensibleCategory->notification_email }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Notification Frequency</h5>
                        <p class="mb-0">{{ ucfirst($sensibleCategory->notification_frequency) }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Created At</h5>
                        <p class="mb-0">{{ $sensibleCategory->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Last Updated</h5>
                        <p class="mb-0">{{ $sensibleCategory->updated_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notifications -->
    @if(isset($notifications) && count($notifications) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Notifications</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Stock Level</th>
                            <th>Message</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                        <tr>
                            <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $notification->stock_level }}</td>
                            <td>{{ $notification->message }}</td>
                            <td>
                                @if($notification->is_sent)
                                    <span class="badge bg-success">Sent</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Category Monitoring -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5>Delete this monitored category</h5>
                    <p class="text-muted">Once deleted, you will no longer receive notifications for this category.</p>
                </div>
                <div class="col-md-4 text-end">
                    <form action="{{ route('sensible-categories.destroy', $sensibleCategory->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this monitored category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash fa-sm me-2"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 