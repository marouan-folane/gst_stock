@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="{{ route('reports.index') }}" class="btn btn-primary btn-rounded">
        <i class="fas fa-download fa-sm me-2"></i> Generate Report
    </a>
</div>

<!-- Stats Cards Row -->
<div class="row mb-4">
    <!-- Total Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Products
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300 stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Low Stock Products
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300 stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Suppliers Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Suppliers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSuppliers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300 stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Customers Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Customers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCustomers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300 stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Stock Movements -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Stock Movements</h6>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Date</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStockMovements as $movement)
                            <tr>
                                <td>{{ $movement->product->name }}</td>
                                <td>
                                    @if($movement->type == 'in')
                                        <span class="badge bg-success">In</span>
                                    @else
                                        <span class="badge bg-danger">Out</span>
                                    @endif
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $movement->user->name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No recent stock movements</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts & Notifications -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Alerts & Notifications</h6>
                <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recentAlerts as $alert)
                <div class="alert {{ $alert->type == 'danger' ? 'alert-danger' : ($alert->type == 'warning' ? 'alert-warning' : 'alert-info') }} mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            @if($alert->type == 'danger')
                                <i class="fas fa-exclamation-circle fa-2x"></i>
                            @elseif($alert->type == 'warning')
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            @else
                                <i class="fas fa-info-circle fa-2x"></i>
                            @endif
                        </div>
                        <div>
                            <h6 class="alert-heading mb-1">{{ $alert->title }}</h6>
                            <p class="mb-0">{{ $alert->message }}</p>
                            <small>{{ $alert->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="mb-0">No alerts at this time!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Sales Statistics Row -->
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Sales Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Today's Sales
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($todaySales, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Monthly Sales
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($monthlySales, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Orders
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingOrders }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Orders
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Sold Qty</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>${{ number_format($product->selling_price, 2) }}</td>
                                <td>{{ $product->total_sold ?? 0 }}</td>
                                <td>${{ number_format($product->total_revenue ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No sales data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Stock Overview</h6>
                <div>
                    <a href="{{ route('products.create') }}" class="btn btn-sm btn-success me-2">
                        <i class="fas fa-plus fa-sm"></i> Add Product
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-list fa-sm"></i> All Products
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Min. Stock</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td>{{ $product->current_stock }}</td>
                                <td>{{ $product->min_stock }}</td>
                                <td>
                                    @if($product->current_stock <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($product->current_stock < $product->min_stock)
                                        <span class="badge bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td>{{ $product->updated_at->format('M d, Y') }}</td>
                                <td class="action-buttons">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('products.stock.create', $product) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Add Stock">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No products found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Handle sidebar toggle buttons
        $("#sidebarToggle, #mobile-sidebar-toggle").on("click", function(e) {
            e.preventDefault();
            $("body").toggleClass("sidebar-toggled");
            $("#sidebar-wrapper").toggleClass("toggled");
            $(".sidebar").toggleClass("toggled");
        });
        
        // Close sidebar on small screens when clicking outside
        $(document).on('click touchstart', function(e) {
            if ($(window).width() < 768 && 
                !$(e.target).closest('#sidebar-wrapper, #sidebarToggle, #mobile-sidebar-toggle').length && 
                $('#sidebar-wrapper').hasClass('toggled')) {
                $("body").removeClass("sidebar-toggled");
                $("#sidebar-wrapper").removeClass("toggled");
                $(".sidebar").removeClass("toggled");
            }
        });
        
        // Make sure sidebar is visible on larger screens
        $(window).resize(function() {
            if ($(window).width() >= 768) {
                $(".sidebar").removeClass("toggled");
                $("#sidebar-wrapper").removeClass("toggled");
            }
        });
    });
</script>
@endpush 