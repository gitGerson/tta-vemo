@extends('layouts.sneat')

@section('title')
    Edit Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Edit Kendaraan: {{ $vehicle->plat_new }}</h4>
            <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('vehicles.partials.form', [
                    'vehicle' => $vehicle,
                    'action' => route('vehicles.update', $vehicle),
                    'method' => 'PUT',
                    'submitLabel' => 'Perbarui',
                ])
            </div>
        </div>
    </div>
@endsection
