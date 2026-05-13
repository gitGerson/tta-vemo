@extends('layouts.sneat')

@section('title')
    Trash - Images
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Deleted Images">
            <x-slot:actions>
                <a href="{{ route('images.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Images
                </a>
            </x-slot:actions>

            <x-datatable.table
                id="images-trash-table"
                :columns="['No', 'Title', 'File Name', 'Size', 'Uploaded By', 'Updated By', 'Deleted By', 'Deleted At', 'Actions']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="images-trash-table" ajax="{{ route('images.trash-data') }}" :export-exclude-last-column="false">
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'title', name: 'title' },
    { data: 'file_name', name: 'file_name' },
    { data: 'formatted_size', name: 'formatted_size', orderable: false, searchable: false },
    { data: 'created_by_name', name: 'created_by_name', orderable: false, searchable: false },
    { data: 'updated_by_name', name: 'updated_by_name', orderable: false, searchable: false },
    { data: 'deleted_by_name', name: 'deleted_by_name', orderable: false, searchable: false },
    { data: 'deleted_at', name: 'deleted_at' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
