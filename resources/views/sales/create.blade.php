@extends('layouts.app')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Create Sale</h1>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sales
                </a>
            </div>

            <form id="sale-form" action="{{ route('sales.store') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Sale Details Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Sale Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="invoice_number">Invoice Number</label>
                                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                        id="invoice_number" name="invoice_number" value="{{ $invoiceNumber }}" readonly>
                                    @error('invoice_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="datetime-local" class="form-control @error('date') is-invalid @enderror" 
                                        id="date" name="date" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control select2 @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                        id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Card -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Products</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="input-group">
                                        <select class="form-control select2" id="product-selector">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                    data-price="{{ $product->selling_price }}"
                                                    data-code="{{ $product->code }}"
                                                    data-stock="{{ $product->stock_quantity }}">
                                                    {{ $product->name }} ({{ $product->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="add-product">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="products-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th width="100">Price</th>
                                                <th width="100">Quantity</th>
                                                <th width="120">Discount</th>
                                                <th width="120">Tax</th>
                                                <th width="120">Total</th>
                                                <th width="50">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Products will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title">Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="payment_method">Payment Method</label>
                                            <select class="form-control @error('payment_method') is-invalid @enderror" 
                                                id="payment_method" name="payment_method">
                                                <option value="">Select Payment Method</option>
                                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                            </select>
                                            @error('payment_method')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="paid_amount">Paid Amount</label>
                                            <input type="number" step="0.01" class="form-control @error('paid_amount') is-invalid @enderror" 
                                                id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}">
                                            @error('paid_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tr>
                                                    <th style="width:50%">Subtotal:</th>
                                                    <td>
                                                        <span id="subtotal">0.00</span>
                                                        <input type="hidden" name="subtotal" id="subtotal-input" value="0">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Discount:</th>
                                                    <td>
                                                        <span id="discount-total">0.00</span>
                                                        <input type="hidden" name="discount" id="discount-input" value="0">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Tax:</th>
                                                    <td>
                                                        <span id="tax-total">0.00</span>
                                                        <input type="hidden" name="tax" id="tax-input" value="0">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total:</th>
                                                    <td>
                                                        <span id="grand-total">0.00</span>
                                                        <input type="hidden" name="total_amount" id="total-input" value="0">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Paid:</th>
                                                    <td><span id="paid">0.00</span></td>
                                                </tr>
                                                <tr>
                                                    <th>Due:</th>
                                                    <td><span id="due">0.00</span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Save Sale
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Product Row Template -->
<template id="product-row-template">
    <tr class="product-row">
        <td>
            <input type="hidden" name="product_id[]" class="product-id">
            <div class="product-name"></div>
            <small class="text-muted product-code"></small>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control form-control-sm price" name="price[]" required>
        </td>
        <td>
            <input type="number" min="1" class="form-control form-control-sm quantity" name="quantity[]" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm discount" name="discount[]" value="0">
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm tax" name="tax[]" value="0">
        </td>
        <td>
            <span class="row-total">0.00</span>
            <input type="hidden" class="row-total-input" name="total[]">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-product">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@section('scripts')
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2();
        
        // Add product to table
        $('#add-product').click(function() {
            const productSelect = $('#product-selector');
            const productId = productSelect.val();
            
            if (productId === '') {
                alert('Please select a product');
                return;
            }
            
            const productOption = productSelect.find('option:selected');
            const productName = productOption.text();
            const productPrice = parseFloat(productOption.data('price'));
            const productCode = productOption.data('code');
            const productStock = parseInt(productOption.data('stock'));
            
            // Check if product already added
            if ($('.product-id[value="' + productId + '"]').length > 0) {
                alert('This product is already added to the sale');
                return;
            }
            
            // Create new row from template
            const template = document.querySelector('#product-row-template');
            const clone = document.importNode(template.content, true);
            
            // Fill product data
            clone.querySelector('.product-id').value = productId;
            clone.querySelector('.product-name').textContent = productName;
            clone.querySelector('.product-code').textContent = 'Code: ' + productCode + ' | Stock: ' + productStock;
            clone.querySelector('.price').value = productPrice;
            clone.querySelector('.quantity').value = 1;
            clone.querySelector('.quantity').setAttribute('max', productStock);
            
            // Calculate initial row total
            const total = productPrice;
            clone.querySelector('.row-total').textContent = total.toFixed(2);
            clone.querySelector('.row-total-input').value = total;
            
            // Add row to table
            document.querySelector('#products-table tbody').appendChild(clone);
            
            // Reset product selector
            productSelect.val('').trigger('change');
            
            // Recalculate totals
            calculateTotals();
        });
        
        // Remove product from table
        $(document).on('click', '.remove-product', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });
        
        // Recalculate row when inputs change
        $(document).on('input', '.price, .quantity, .discount, .tax', function() {
            const row = $(this).closest('tr');
            calculateRowTotal(row);
            calculateTotals();
        });
        
        // Calculate paid amount changes
        $('#paid_amount').on('input', function() {
            calculateTotals();
        });
        
        // Calculate row total
        function calculateRowTotal(row) {
            const price = parseFloat(row.find('.price').val()) || 0;
            const quantity = parseInt(row.find('.quantity').val()) || 0;
            const discount = parseFloat(row.find('.discount').val()) || 0;
            const tax = parseFloat(row.find('.tax').val()) || 0;
            
            const subtotal = price * quantity;
            const total = subtotal - discount + tax;
            
            row.find('.row-total').text(total.toFixed(2));
            row.find('.row-total-input').val(total);
        }
        
        // Calculate all totals
        function calculateTotals() {
            let subtotal = 0;
            let discountTotal = 0;
            let taxTotal = 0;
            let grandTotal = 0;
            
            $('.product-row').each(function() {
                const price = parseFloat($(this).find('.price').val()) || 0;
                const quantity = parseInt($(this).find('.quantity').val()) || 0;
                const discount = parseFloat($(this).find('.discount').val()) || 0;
                const tax = parseFloat($(this).find('.tax').val()) || 0;
                
                const rowSubtotal = price * quantity;
                subtotal += rowSubtotal;
                discountTotal += discount;
                taxTotal += tax;
                grandTotal += parseFloat($(this).find('.row-total-input').val()) || 0;
            });
            
            // Update summary totals
            $('#subtotal').text(subtotal.toFixed(2));
            $('#subtotal-input').val(subtotal);
            
            $('#discount-total').text(discountTotal.toFixed(2));
            $('#discount-input').val(discountTotal);
            
            $('#tax-total').text(taxTotal.toFixed(2));
            $('#tax-input').val(taxTotal);
            
            $('#grand-total').text(grandTotal.toFixed(2));
            $('#total-input').val(grandTotal);
            
            // Paid and due calculation
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            $('#paid').text(paidAmount.toFixed(2));
            
            const dueAmount = grandTotal - paidAmount;
            $('#due').text(dueAmount.toFixed(2));
        }
        
        // Form validation before submit
        $('#sale-form').on('submit', function(e) {
            const productRows = $('.product-row').length;
            if (productRows === 0) {
                e.preventDefault();
                alert('Please add at least one product to the sale');
                return false;
            }
            
            const customerId = $('#customer_id').val();
            if (!customerId) {
                e.preventDefault();
                alert('Please select a customer');
                return false;
            }
            
            // If paid amount is entered, payment method is required
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            const paymentMethod = $('#payment_method').val();
            
            if (paidAmount > 0 && !paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return false;
            }
            
            return true;
        });
    });
</script>
@endsection 