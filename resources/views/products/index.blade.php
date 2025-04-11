@extends('layouts.app')

@section('title', isset($filtered) && $filtered ? "Products in {$filterName}" : 'Products')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        @if(isset($filtered) && $filtered)
            @if($filterType == 'category')
                Products in Category: {{ $filterName }}
            @endif
        @else
            Products
        @endif
    </h1>
    <div>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-plus fa-sm me-2"></i> Add New Product
        </a>
        @if(isset($filtered) && $filtered)
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-rounded">
                <i class="fas fa-times fa-sm me-2"></i> Clear Filter
            </a>
        @endif
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            @if(isset($filtered) && $filtered)
                Filtered Products
            @else
                All Products
            @endif
        </h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Export Options:</div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-file-csv fa-sm fa-fw me-2 text-gray-400"></i>
                    Export CSV
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-file-pdf fa-sm fa-fw me-2 text-gray-400"></i>
                    Export PDF
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-print fa-sm fa-fw me-2 text-gray-400"></i>
                    Print
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="productsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="text-center">
                            @if($product->image && file_exists(public_path('storage/' . $product->image)))
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 50px; max-width: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-box text-secondary"></i>
                                </div>
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td><span class="badge bg-secondary">{{ $product->code }}</span></td>
                        <td>{{ $product->category->name }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                {{ $product->current_stock }} {{ $product->unit }}
                                @if($product->current_stock <= 0)
                                    <span class="badge bg-danger ms-2">Out of Stock</span>
                                @elseif($product->current_stock < $product->min_stock)
                                    <span class="badge bg-warning ms-2">Low Stock</span>
                                @endif
                            </div>
                        </td>
                        <td>${{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="action-buttons">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('products.stock.create', $product->id) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Add Stock">
                                <i class="fas fa-plus"></i>
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush 