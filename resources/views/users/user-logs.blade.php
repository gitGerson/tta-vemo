@extends('layouts.sneat')

@section('title')
    Activity Log - {{ $user->name }}
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Activity Log: {{ $user->name }} ({{ $user->username }})">
            <x-slot:actions>
                <a href="{{ route('users.show', $user) }}" class="btn btn-info me-2">
                    <i class="bx bx-show me-1"></i> Lihat Pengguna
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>

            <x-datatable.table
                id="user-logs-table"
                :columns="['Waktu', 'Dilakukan Oleh', 'Aksi', 'Deskripsi', 'IP Address']"
            />
        </x-datatable.card>
    </div>
@endsection

<x-datatable.scripts
    table-id="user-logs-table"
    ajax="{{ route('users.user-logs.data', $user) }}"
    :order="[[0, 'desc']]"
    :export-exclude-last-column="false"
>
    { data: 'formatted_date', name: 'created_at' },
    { data: 'user_name', name: 'user_name' },
    { data: 'action_badge', name: 'action', orderable: false, searchable: false },
    { data: 'description', name: 'description' },
    { data: 'ip_address', name: 'ip_address' }
</x-datatable.scripts>
