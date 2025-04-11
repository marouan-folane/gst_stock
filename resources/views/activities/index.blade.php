@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Activity Log</h1>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Activities</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('activities.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="type" class="form-label">Activity Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="user_id" class="form-label">User</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Activities Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Activities</h6>
    </div>
    <div class="card-body">
        <div class="timeline">
            @forelse($activities as $activity)
                <div class="timeline-item">
                    <div class="timeline-item-marker">
                        @if($activity->type == 'sale')
                            <div class="timeline-item-marker-indicator bg-success"><i class="fas fa-shopping-cart"></i></div>
                        @elseif($activity->type == 'purchase')
                            <div class="timeline-item-marker-indicator bg-primary"><i class="fas fa-dolly-flatbed"></i></div>
                        @elseif($activity->type == 'stock')
                            <div class="timeline-item-marker-indicator bg-info"><i class="fas fa-boxes"></i></div>
                        @elseif($activity->type == 'payment')
                            <div class="timeline-item-marker-indicator bg-warning"><i class="fas fa-dollar-sign"></i></div>
                        @elseif($activity->type == 'user')
                            <div class="timeline-item-marker-indicator bg-danger"><i class="fas fa-user"></i></div>
                        @elseif($activity->type == 'product')
                            <div class="timeline-item-marker-indicator bg-dark"><i class="fas fa-box"></i></div>
                        @else
                            <div class="timeline-item-marker-indicator bg-secondary"><i class="fas fa-check"></i></div>
                        @endif
                    </div>
                    <div class="timeline-item-content pt-0">
                        <div class="card shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="small text-muted">
                                        <i class="far fa-clock me-1"></i> {{ $activity->created_at->format('M d, Y g:i A') }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="far fa-user me-1"></i> {{ $activity->user ? $activity->user->name : 'System' }}
                                    </div>
                                </div>
                                <p class="mb-0">{{ $activity->description }}</p>
                                @if($activity->properties && count($activity->properties) > 0)
                                    <div class="small mt-2">
                                        @foreach($activity->properties as $key => $value)
                                            <span class="badge bg-light text-dark">{{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($activity->link)
                                    <div class="small mt-2">
                                        <a href="{{ $activity->link }}">View Details</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No activities found based on your filters.</p>
                </div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $activities->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    
    .timeline-item {
        position: relative;
        display: flex;
        margin-bottom: 1rem;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-item-marker {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    
    .timeline-item-marker-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 100%;
        color: #fff;
        font-size: 1rem;
    }
    
    .timeline-item-content {
        width: 100%;
    }
</style>
@endsection 