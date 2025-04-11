@extends('layouts.app')

@section('title', 'Stock History')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Stock History: {{ $product->name }}</h1>
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
    <!-- Product Info Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Product Code</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $product->code }}</div>
                    </div>
                    <div class="col-auto">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 50px;">
                        @else
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Stock Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Current Stock</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $product->current_stock }} {{ ucfirst($product->unit) }}s</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Status Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-{{ $product->current_stock <= $product->min_stock ? 'danger' : 'info' }} shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-{{ $product->current_stock <= $product->min_stock ? 'danger' : 'info' }} text-uppercase mb-1">Stock Status</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            @if($product->current_stock <= $product->min_stock)
                                Low Stock
                            @elseif($product->max_stock && $product->current_stock >= $product->max_stock)
                                Overstocked
                            @else
                                In Stock
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-{{ $product->current_stock <= $product->min_stock ? 'exclamation-triangle' : 'check-circle' }} fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Movement History -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Stock Movement History</h6>
        <div>
            <a href="{{ route('products.stock.create', $product->id) }}?type=add" class="btn btn-success btn-sm me-1">
                <i class="fas fa-plus fa-sm"></i> Add Stock
            </a>
            <a href="{{ route('products.stock.create', $product->id) }}?type=remove" class="btn btn-danger btn-sm">
                <i class="fas fa-minus fa-sm"></i> Remove Stock
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="stockHistoryTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Reference</th>
                        <th>User</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockMovements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($movement->type == 'add')
                                    <span class="badge bg-success">Addition</span>
                                @elseif($movement->type == 'remove')
                                    <span class="badge bg-danger">Removal</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($movement->type) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->type == 'add')
                                    <span class="text-success">+{{ abs($movement->quantity) }}</span>
                                @else
                                    <span class="text-danger">-{{ abs($movement->quantity) }}</span>
                                @endif
                            </td>
                            <td>{{ $movement->before_stock }}</td>
                            <td>{{ $movement->after_stock }}</td>
                            <td>
                                @if($movement->reference)
                                    @if($movement->reference_type == 'purchase' && $movement->purchase_id)
                                        <a href="{{ route('purchases.show', $movement->purchase_id) }}">
                                            {{ $movement->reference }}
                                        </a>
                                    @elseif($movement->reference_type == 'sale' && $movement->sale_id)
                                        <a href="{{ route('sales.show', $movement->sale_id) }}">
                                            {{ $movement->reference }}
                                        </a>
                                    @else
                                        {{ $movement->reference }}
                                    @endif
                                    <small class="d-block text-muted">{{ ucfirst(str_replace('_', ' ', $movement->reference_type ?? '')) }}</small>
                                @else
                                    <span class="text-muted">Manual Adjustment</span>
                                @endif
                            </td>
                            <td>{{ $movement->user->name ?? 'System' }}</td>
                            <td>
                                @if($movement->notes)
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="popover" 
                                            title="Notes" data-bs-content="{{ $movement->notes }}">
                                        <i class="fas fa-sticky-note"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No stock movements found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $stockMovements->links() }}
        </div>
    </div>
</div>

<!-- Stock Statistics -->
<div class="row">
    <!-- Monthly Stock Movement Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Stock Movement</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="monthlyStockChart"></canvas>
                </div>
                <hr>
                <div class="text-center small mt-2">
                    <span class="me-2">
                        <i class="fas fa-circle text-success"></i> Stock Added
                    </span>
                    <span class="me-2">
                        <i class="fas fa-circle text-danger"></i> Stock Removed
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Summary -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Stock Movement Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="small font-weight-bold">
                        Total Additions <span class="float-end">{{ $totalAdd }} {{ ucfirst($product->unit) }}s</span>
                    </h5>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($totalAdd / ($totalAdd + $totalRemove)) * 100 }}%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <h5 class="small font-weight-bold">
                        Total Removals <span class="float-end">{{ $totalRemove }} {{ ucfirst($product->unit) }}s</span>
                    </h5>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($totalRemove / ($totalAdd + $totalRemove)) * 100 }}%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <h5 class="small font-weight-bold">
                        Initial Stock <span class="float-end">{{ $initialStock }} {{ ucfirst($product->unit) }}s</span>
                    </h5>
                </div>
                <div class="mb-4">
                    <h5 class="small font-weight-bold">
                        Current Stock <span class="float-end">{{ $product->current_stock }} {{ ucfirst($product->unit) }}s</span>
                    </h5>
                </div>
                <div class="text-center mt-4">
                    <h5 class="font-weight-bold">
                        Stock Turnover Rate:
                        <span class="{{ $turnoverRate > 3 ? 'text-success' : ($turnoverRate > 1 ? 'text-info' : 'text-warning') }}">
                            {{ number_format($turnoverRate, 2) }}
                        </span>
                    </h5>
                    <small class="text-muted">
                        Number of times inventory is sold and replaced over the last 30 days
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
        
        // Monthly Stock Chart
        var ctx = document.getElementById("monthlyStockChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: "Stock Added",
                        lineTension: 0.3,
                        backgroundColor: "rgba(40, 167, 69, 0.05)",
                        borderColor: "rgba(40, 167, 69, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(40, 167, 69, 1)",
                        pointBorderColor: "rgba(40, 167, 69, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(40, 167, 69, 1)",
                        pointHoverBorderColor: "rgba(40, 167, 69, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: {!! json_encode($chartData['additions']) !!},
                    },
                    {
                        label: "Stock Removed",
                        lineTension: 0.3,
                        backgroundColor: "rgba(220, 53, 69, 0.05)",
                        borderColor: "rgba(220, 53, 69, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(220, 53, 69, 1)",
                        pointBorderColor: "rgba(220, 53, 69, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(220, 53, 69, 1)",
                        pointHoverBorderColor: "rgba(220, 53, 69, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: {!! json_encode($chartData['removals']) !!},
                    }
                ],
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
                    xAxes: [{
                        time: {
                            unit: 'date'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            beginAtZero: true
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': ' + tooltipItem.yLabel + ' {{ ucfirst($product->unit) }}s';
                        }
                    }
                }
            }
        });
    });
</script>
@endsection 