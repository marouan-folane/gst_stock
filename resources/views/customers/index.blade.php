@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customers</h1>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-rounded">
        <i class="fas fa-plus fa-sm me-2"></i> Add New Customer
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Customers</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Export Options:</div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-file-csv fa-sm fa-fw me-2 text-gray-400"></i>
                    Export CSV
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-file-pdf fa-sm fa-fw me-2 text-gray-400"></i>
                    Export PDF
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-print fa-sm fa-fw me-2 text-gray-400"></i>
                    Print
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="customersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">Image</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>Postal Code</th>

                        
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                       
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->address }}</td>
                        <td>{{ $customer->city }}</td>
                        <td>{{ $customer->state }}</td>
                        <td>{{ $customer->postal_code }}</td>
                        <td>{{ $customer->notes }}</td>
                        <td>{{ $customer->status }}</td>
                        <td class="action-buttons">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                          
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this customer?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush 