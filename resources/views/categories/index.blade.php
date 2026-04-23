@extends('layouts.sneat')

@section('title')
    Daftar Kategori
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Daftar Kategori">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('master.categories.delete'))
                    <a href="{{ route('categories.trash') }}" class="btn btn-secondary me-2">
                        <i class="bx bx-trash me-1"></i> Tempat Sampah
                    </a>
                @endif
                @if(auth()->user()->hasPermission('master.categories.create'))
                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Kategori
                    </a>
                @endif
            </x-slot:actions>

            <x-datatable.table
                id="categories-table"
                :columns="['No', 'Gambar', 'Nama', 'Slug', 'Jumlah Produk', 'Status', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="categories-table" ajax="{{ route('categories.data') }}" clickable-rows>
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'image_preview', name: 'image', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'slug', name: 'slug' },
    { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
    { data: 'status', name: 'is_active', orderable: false, searchable: false },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
