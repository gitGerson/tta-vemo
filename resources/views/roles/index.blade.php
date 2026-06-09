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

        <x-table :table="$table" engine="tabulator" layout="fitColumns" title="Manajemen Role">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('management.roles.create'))
                    <a href="{{ route('roles.create') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Role
                    </a>
                @endif
            </x-slot:actions>
        </x-table>
    </div>
@endsection
