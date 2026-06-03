@extends('layouts.sneat')

@section('title')
    Detail Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Detail Kendaraan</h4>
            <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ $vehicle->plat_new }} — {{ $vehicle->type }}</h5>
            </div>
            <div class="card-body">
                @include('vehicles.partials.show')
            </div>
        </div>

        <x-table :table="$snapshotTable" engine="tabulator" title="Riwayat Perubahan" />
    </div>
@endsection
