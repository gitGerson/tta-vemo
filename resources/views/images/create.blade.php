@extends('layouts.sneat')

@section('title')
    Upload Image
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Upload New Image</h5>
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

                        <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required
                                       placeholder="Enter image title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Enter image description (optional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <x-form.file
                                name="image"
                                label="Image File"
                                mode="filepond"
                                accept="image/*"
                                :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']"
                                maxFileSize="10MB"
                                :existingFiles="old('image') ? [[
                                    'source' => old('image'),
                                    'options' => ['type' => 'local'],
                                ]] : []"
                                :required="true"
                                help="Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 10MB"
                            />

                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-upload me-1"></i> Upload
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
