@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Category: {{ $category->name }}</h1>
    <div>
        <a href="{{ route('categories.show', $category->id) }}" class="btn btn-info btn-rounded">
            <i class="fas fa-eye fa-sm me-2"></i> View Category
        </a>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Categories
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                <small class="text-muted">Optional: Provide a brief description of this category</small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="icon" class="form-label">Icon Class (FontAwesome)</label>
                <div class="input-group">
                    <span class="input-group-text"><i id="icon-preview" class="fas {{ $category->icon ?? 'fa-folder' }}"></i></span>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="fa-folder" onkeyup="updateIconPreview()">
                </div>
                <small class="text-muted">Optional: Enter a FontAwesome icon class (e.g., fa-box, fa-tags)</small>
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <small class="text-muted">Inactive categories won't be displayed in product forms</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Statistics</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <p class="mb-1"><strong>Created:</strong> {{ $category->created_at->format('M d, Y') }}</p>
                            <p class="mb-0"><strong>Last Updated:</strong> {{ $category->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Update Category
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