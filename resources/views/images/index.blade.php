@extends('layouts.sneat')

@section('title')
    Image Gallery
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Image Gallery">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('images.delete'))
                    <a href="{{ route('images.trash') }}" class="btn btn-secondary me-2">
                        <i class="bx bx-trash me-1"></i> Trash
                    </a>
                @endif
                @if(auth()->user()->hasPermission('images.create'))
                    <a href="{{ route('images.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Upload Image
                    </a>
                @endif
            </x-slot:actions>

            <x-datatable.table
                id="images-table"
                :columns="['No', 'Title', 'File Name', 'Type', 'Size', 'Uploaded By', 'Uploaded At', 'Actions']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="images-table" ajax="{{ route('images.data') }}" :export-exclude-last-column="false" clickable-rows>
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'title', name: 'title' },
    { data: 'file_name', name: 'file_name' },
    { data: 'file_type', name: 'file_type' },
    { data: 'formatted_size', name: 'formatted_size', orderable: false, searchable: false },
    { data: 'created_by_name', name: 'created_by_name', orderable: false, searchable: false },
    { data: 'formatted_date', name: 'formatted_date', orderable: false, searchable: false },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
