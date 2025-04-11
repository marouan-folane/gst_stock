@extends('layouts.app')

@section('title', 'Sensible Categories')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Category Monitoring</h1>
    <a href="{{ route('sensible-categories.create') }}" class="btn btn-primary btn-rounded">
        <i class="fas fa-plus fa-sm me-2"></i> Add New Category
    </a>
</div>

<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Sensible Categories List -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Monitored Categories</h6>
    </div>
    <div class="card-body">
        @if(count($sensibleCategories) > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Min. Quantity</th>
                            <th>Notification Email</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sensibleCategories as $sensibleCategory)
                            <tr>
                                <td>{{ $sensibleCategory->category->name ?? 'N/A' }}</td>
                                <td>{{ $sensibleCategory->min_quantity }}</td>
                                <td>{{ $sensibleCategory->notification_email }}</td>
                                <td>
                                    <span class="text-capitalize">{{ $sensibleCategory->notification_frequency }}</span>
                                </td>
                                <td>
                                    @if($sensibleCategory->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('sensible-categories.edit', $sensibleCategory->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('sensible-categories.destroy', $sensibleCategory->id) }}" 
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                {{ $sensibleCategories->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <p class="mb-0">No monitored categories found.</p>
                <a href="{{ route('sensible-categories.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-1"></i> Add Your First Category
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Initialize DataTable
        $('#dataTable').DataTable({
            "ordering": true,
            "info": true,
            "paging": false,
            "searching": true,
            "language": {
                "emptyTable": "No monitored categories found"
            }
        });
        
        // Confirm Delete
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this monitored category?')) {
                $(this).unbind('submit').submit();
            }
        });
    });
</script>
@endsection 