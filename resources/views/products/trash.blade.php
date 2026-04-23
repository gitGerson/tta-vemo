@extends('layouts.sneat')

@section('title')
    Tempat Sampah Produk
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Tempat Sampah Produk">
            <x-slot:actions>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Produk
                </a>
            </x-slot:actions>

            <x-datatable.table
                id="trash-table"
                :columns="['ID', 'Nama', 'Kategori', 'Dihapus', 'Dihapus Oleh', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="trash-table" ajax="{{ route('products.trash.data') }}">
    { data: 'id', name: 'id' },
    { data: 'name', name: 'name' },
    { data: 'category_name', name: 'category.name' },
    {
        data: 'deleted_at',
        name: 'deleted_at',
        render: function(data) {
            return moment(data).format('DD MMM YYYY HH:mm');
        }
    },
    { data: 'deleted_by_name', name: 'deleted_by_name' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
