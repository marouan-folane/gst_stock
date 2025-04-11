@extends('layouts.app')

@section('title', 'Adjust Stock')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        {{ request('type') == 'remove' ? 'Remove Stock' : 'Add Stock' }}: {{ $product->name }}
    </h1>
    <div>
        <a href="{{ route('products.show', $product->id) }}" class="btn btn-info btn-rounded">
            <i class="fas fa-eye fa-sm me-2"></i> View Product
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <!-- Product Info -->
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 150px;">
                    @else
                        <div class="bg-light rounded p-3 d-flex align-items-center justify-content-center" style="height: 150px;">
                            <i class="fas fa-box fa-3x text-gray-300"></i>
                        </div>
                    @endif
                </div>
                
                <div class="text-center mb-3">
                    <h5 class="font-weight-bold">{{ $product->name }}</h5>
                    <p class="text-muted">{{ $product->code }}</p>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Category:</span>
                    <span>{{ $product->category->name ?? 'N/A' }}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Unit:</span>
                    <span>{{ ucfirst($product->unit) }}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Current Stock:</span>
                    <span class="font-weight-bold {{ $product->current_stock <= $product->min_stock ? 'text-danger' : 'text-success' }}">
                        {{ $product->current_stock }} {{ ucfirst($product->unit) }}s
                    </span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Min. Stock:</span>
                    <span>{{ $product->min_stock }} {{ ucfirst($product->unit) }}s</span>
                </div>
                
                @if($product->max_stock)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Max. Stock:</span>
                    <span>{{ $product->max_stock }} {{ ucfirst($product->unit) }}s</span>
                </div>
                @endif
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Selling Price:</span>
                    <span>${{ number_format($product->selling_price, 2) }}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Status:</span>
                    @if($product->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stock Adjustment Form -->
    <div class="col-md-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    {{ request('type') == 'remove' ? 'Remove Stock' : 'Add Stock' }}
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('products.stock.store', $product->id) }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="type" value="{{ request('type', 'add') }}">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                                <small class="text-muted">
                                    Enter the quantity to {{ request('type') == 'remove' ? 'remove from' : 'add to' }} stock
                                </small>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date" class="form-label">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                       id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    {{ request('type') == 'remove' ? 'Optional: Select if returning to supplier' : 'Optional: Select the supplier for this stock' }}
                                </small>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_type" class="form-label">Reference Type</label>
                                <select class="form-select @error('reference_type') is-invalid @enderror" id="reference_type" name="reference_type">
                                    <option value="">Select Reference Type</option>
                                    @if(request('type') == 'add')
                                        <option value="purchase" {{ old('reference_type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                                        <option value="return_in" {{ old('reference_type') == 'return_in' ? 'selected' : '' }}>Customer Return</option>
                                        <option value="adjustment" {{ old('reference_type') == 'adjustment' ? 'selected' : '' }}>Inventory Adjustment</option>
                                        <option value="initial" {{ old('reference_type') == 'initial' ? 'selected' : '' }}>Initial Stock</option>
                                    @else
                                        <option value="sale" {{ old('reference_type') == 'sale' ? 'selected' : '' }}>Sale</option>
                                        <option value="return_out" {{ old('reference_type') == 'return_out' ? 'selected' : '' }}>Return to Supplier</option>
                                        <option value="damage" {{ old('reference_type') == 'damage' ? 'selected' : '' }}>Damaged/Expired</option>
                                        <option value="adjustment" {{ old('reference_type') == 'adjustment' ? 'selected' : '' }}>Inventory Adjustment</option>
                                    @endif
                                </select>
                                @error('reference_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference" class="form-label">Reference Number</label>
                                <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                       id="reference" name="reference" value="{{ old('reference') }}">
                                <small class="text-muted">
                                    Optional: Invoice, PO, or any reference number
                                </small>
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                <small class="text-muted">
                                    Optional: Any additional information about this stock adjustment
                                </small>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">Summary</h6>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1">Current Stock:</p>
                                            <p class="mb-1">{{ request('type') == 'remove' ? 'Removing' : 'Adding' }}:</p>
                                            <p class="font-weight-bold">New Stock:</p>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <p class="mb-1">{{ $product->current_stock }} {{ ucfirst($product->unit) }}s</p>
                                            <p class="mb-1" id="adjustmentText">1 {{ ucfirst($product->unit) }}s</p>
                                            <p class="font-weight-bold" id="newStockText">
                                                @if(request('type') == 'remove')
                                                    {{ max(0, $product->current_stock - 1) }}
                                                @else
                                                    {{ $product->current_stock + 1 }}
                                                @endif
                                                {{ ucfirst($product->unit) }}s
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn {{ request('type') == 'remove' ? 'btn-danger' : 'btn-success' }}">
                            <i class="fas {{ request('type') == 'remove' ? 'fa-minus' : 'fa-plus' }} me-1"></i>
                            {{ request('type') == 'remove' ? 'Remove from Stock' : 'Add to Stock' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const adjustmentText = document.getElementById('adjustmentText');
        const newStockText = document.getElementById('newStockText');
        const currentStock = {{ $product->current_stock }};
        const unitName = '{{ ucfirst($product->unit) }}s';
        const isRemove = {{ request('type') == 'remove' ? 'true' : 'false' }};
        
        quantityInput.addEventListener('input', function() {
            const quantity = parseInt(this.value) || 0;
            adjustmentText.textContent = `${quantity} ${unitName}`;
            
            if (isRemove) {
                const newStock = Math.max(0, currentStock - quantity);
                newStockText.textContent = `${newStock} ${unitName}`;
            } else {
                const newStock = currentStock + quantity;
                newStockText.textContent = `${newStock} ${unitName}`;
            }
        });
    });
</script>
@endsection 