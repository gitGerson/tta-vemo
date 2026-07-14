@extends('layouts.sneat')

@section('title')
    Sampah Supir
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-table :table="$table" engine="tabulator" title="Sampah Supir">
            <x-slot:actions>
                <a href="{{ route('drivers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
