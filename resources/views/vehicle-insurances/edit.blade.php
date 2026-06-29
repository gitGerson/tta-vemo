@extends('layouts.sneat')

@section('title')
    Ubah Asuransi Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Ubah Asuransi: {{ $insurance->code }}</h4>
            <a href="{{ route('vehicle-insurances.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('vehicle-insurances.partials.form', [
                    'insurance' => $insurance,
                    'action' => route('vehicle-insurances.update', $insurance),
                    'method' => 'PUT',
                    'submitLabel' => 'Perbarui',
                ])
            </div>
        </div>
    </div>
@endsection
