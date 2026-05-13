@extends('layouts.sneat')

@section('title')
    Manajemen Role
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

        <x-datatable.card title="Manajemen Role">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('management.roles.create'))
                    <a href="{{ route('roles.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Role
                    </a>
                @endif
            </x-slot:actions>

            <x-datatable.table
                id="roles-table"
                :columns="['ID', 'Nama Role', 'Display Name', 'Deskripsi', 'Jumlah User', 'Jumlah Permission', 'Dibuat', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="roles-table" ajax="{{ route('roles.data') }}" :export-exclude-last-column="false" clickable-rows>
    { data: 'id', name: 'id' },
    { data: 'name', name: 'name' },
    { data: 'display_name', name: 'display_name' },
    {
        data: 'description',
        name: 'description',
        render: function(data, type, row) {
            return data || '-';
        }
    },
    { data: 'users_count', name: 'users_count', searchable: false },
    { data: 'permissions_count', name: 'permissions_count', searchable: false },
    {
        data: 'created_at',
        name: 'created_at',
        render: function(data, type, row) {
            return moment(data).format('DD MMM YYYY HH:mm');
        }
    },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
