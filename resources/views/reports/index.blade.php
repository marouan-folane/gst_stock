@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reports</h1>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Sales Report Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Sales Report</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">View sales transactions</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <button type="button" class="btn btn-primary btn-block" data-bs-toggle="modal" data-bs-target="#salesReportModal">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Inventory Report Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Inventory Report</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">View current inventory status</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <button type="button" class="btn btn-success btn-block" data-bs-toggle="modal" data-bs-target="#inventoryReportModal">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Purchase Report Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Purchase Report</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">View purchase transactions</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <button type="button" class="btn btn-info btn-block" data-bs-toggle="modal" data-bs-target="#purchaseReportModal">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Profit & Loss Report Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Profit & Loss Report</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">View financial performance</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <button type="button" class="btn btn-warning btn-block" data-bs-toggle="modal" data-bs-target="#profitLossReportModal">
                    Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <!-- Expiry Report Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Expiry Report</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">View expiring products</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#expiryReportModal">
                    Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recently Generated Reports</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Report Type</th>
                                <th>Generated By</th>
                                <th>Parameters</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentReports) && count($recentReports) > 0)
                                @foreach($recentReports as $report)
                                <tr>
                                    <td>{{ $report->created_at->format('M d, Y g:i A') }}</td>
                                    <td>{{ $report->type }}</td>
                                    <td>{{ $report->user->name }}</td>
                                    <td>
                                        @foreach($report->parameters as $key => $value)
                                            <span class="badge bg-light text-dark">{{ $key }}: {{ $value }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ $report->url }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ $report->url }}?pdf=1" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No reports have been generated yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Report Modal -->
<div class="modal fade" id="salesReportModal" tabindex="-1" aria-labelledby="salesReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.generate') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="salesReportModalLabel">Generate Sales Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="sales">
                    
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" name="end_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" name="customer_id">
                            <option value="">All Customers</option>
                            @foreach(\App\Models\Customer::orderBy('name')->get() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Sale Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" name="payment_status">
                            <option value="">All Payment Statuses</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <button type="submit" class="btn btn-outline-primary" name="pdf" value="1">Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inventory Report Modal -->
<div class="modal fade" id="inventoryReportModal" tabindex="-1" aria-labelledby="inventoryReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.generate') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="inventoryReportModalLabel">Generate Inventory Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="inventory">
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Product Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock_filter" class="form-label">Stock Filter</label>
                        <select class="form-select" name="stock_filter">
                            <option value="">All Products</option>
                            <option value="in_stock">In Stock</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Generate Report</button>
                    <button type="submit" class="btn btn-outline-success" name="pdf" value="1">Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Purchase Report Modal -->
<div class="modal fade" id="purchaseReportModal" tabindex="-1" aria-labelledby="purchaseReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.generate') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseReportModalLabel">Generate Purchase Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="purchase">
                    
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" name="end_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id">
                            <option value="">All Suppliers</option>
                            @foreach(\App\Models\Supplier::orderBy('name')->get() as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Purchase Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" name="payment_status">
                            <option value="">All Payment Statuses</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Generate Report</button>
                    <button type="submit" class="btn btn-outline-info" name="pdf" value="1">Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profit & Loss Report Modal -->
<div class="modal fade" id="profitLossReportModal" tabindex="-1" aria-labelledby="profitLossReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.generate') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="profitLossReportModalLabel">Generate Profit & Loss Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="profit_loss">
                    
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" name="end_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Generate Report</button>
                    <button type="submit" class="btn btn-outline-warning" name="pdf" value="1">Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Product Report Modal -->
<div class="modal fade" id="productReportModal" tabindex="-1" aria-labelledby="productReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.generate') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="productReportModalLabel">Generate Product Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="product">
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Generate Report</button>
                    <button type="submit" class="btn btn-outline-warning" name="pdf" value="1">Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Expiry Report Modal -->
<div class="modal fade" id="expiryReportModal" tabindex="-1" aria-labelledby="expiryReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.generate') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title" id="expiryReportModalLabel">Generate Expiry Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="expiry">
                    
                    <div class="mb-3">
                        <label for="expiry_range" class="form-label">Expiry Range</label>
                        <select name="expiry_range" id="expiry_range" class="form-select">
                            <option value="expired">Expired Products</option>
                            <option value="7_days">Expiring in 7 Days</option>
                            <option value="30_days" selected>Expiring in 30 Days</option>
                            <option value="90_days">Expiring in 90 Days</option>
                            <option value="custom">Custom Days</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="custom_days_container" style="display: none;">
                        <label for="days" class="form-label">Number of Days</label>
                        <input type="number" class="form-control" name="days" id="days" value="30" min="1" max="365">
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id_expiry" class="form-label">Category</label>
                        <select name="category_id" id="category_id_expiry" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_id_expiry" class="form-label">Supplier</label>
                        <select name="supplier_id" id="supplier_id_expiry" class="form-select">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Generate Report</button>
                    <button type="submit" class="btn btn-outline-danger" name="pdf" value="1">Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Existing scripts...
        
        // For expiry report modal
        $('#expiry_range').change(function() {
            if ($(this).val() === 'custom') {
                $('#custom_days_container').show();
            } else {
                $('#custom_days_container').hide();
            }
        });
    });
</script>
@endpush 