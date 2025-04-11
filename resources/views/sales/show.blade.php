@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Sale Details</h1>
                <div>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Sales
                    </a>
                    <a href="{{ route('sales.pdf', $sale) }}" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                    @if($sale->status != 'completed' && $sale->status != 'canceled')
                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-primary">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    @endif
                    @if($sale->payment_status != 'paid')
                        <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-success">
                            <i class="fas fa-money-bill-wave mr-1"></i> Add Payment
                        </a>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Sale Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Sale Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
                                    <p><strong>Date:</strong> {{ $sale->date->format('M d, Y H:i') }}</p>
                                    <p><strong>Status:</strong> 
                                        @if($sale->status == 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($sale->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-danger">Canceled</span>
                                        @endif
                                    </p>
                                    <p><strong>Created By:</strong> {{ $sale->user->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Payment Status:</strong>
                                        @if($sale->payment_status == 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($sale->payment_status == 'partial')
                                            <span class="badge badge-info">Partial</span>
                                        @else
                                            <span class="badge badge-danger">Unpaid</span>
                                        @endif
                                    </p>
                                    <p><strong>Payment Method:</strong> {{ $sale->payment_method ?? 'N/A' }}</p>
                                    <p><strong>Total Amount:</strong> {{ currency_format($sale->total_amount) }}</p>
                                    <p><strong>Paid Amount:</strong> {{ currency_format($sale->paid_amount) }}</p>
                                    <p><strong>Due Amount:</strong> {{ currency_format($sale->due_amount) }}</p>
                                </div>
                            </div>
                            @if($sale->notes)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <p><strong>Notes:</strong></p>
                                        <p>{{ $sale->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> {{ $sale->customer->name }}</p>
                            <p><strong>Email:</strong> {{ $sale->customer->email }}</p>
                            <p><strong>Phone:</strong> {{ $sale->customer->phone }}</p>
                            <p><strong>Address:</strong> {{ $sale->customer->address }}</p>
                            <p><strong>City:</strong> {{ $sale->customer->city }}, {{ $sale->customer->state }} {{ $sale->customer->postal_code }}</p>
                            <p><strong>Country:</strong> {{ $sale->customer->country }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Items -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Sale Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Discount</th>
                                    <th>Tax</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $item->product->name }}
                                            <small class="text-muted d-block">{{ $item->product->code }}</small>
                                        </td>
                                        <td>{{ currency_format($item->price) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ currency_format($item->discount) }}</td>
                                        <td>{{ currency_format($item->tax) }}</td>
                                        <td class="text-right">{{ currency_format($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Subtotal:</th>
                                    <th class="text-right">{{ currency_format($sale->total_amount - $sale->tax + $sale->discount) }}</th>
                                </tr>
                                @if($sale->discount > 0)
                                    <tr>
                                        <th colspan="6" class="text-right">Discount:</th>
                                        <th class="text-right">{{ currency_format($sale->discount) }}</th>
                                    </tr>
                                @endif
                                @if($sale->tax > 0)
                                    <tr>
                                        <th colspan="6" class="text-right">Tax:</th>
                                        <th class="text-right">{{ currency_format($sale->tax) }}</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th colspan="6" class="text-right">Total:</th>
                                    <th class="text-right">{{ currency_format($sale->total_amount) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Paid:</th>
                                    <th class="text-right">{{ currency_format($sale->paid_amount) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Due:</th>
                                    <th class="text-right">{{ currency_format($sale->due_amount) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Payment History</h5>
                    @if($sale->payment_status != 'paid')
                        <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Add Payment
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>User</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->payments as $index => $payment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $payment->date->format('M d, Y H:i') }}</td>
                                        <td>{{ currency_format($payment->amount) }}</td>
                                        <td>{{ $payment->method }}</td>
                                        <td>{{ $payment->reference ?? 'N/A' }}</td>
                                        <td>{{ $payment->user->name }}</td>
                                        <td>{{ $payment->notes }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No payment records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 