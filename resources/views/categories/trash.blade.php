@extends('layouts.sneat')

@section('title')
    Tempat Sampah Kategori
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Tempat Sampah Kategori">
            <x-slot:actions>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Kategori
                </a>
            </x-slot:actions>

            <x-datatable.table
                id="trash-table"
                :columns="['ID', 'Nama', 'Slug', 'Dihapus', 'Dihapus Oleh', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="trash-table" ajax="{{ route('categories.trash.data') }}">
    { data: 'id', name: 'id' },
    { data: 'name', name: 'name' },
    { data: 'slug', name: 'slug' },
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
