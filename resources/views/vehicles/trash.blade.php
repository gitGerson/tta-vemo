@extends('layouts.sneat')

@section('title')
    Trash Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-table :table="$table" engine="tabulator" title="Trash Kendaraan">
            <x-slot:actions>
                <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
