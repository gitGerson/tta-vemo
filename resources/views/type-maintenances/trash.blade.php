@extends('layouts.sneat')

@section('title')
    Sampah Tipe Pemeliharaan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-table :table="$table" engine="tabulator" title="Sampah Tipe Pemeliharaan">
            <x-slot:actions>
                <a href="{{ route('type-maintenances.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
