@extends('layouts.sneat')

@section('title')
    Image Details
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Image Details</h5>
                        <div>
                            @if(auth()->user()->hasPermission('images.edit'))
                                <a href="{{ route('images.edit', $image) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                            @endif
                            <a href="{{ $image->url }}" download target="_blank" class="btn btn-success me-2">
                                <i class="bx bx-download me-1"></i> Download
                            </a>
                            <a href="{{ route('images.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12 text-center">
                                <img src="{{ $image->url }}" alt="{{ $image->title }}" style="max-width: 100%; max-height: 500px; border-radius: 8px;">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="200">ID</th>
                                        <td>{{ $image->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Title</th>
                                        <td>{{ $image->title }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ $image->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>File Name</th>
                                        <td>{{ $image->file_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>File Path</th>
                                        <td><code>{{ $image->file_path }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>File Type</th>
                                        <td>{{ $image->file_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>File Size</th>
                                        <td>{{ $image->formatted_size }}</td>
                                    </tr>
                                    <tr>
                                        <th>URL</th>
                                        <td><a href="{{ $image->url }}" target="_blank">{{ $image->url }}</a></td>
                                    </tr>
                                    <tr>
                                        <th>Uploaded At</th>
                                        <td>{{ $image->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ $image->updated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Uploaded By</th>
                                        <td>{{ $image->creator ? $image->creator->name : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated By</th>
                                        <td>{{ $image->updater ? $image->updater->name : '-' }}</td>
                                    </tr>
                                    @if($image->deleted_at)
                                    <tr>
                                        <th>Deleted At</th>
                                        <td>{{ $image->deleted_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Deleted By</th>
                                        <td>{{ $image->deleter ? $image->deleter->name : '-' }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
