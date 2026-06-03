@extends('layouts.sneat')

@section('title')
    Trash Tipe Maintenance
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-table :table="$table" engine="tabulator" title="Trash Tipe Maintenance">
            <x-slot:actions>
                <a href="{{ route('type-maintenances.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
