@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Product Details: {{ $product->name }}</h1>
    <div>
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-edit fa-sm me-2"></i> Edit Product
        </a>
        <a href="{{ route('products.stock.create', $product->id) }}" class="btn btn-success btn-rounded">
            <i class="fas fa-plus fa-sm me-2"></i> Add Stock
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <!-- Product Basic Info -->
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 200px;">
                    @else
                        <div class="bg-light rounded p-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-box fa-4x text-gray-300"></i>
                        </div>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Product ID:</span>
                    <span class="badge bg-primary">{{ $product->id }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Product Code:</span>
                    <span class="fw-semibold">{{ $product->code }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Category:</span>
                    <span>{{ $product->category->name ?? 'N/A' }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Status:</span>
                    @if($product->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Created At:</span>
                    <span>{{ $product->created_at->format('M d, Y') }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-xs font-weight-bold text-uppercase">Last Updated:</span>
                    <span>{{ $product->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-xl-8 col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detailed Information</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="30%" class="text-gray-700">Description</th>
                                <td>{{ $product->description ?? 'No description available' }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Supplier</th>
                                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Cost Price</th>
                                <td>${{ number_format($product->cost_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Selling Price</th>
                                <td>${{ number_format($product->selling_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Profit Margin</th>
                                <td>
                                    @php
                                        $profit = $product->selling_price - $product->cost_price;
                                        $marginPercent = $product->cost_price > 0 ? ($profit / $product->cost_price) * 100 : 0;
                                    @endphp
                                    ${{ number_format($profit, 2) }} ({{ number_format($marginPercent, 2) }}%)
                                </td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Unit</th>
                                <td>{{ ucfirst($product->unit) }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Expiry Date</th>
                                <td>{{ $product->expiry_date ? $product->expiry_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Stock Status -->
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Stock Information</h6>
                <a href="{{ route('products.stock.history', $product->id) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-history fa-sm me-1"></i> Stock History
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Current Stock</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $product->current_stock }} {{ ucfirst($product->unit) }}s</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Minimum Stock</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $product->min_stock }} {{ ucfirst($product->unit) }}s</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Maximum Stock</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $product->max_stock ?? 'Not set' }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-2">
                    <h6 class="font-weight-bold">Stock Status:</h6>
                    <div class="progress" style="height: 25px;">
                        @php
                            $stockPercentage = $product->max_stock > 0 ? min(100, ($product->current_stock / $product->max_stock) * 100) : 
                                              ($product->min_stock > 0 ? ($product->current_stock / $product->min_stock) * 50 : 50);
                            
                            if ($product->current_stock <= $product->min_stock) {
                                $barClass = 'bg-danger';
                                $stockStatus = 'Low Stock';
                            } elseif ($product->max_stock && $product->current_stock >= $product->max_stock) {
                                $barClass = 'bg-warning';
                                $stockStatus = 'Overstocked';
                            } else {
                                $barClass = 'bg-success';
                                $stockStatus = 'In Stock';
                            }
                        @endphp
                        <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $stockPercentage }}%;" 
                             aria-valuenow="{{ $stockPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $stockStatus }}: {{ $product->current_stock }} {{ ucfirst($product->unit) }}s
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-around">
                        <a href="{{ route('products.stock.create', $product->id) }}?type=add" class="btn btn-success">
                            <i class="fas fa-plus-circle me-1"></i> Add Stock
                        </a>
                        <a href="{{ route('products.stock.create', $product->id) }}?type=remove" class="btn btn-danger">
                            <i class="fas fa-minus-circle me-1"></i> Remove Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Stock Movements and Sales -->
<div class="row">
    <!-- Recent Stock Movements -->
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Stock Movements</h6>
            </div>
            <div class="card-body">
                @if($stockMovements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockMovements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($movement->type == 'add')
                                                <span class="badge bg-success">Addition</span>
                                            @elseif($movement->type == 'remove')
                                                <span class="badge bg-danger">Removal</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($movement->type) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ abs($movement->quantity) }}</td>
                                        <td>{{ $movement->reference ?? 'Manual Adjustment' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No stock movements recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Sales -->
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Sales</h6>
            </div>
            <div class="card-body">
                @if($sales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Quantity</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('sales.show', $sale->sale_id) }}">
                                                #{{ $sale->sale->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->quantity }}</td>
                                        <td>${{ number_format($sale->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No sales recorded for this product yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($product->description)
<!-- Product Description -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Product Description</h6>
            </div>
            <div class="card-body">
                <div class="p-3">
                    {{ $product->description }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 