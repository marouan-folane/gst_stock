@extends('layouts.app')

@section('title', 'Stock History')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Stock History: {{ $product->name }}</h1>
    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 me-2"></i> Back to Product
    </a>
</div>

<!-- Product Information Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <strong>Product Name:</strong>
                <p>{{ $product->name }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Product Code:</strong>
                <p>{{ $product->code }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Category:</strong>
                <p>{{ $product->category->name }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Current Stock:</strong>
                <p>
                    <span class="badge {{ $product->current_stock > $product->min_stock ? 'bg-primary' : 'bg-danger' }} px-3 py-2">
                        {{ $product->current_stock }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Stock History Card -->
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
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Reference/Reason</th>
                        <th>Adjusted By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->created_at->format('M d, Y g:i A') }}</td>
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
                            <td>{{ $adjustment->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No stock adjustments found for this product</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $adjustments->links() }}
        </div>
    </div>
</div>

<!-- Stock Chart Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Stock Level History</h6>
    </div>
    <div class="card-body">
        <div class="chart-area">
            <canvas id="stockHistoryChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Extract data from adjustments
        const adjustments = @json($adjustments->sortBy('created_at')->values());
        const labels = adjustments.map(a => new Date(a.created_at).toLocaleDateString());
        const stockLevels = adjustments.map(a => a.new_stock);
        
        // Add current date and stock if not empty
        if (labels.length > 0) {
            labels.push('Current');
            stockLevels.push({{ $product->current_stock }});
        }
        
        // Create the chart
        const ctx = document.getElementById('stockHistoryChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Stock Level',
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: stockLevels
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                beginAtZero: true
                            },
                            grid: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10
                        }
                    }
                }
            });
        }
    });
</script>
@endsection

@section('styles')
<style>
    .chart-area {
        position: relative;
        height: 20rem;
        width: 100%;
    }
</style>
@endsection 