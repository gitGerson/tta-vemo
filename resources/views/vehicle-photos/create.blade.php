@extends('layouts.sneat')

@section('title')
    Upload Foto Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ $vehicle !== null ? 'Upload Foto: ' . $vehicle->plat_new : 'Upload Foto Kendaraan' }}</h4>
            <a href="{{ $vehicle !== null ? route('vehicles.show', $vehicle) : route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('vehicle-photos.partials.form', [
                    'vehicle' => $vehicle,
                    'vehicles' => $vehicles,
                    'selectedVehicleId' => $selectedVehicleId,
                    'periodsByVehicle' => $periodsByVehicle,
                    'documentedPeriods' => $documentedPeriods,
                    'action' => route('vehicle-photos.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Simpan',
                ])
            </div>
        </div>
    </div>
@endsection
