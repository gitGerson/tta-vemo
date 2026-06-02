@extends('layouts.sneat')

@section('title')
    Trash Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-datatable :table="$table" title="Trash Driver">
            <x-slot:actions>
                <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>
        </x-datatable>
    </div>
@endsection
