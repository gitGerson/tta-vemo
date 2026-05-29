@extends('layouts.sneat')

@section('title')
    Daftar Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Daftar Driver">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.drivers.delete'))
                        <a href="{{ route('drivers.trash') }}" class="btn btn-secondary">
                            <i class="bx bx-trash me-1"></i> Trash
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('master.drivers.create'))
                        <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Driver
                        </a>
                    @endif
                </div>
            </x-slot:actions>

            <x-datatable.table
                id="drivers-table"
                :columns="['No', 'Nama', 'No Identitas', 'No Telepon', 'Status', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="drivers-table" ajax="{{ route('drivers.data') }}" clickable-rows>
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'no_identity', name: 'no_identity' },
    { data: 'no_phone_1', name: 'no_phone_1' },
    { data: 'status', name: 'status' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
