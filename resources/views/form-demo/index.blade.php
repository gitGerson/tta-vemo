@extends('layouts.sneat')

@section('title')
    Form Demo
@endsection

@section('content')
    @php
        $existingImagePath = public_path('assets/logo.png');
        $existingImageUrl = asset('assets/logo.png');
        $existingImageSize = is_file($existingImagePath) ? filesize($existingImagePath) : null;
        $existingImageMime = is_file($existingImagePath) ? (mime_content_type($existingImagePath) ?: 'image/png') : 'image/png';
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Form Components Demo</h4>
                <p class="text-muted mb-0">Reference page for the reusable <code>x-form.*</code> Blade components.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('demo_result'))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Normalized Payload</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0 small">{{ json_encode(session('demo_result'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif

        <form action="{{ route('form-demo.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Basic Inputs</h5>
                        </div>
                        <div class="card-body">
                            <x-form.text
                                name="title"
                                label="Title"
                                value="Demo Content Title"
                                placeholder="Enter title"
                                help="Basic text input with label, help text, and old value support."
                                required
                            />

                            <x-form.number
                                name="quantity"
                                label="Quantity"
                                value="12"
                                min="0"
                                help="Numeric input example."
                            />

                            <x-form.select
                                name="status"
                                label="Status"
                                :options="$statusOptions"
                                value="review"
                                placeholder="Choose status"
                                help="Native select component with placeholder support."
                            />

                            <x-form.select
                                name="role_template"
                                label="Role Template"
                                :options="$roleTemplateOptions"
                                optionValue="id"
                                optionLabel="display_name"
                                value="admin_template"
                                placeholder="Select template"
                                help="Select using object arrays."
                            />

                            <x-form.checkbox
                                name="featured"
                                label="Featured content"
                                :checked="true"
                                help="Standard checkbox component."
                            />

                            <x-form.switch
                                name="is_active"
                                label="Active"
                                :checked="true"
                                help="Bootstrap switch-style toggle."
                            />
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Date Inputs</h5>
                        </div>
                        <div class="card-body">
                            <x-form.date
                                name="publish_date"
                                label="Publish Date"
                                value="2026-04-06"
                                help="Native browser date input."
                            />

                            <x-form.date
                                name="event_date"
                                label="Event Date"
                                mode="daterangepicker"
                                :single="true"
                                value="2026-04-10"
                                help="Single-date picker mode powered by Date Range Picker."
                            />

                            <x-form.date
                                name="booking_window"
                                label="Booking Window"
                                mode="daterangepicker"
                                :single="false"
                                value="2026-04-10 - 2026-04-20"
                                help="Range picker mode using the same component."
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Text Areas</h5>
                        </div>
                        <div class="card-body">
                            <x-form.textarea
                                name="summary"
                                label="Summary"
                                rows="4"
                                value="Short summary text for the demo module."
                                placeholder="Write a summary"
                                help="Plain textarea component."
                            />

                            <x-form.rich-editor
                                name="body"
                                label="Rich Editor"
                                value="<h2>Rich editor demo</h2><p>This editor now uses the TipTap-based reusable component. Try headings, alignment, links, lists, code block, and image insertion.</p>"
                                help="TipTap-based rich editor reusable component."
                            />
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">File Input</h5>
                        </div>
                        <div class="card-body">
                            <x-form.file
                                name="attachment_native"
                                label="Attachment"
                                help="Native file input mode."
                                accept="image/*,.pdf"
                            />

                            <x-form.file
                                name="attachment_filepond"
                                label="Attachment FilePond"
                                mode="filepond"
                                accept="image/*,.pdf"
                                :acceptedFileTypes="['image/png', 'image/jpeg', 'application/pdf']"
                                maxFileSize="5MB"
                                help="FilePond mode now uses the generic upload endpoints by default."
                            />

                            <x-form.file
                                name="attachment_filepond_existing"
                                label="Attachment FilePond With Existing Image"
                                mode="filepond"
                                accept="image/*"
                                :acceptedFileTypes="['image/png', 'image/jpeg', 'image/webp']"
                                :existingFiles="[[
                                    'source' => $existingImageUrl,
                                    'options' => [
                                        'type' => 'local',
                                        'file' => [
                                            'name' => 'logo.png',
                                            'size' => $existingImageSize,
                                            'type' => $existingImageMime,
                                        ],
                                    ],
                                ]]"
                                help="Example with a preloaded stored image."
                            />

                            <button type="submit" class="btn btn-primary">
                                Submit Demo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
