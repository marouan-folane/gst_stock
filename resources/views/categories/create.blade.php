@extends('layouts.app')

@section('title', 'Add New Category')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Category</h1>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-rounded">
        <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Categories
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                <small class="text-muted">Optional: Provide a brief description of this category</small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="icon" class="form-label">Icon Class (FontAwesome)</label>
                <div class="input-group">
                    <span class="input-group-text"><i id="icon-preview" class="fas fa-folder"></i></span>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', 'fa-folder') }}" placeholder="fa-folder" onkeyup="updateIconPreview()">
                </div>
                <small class="text-muted">Optional: Enter a FontAwesome icon class (e.g., fa-box, fa-tags)</small>
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <small class="text-muted">Inactive categories won't be displayed in product forms</small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Save Category
            </button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i> Cancel
            </a>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateIconPreview() {
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('icon-preview');
        
        // Remove all classes
        iconPreview.className = '';
        
        // Add the base classes
        iconPreview.classList.add('fas');
        
        // Add the user input icon class if it exists
        if (iconInput.value) {
            iconPreview.classList.add(iconInput.value);
        } else {
            iconPreview.classList.add('fa-folder');
        }
    }
</script>
@endsection 