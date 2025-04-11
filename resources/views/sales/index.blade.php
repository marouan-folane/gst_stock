@extends('layouts.app')

@section('title', 'Sales')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Sales</h1>
                    <a href="{{ route('sales.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle mr-1"></i> New Sale
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <form method="GET" action="{{ route('sales.index') }}" class="row align-items-center">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label for="customer" class="sr-only">Customer</label>
                                <select id="customer" name="customer_id" class="form-control select2">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label for="status" class="sr-only">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label for="payment_status" class="sr-only">Payment Status</label>
                                <select id="payment_status" name="payment_status" class="form-control">
                                    <option value="">All Payment Status</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label for="date_range" class="sr-only">Date Range</label>
                                <input type="text" id="date_range" name="date_range" class="form-control daterange" 
                                    placeholder="Date Range" value="{{ request('date_range') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->customer->name }}</td>
                                        <td>{{ $sale->date->format('M d, Y') }}</td>
                                        <td>{{ currency_format($sale->total_amount) }}</td>
                                        <td>{{ currency_format($sale->paid_amount) }}</td>
                                        <td>{{ currency_format($sale->due_amount) }}</td>
                                        <td>
                                            @if ($sale->status == 'completed')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif ($sale->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @else
                                                <span class="badge badge-danger">Canceled</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($sale->payment_status == 'paid')
                                                <span class="badge badge-success">Paid</span>
                                            @elseif ($sale->payment_status == 'partial')
                                                <span class="badge badge-info">Partial</span>
                                            @else
                                                <span class="badge badge-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group">
                                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('sales.pdf', $sale) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="Download PDF" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @if ($sale->payment_status != 'paid')
                                                    <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="Add Payment">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#deleteModal{{ $sale->id }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $sale->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $sale->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel{{ $sale->id }}">Delete Sale</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete sale <strong>{{ $sale->invoice_number }}</strong>?</p>
                                                            <p class="text-danger"><small>This action cannot be undone.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <form action="{{ route('sales.destroy', $sale) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No sales found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} sales
                            </div>
                            <div>
                                {{ $sales->appends(request()->query())->links() }}
                            </div>
                        </div>
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
        
        $('.daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });
        
        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });
        
        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection 