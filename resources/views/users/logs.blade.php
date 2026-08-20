@extends('layouts.sneat')

@section('title')
    User Activity Logs
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="User Activity Logs">
            <x-slot:actions>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar User
                </a>
            </x-slot:actions>

            <x-datatable.table
                id="logs-table"
                :columns="['Waktu', 'User', 'Aksi', 'Target User', 'Deskripsi', 'IP Address']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts
    table-id="logs-table"
    ajax="{{ route('users.logs.data') }}"
    :order="[[0, 'desc']]"
    :export-exclude-last-column="false"
>
    { data: 'formatted_date', name: 'created_at' },
    { data: 'user_name', name: 'user_name' },
    { data: 'action_badge', name: 'action', orderable: false, searchable: false },
    { data: 'target_user_name', name: 'target_user_name' },
    { data: 'description', name: 'description' },
    { data: 'ip_address', name: 'ip_address' }
</x-datatable.scripts>
