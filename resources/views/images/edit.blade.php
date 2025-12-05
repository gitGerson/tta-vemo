@extends('layouts.sneat')

@section('title')
    Edit Image
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Image</h5>
                        <a href="{{ route('images.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('images.update', $image->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $image->title) }}" required
                                       placeholder="Enter image title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Enter image description (optional)">{{ old('description', $image->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="mb-2">
                                    <img src="{{ $image->url }}" alt="{{ $image->title }}" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                                </div>
                                <small class="text-muted">{{ $image->file_name }} ({{ $image->formatted_size }})</small>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Replace Image (Optional)</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                       id="image" name="image" accept="image/*" onchange="previewImage(event)">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to keep current image. Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 10MB</div>
                            </div>

                            <div class="mb-3" id="image-preview-container" style="display: none;">
                                <label class="form-label">New Image Preview</label>
                                <div>
                                    <img id="image-preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Update
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
