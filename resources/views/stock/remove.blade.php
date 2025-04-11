@extends('layouts.app')

@section('title', 'Remove Stock')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Remove Stock</h1>
    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 me-2"></i> Back to Product
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <!-- Product Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 text-md-end">
                        <strong>Product Name:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $product->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-md-end">
                        <strong>Code:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $product->code }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-md-end">
                        <strong>Category:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $product->category->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-md-end">
                        <strong>Current Stock:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge {{ $product->current_stock > $product->min_stock ? 'bg-primary' : 'bg-danger' }} px-3 py-2">
                            {{ $product->current_stock }}
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 text-md-end">
                        <strong>Min Stock:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $product->min_stock }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Remove Stock Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Remove Stock</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('stock.destroy', $product->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity to Remove <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                            id="quantity" name="quantity" min="1" max="{{ $product->current_stock }}" 
                            value="{{ old('quantity', 1) }}" required>
                        <div class="form-text">Maximum available: {{ $product->current_stock }}</div>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Expired">Expired</option>
                            <option value="Lost">Lost</option>
                            <option value="Returned to Supplier">Returned to Supplier</option>
                            <option value="Adjustment">Adjustment</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-danger">Remove Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 