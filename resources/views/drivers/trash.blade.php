@extends('layouts.sneat')

@section('title')
    Trash Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-datatable.card title="Trash Driver">
            <x-slot:actions>
                <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>

            <x-datatable.table
                id="drivers-trash-table"
                :columns="['No', 'Nama', 'No Identitas', 'No Telepon', 'Status', 'Dihapus Pada', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts table-id="drivers-trash-table" ajax="{{ route('drivers.trash.data') }}">
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'no_identity', name: 'no_identity' },
    { data: 'no_phone_1', name: 'no_phone_1' },
    { data: 'status', name: 'status' },
    { data: 'deleted_at', name: 'deleted_at' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
