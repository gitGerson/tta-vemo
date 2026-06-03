@extends('layouts.sneat')

@section('title')
    Trash Tujuan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-table :table="$table" engine="tabulator" title="Trash Tujuan">
            <x-slot:actions>
                <a href="{{ route('destinations.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
