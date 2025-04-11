@extends('layouts.app')

@section('title', 'Add Monitored Category')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Monitored Category</h1>
        <a href="{{ route('sensible-categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to List
        </a>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please check the form below for errors.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <ul class="mt-2 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Create Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Category Monitoring Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('sensible-categories.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control select2 @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="min_quantity" class="form-label">Minimum Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="min_quantity" id="min_quantity" class="form-control @error('min_quantity') is-invalid @enderror" 
                               value="{{ old('min_quantity') }}" min="1" placeholder="Enter minimum stock quantity" required>
                        @error('min_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Notification will be sent when stock falls below this value</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="notification_email" class="form-label">Notification Email <span class="text-danger">*</span></label>
                        <input type="email" name="notification_email" id="notification_email" class="form-control @error('notification_email') is-invalid @enderror" 
                               value="{{ old('notification_email') }}" placeholder="Enter email address" required>
                        @error('notification_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="notification_frequency" class="form-label">Notification Frequency <span class="text-danger">*</span></label>
                        <select name="notification_frequency" id="notification_frequency" class="form-control @error('notification_frequency') is-invalid @enderror" required>
                            <option value="">Select Frequency</option>
                            <option value="daily" {{ old('notification_frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('notification_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('notification_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                        @error('notification_frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                               {{ old('is_active') ? 'checked' : '' }} value="1">
                        <label for="is_active" class="form-check-label">Activate monitoring for this category</label>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                    <a href="{{ route('sensible-categories.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select a category",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection 