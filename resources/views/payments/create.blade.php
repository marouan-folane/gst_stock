@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Add Payment</h1>
                @if($sale)
                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Sale
                    </a>
                @else
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Payments
                    </a>
                @endif
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Payment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf

                        @if($sale)
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Invoice Number</label>
                                        <input type="text" class="form-control" value="{{ $sale->invoice_number }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer</label>
                                        <input type="text" class="form-control" value="{{ $sale->customer->name }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total Amount</label>
                                        <input type="text" class="form-control" value="{{ currency_format($sale->total_amount) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Paid Amount</label>
                                        <input type="text" class="form-control" value="{{ currency_format($sale->paid_amount) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Due Amount</label>
                                        <input type="text" class="form-control" value="{{ currency_format($sale->due_amount) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="form-group">
                                <label for="sale_id">Sale</label>
                                <select class="form-control select2 @error('sale_id') is-invalid @enderror" id="sale_id" name="sale_id" required>
                                    <option value="">Select Sale</option>
                                    @foreach($sales as $sale)
                                        <option value="{{ $sale->id }}" {{ old('sale_id') == $sale->id ? 'selected' : '' }}>
                                            {{ $sale->invoice_number }} - {{ $sale->customer->name }} (Due: {{ currency_format($sale->due_amount) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('sale_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                    id="amount" name="amount" value="{{ old('amount', $sale ? min($sale->due_amount, $sale->total_amount) : '') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="method">Payment Method</label>
                            <select class="form-control @error('method') is-invalid @enderror" id="method" name="method" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="credit_card" {{ old('method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                id="reference" name="reference" value="{{ old('reference') }}" 
                                placeholder="Transaction ID, Cheque Number, etc.">
                            @error('reference')
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
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('.select2').select2();
    });
</script>
@endsection