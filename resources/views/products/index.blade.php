@extends('layouts.sneat')

@section('title')
    Daftar Produk
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Daftar Produk">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('master.products.delete'))
                    <a href="{{ route('products.trash') }}" class="btn btn-secondary me-2">
                        <i class="bx bx-trash me-1"></i> Tempat Sampah
                    </a>
                @endif
                @if(auth()->user()->hasPermission('master.products.create'))
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Produk
                    </a>
                @endif
            </x-slot:actions>

            <x-datatable.table
                id="products-table"
                :columns="['No', 'Gambar', 'Nama', 'Kategori', 'SKU', 'Harga', 'Stok', 'Status', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="products-table" ajax="{{ route('products.data') }}" clickable-rows>
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'image_preview', name: 'image', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'category_name', name: 'category.name' },
    { data: 'sku', name: 'sku' },
    { data: 'formatted_price', name: 'price' },
    { data: 'stock', name: 'stock' },
    { data: 'status', name: 'is_active', orderable: false, searchable: false },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
