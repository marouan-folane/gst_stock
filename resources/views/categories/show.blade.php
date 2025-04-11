@extends('layouts.app')

@section('title', 'Category Details')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Category: {{ $category->name }}</h1>
    <div>
        <a href="{{ route('products.create', ['category_id' => $category->id]) }}" class="btn btn-success btn-rounded">
            <i class="fas fa-plus fa-sm me-2"></i> Add Product
        </a>
        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-edit fa-sm me-2"></i> Edit Category
        </a>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Categories
        </a>
    </div>
</div>

<div class="row">
    <!-- Category Details -->
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-wrapper bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                        <i class="fas {{ $category->icon ?? 'fa-folder' }} fa-2x text-white"></i>
                    </div>
                    <h5 class="mt-3 font-weight-bold">{{ $category->name }}</h5>
                    
                    <div class="mb-2">
                        @if($category->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                
                @if($category->description)
                <div class="mb-4">
                    <h6 class="font-weight-bold">Description</h6>
                    <p>{{ $category->description }}</p>
                </div>
                @endif
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Products Count:</span>
                    <span class="badge bg-primary px-3 py-2">{{ $category->products->count() }}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created On:</span>
                    <span>{{ $category->created_at->format('M d, Y') }}</span>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Last Updated:</span>
                    <span>{{ $category->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="col-xl-8 col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Products in this Category</h6>
                <a href="{{ route('products.create', ['category_id' => $category->id]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus fa-sm"></i> Add Product
                </a>
            </div>
            <div class="card-body">
                @if($category->products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Image</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->products as $product)
                                <tr>
                                    <td class="text-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 40px;">
                                        @else
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        @endif
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->code }}</td>
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
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('products.stock.create', $product->id) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Add Stock">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-box-open fa-4x text-gray-300"></i>
                        </div>
                        <p class="mb-3">No products found in this category</p>
                        <a href="{{ route('products.create', ['category_id' => $category->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus fa-sm me-2"></i> Add New Product
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endsection 