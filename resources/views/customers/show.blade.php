@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customer Details: {{ $customer->name }}</h1>
    <div>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-edit fa-sm me-2"></i> Edit Customer
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Customers
        </a>
    </div>
</div>

<div class="row">
    <!-- Customer Basic Info -->
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Customer ID:</span>
                    <span class="badge bg-primary">{{ $customer->id }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Name:</span>
                    <span class="fw-semibold">{{ $customer->name }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Email:</span>
                    <span>{{ $customer->email }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Phone:</span>
                    <span>{{ $customer->phone ?? 'N/A' }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-weight-bold text-uppercase">Created At:</span>
                    <span>{{ $customer->created_at ? $customer->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-xs font-weight-bold text-uppercase">Last Updated:</span>
                    <span>{{ $customer->updated_at ? $customer->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Details -->
    <div class="col-xl-8 col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="30%" class="text-gray-700">Address</th>
                                <td>{{ $customer->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">City</th>
                                <td>{{ $customer->city ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">State</th>
                                <td>{{ $customer->state ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Country</th>
                                <td>{{ $customer->country ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-700">Postal Code</th>
                                <td>{{ $customer->postal_code ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($customer->notes)
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
            </div>
            <div class="card-body">
                <div class="p-3">
                    {{ $customer->notes }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Customer Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this customer? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection