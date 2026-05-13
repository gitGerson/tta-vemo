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

                        <form action="{{ route('images.update', $image) }}" method="POST" enctype="multipart/form-data">
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

                            @php
                                $imageSource = old('image') ?: $image->url;
                            @endphp

                            <x-form.file
                                name="image"
                                label="Replace Image (Optional)"
                                mode="filepond"
                                accept="image/*"
                                :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']"
                                maxFileSize="10MB"
                                :existingFiles="$imageSource ? [[
                                    'source' => $imageSource,
                                    'options' => [
                                        'type' => 'local',
                                        'file' => [
                                            'name' => basename((string) $imageSource),
                                            'size' => old('image') ? null : $image->file_size,
                                            'type' => old('image') ? null : $image->file_type,
                                        ],
                                    ],
                                ]] : []"
                                help="Leave empty to keep current image. Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 10MB"
                            />

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
