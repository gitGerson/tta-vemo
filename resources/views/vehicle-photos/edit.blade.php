@extends('layouts.sneat')

@section('title')
    Edit Foto Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Edit Foto: {{ $vehicle->plat_new }} — {{ $photo->period?->translatedFormat('F Y') }}</h4>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('vehicle-photos.partials.form', [
                    'photo' => $photo,
                    'vehicle' => $vehicle,
                    'documentedPeriods' => $documentedPeriods,
                    'action' => route('vehicle-photos.update', $photo),
                    'method' => 'PUT',
                    'submitLabel' => 'Perbarui',
                ])
            </div>
        </div>
    </div>
@endsection
