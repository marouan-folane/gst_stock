@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Stock Adjustments</h1>
</div>

<!-- Stock Adjustments Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Stock Adjustment History</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Reference</th>
                        <th>Adjusted By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockAdjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->created_at->format('M d, Y g:i A') }}</td>
                            <td>
                                <a href="{{ route('products.show', $adjustment->product_id) }}">
                                    {{ $adjustment->product->name }}
                                </a>
                            </td>
                            <td>
                                @if($adjustment->type == 'addition')
                                    <span class="badge bg-success">Addition</span>
                                @elseif($adjustment->type == 'removal')
                                    <span class="badge bg-danger">Removal</span>
                                @elseif($adjustment->type == 'sale')
                                    <span class="badge bg-info">Sale</span>
                                @elseif($adjustment->type == 'purchase')
                                    <span class="badge bg-primary">Purchase</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($adjustment->type) }}</span>
                                @endif
                            </td>
                            <td>{{ $adjustment->quantity }}</td>
                            <td>{{ $adjustment->previous_stock }}</td>
                            <td>{{ $adjustment->new_stock }}</td>
                            <td>{{ $adjustment->reference }}</td>
                            <td>{{ $adjustment->user->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No stock adjustments found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $stockAdjustments->links() }}
        </div>
    </div>
</div>
@endsection 