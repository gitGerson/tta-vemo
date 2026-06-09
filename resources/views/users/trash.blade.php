@extends('layouts.sneat')

@section('title')
    Tempat Sampah User
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" layout="fitColumns" title="Tempat Sampah User">
            <x-slot:actions>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar User
                </a>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
