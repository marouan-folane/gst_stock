@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Supplier Details: {{ $supplier->name }}</h1>
    <div>
        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-edit fa-sm me-2"></i> Edit Supplier
        </a>
       
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Suppliers
        </a>
    </div>
</div>

<div class="row">
    <!-- Supplier Details -->
    <div class="col-xl-12 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar mx-auto bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <span class="font-weight-bold h1">{{ substr($supplier->name, 0, 1) }}</span>
                    </div>
                    <h5 class="mt-3 font-weight-bold">{{ $supplier->name }}</h5>
                    <div class="mb-0 text-muted">
                        @if($supplier->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-envelope text-primary me-3"></i>
                        <span>{{ $supplier->email ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone text-primary me-3"></i>
                        <span>{{ $supplier->phone }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user text-primary me-3"></i>
                        <span>{{ $supplier->contact_person ?? 'N/A' }}</span>
                    </div>
                    @if($supplier->tax_number)
                    <div class="d-flex align-items-center">
                        <i class="fas fa-receipt text-primary me-3"></i>
                        <span>{{ $supplier->tax_number }}</span>
                    </div>
                    @endif
                </div>
                
                <hr>
                
                <h6 class="font-weight-bold">Address</h6>
                @if($supplier->address || $supplier->city || $supplier->state || $supplier->postal_code || $supplier->country)
                    <p class="mb-0">{{ $supplier->address }}</p>
                    <p class="mb-0">
                        @if($supplier->city){{ $supplier->city }}@endif
                        @if($supplier->state && $supplier->city), @endif
                        @if($supplier->state){{ $supplier->state }}@endif
                        @if($supplier->postal_code) {{ $supplier->postal_code }}@endif
                    </p>
                    <p>{{ $supplier->country }}</p>
                @else
                    <p>No address information available</p>
                @endif
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created On:</span>
                    <span>{{ $supplier->created_at->format('M d, Y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Last Updated:</span>
                    <span>{{ $supplier->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
        
        @if($supplier->notes)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
            </div>
            <div class="card-body">
                <p>{{ $supplier->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Supplier Products -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Products Supplied</h6>
                <a href="{{ route('products.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus fa-sm me-1"></i> Add New Product
                </a>
            </div>
            <div class="card-body">
                @if($supplier->products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplier->products as $product)
                                <tr>
                                    <td class="text-center" style="width: 80px;">
                                        @if($product->image && file_exists(public_path('storage/' . $product->image)))
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 50px; max-width: 50px; object-fit: cover;">
                                        @else
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        @endif
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($product->selling_price, 2) }}</td>
                                    <td>
                                        @if($product->current_stock <= $product->min_stock)
                                            <span class="badge bg-danger">Low: {{ $product->current_stock }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $product->current_stock }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('products.stock.create', $product->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Stock
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-box fa-4x text-gray-300 mb-3"></i>
                        <p class="text-muted">No products found for this supplier</p>
                        <a href="{{ route('products.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus fa-sm me-1"></i> Add Product
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  
            
            <div class="modal-footer">
                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 