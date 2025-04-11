@extends('layouts.app')

@section('title', 'Expiring Products')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Expiring Products</h1>
    <div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Products
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Filter by Expiry Timeline</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('products.expiring') }}" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="days" class="form-label">Show products expiring within</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="days" name="days" value="{{ $days }}" min="1" max="365">
                            <span class="input-group-text">days</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search by name or code</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search...">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Products Expiring Within {{ $days }} Days</h6>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Current Stock</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $daysRemaining = \Carbon\Carbon::now()->diffInDays($product->expiry_date, false);
                                $badgeClass = '';
                                
                                if($daysRemaining <= 7) {
                                    $badgeClass = 'bg-danger';
                                } elseif($daysRemaining <= 14) {
                                    $badgeClass = 'bg-warning';
                                } elseif($daysRemaining <= 30) {
                                    $badgeClass = 'bg-info';
                                } else {
                                    $badgeClass = 'bg-success';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <i class="fas fa-box text-secondary"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                                <td>
                                    @if($product->isLowStock())
                                        <span class="badge bg-warning">Low: {{ $product->current_stock }} {{ ucfirst($product->unit) }}s</span>
                                    @else
                                        <span class="badge bg-success">{{ $product->current_stock }} {{ ucfirst($product->unit) }}s</span>
                                    @endif
                                </td>
                                <td>{{ $product->expiry_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $daysRemaining }} days left</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-check fa-4x text-gray-300 mb-3"></i>
                <p class="text-gray-500 mb-0">No expiring products found within {{ $days }} days</p>
                <p class="text-muted">Try extending the time period or check back later.</p>
            </div>
        @endif
    </div>
</div>
@endsection 