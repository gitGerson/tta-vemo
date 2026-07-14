@extends('layouts.sneat')

@section('title')
    Detail Data Perjalanan
@endsection

@php
    $idrLabel = 'Rp ' . number_format((float) $record->idr, 0, ',', '.');
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Header breadcrumb --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('records.index') }}">Data Perjalanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $record->no_pol }} &middot; {{ $record->date?->translatedFormat('d M Y') }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission('operational.records.edit'))
                    <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('records.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <span class="badge bg-label-primary rounded p-2 me-2"><i class="bx bx-navigation"></i></span>
                    <div>
                        <h6 class="mb-0 fw-semibold">{{ $record->no_pol }} &rarr; {{ $record->destination }}</h6>
                        <small class="text-muted">{{ $record->company?->name ?? '-' }} &middot; {{ $record->date?->translatedFormat('d F Y') }}</small>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><small class="text-muted text-uppercase d-block">Supir 1</small><span class="fw-medium">{{ $record->driver_1 ?: '-' }}</span></div>
                    <div class="col-md-6"><small class="text-muted text-uppercase d-block">Supir 2</small><span class="fw-medium">{{ $record->driver_2 ?: 'NONE' }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">KM Awal</small><span class="fw-medium">{{ $record->start_km }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">KM Akhir</small><span class="fw-medium">{{ $record->finish_km }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Total</small><span class="fw-medium">{{ $record->totals }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Skala (KM/L)</small><span class="fw-medium">{{ $record->scale }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">BBM / Liter</small><span class="fw-medium">{{ $record->bbm }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Jenis BBM</small><span class="fw-medium">{{ $record->fuelType?->name ?? '-' }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">IDR</small><span class="fw-medium">{{ $idrLabel }}</span></div>
                </div>
            </div>
        </div>

        <x-table :table="$snapshotTable" engine="tabulator" layout="fitDataStretch" title="Riwayat Perubahan" />
    </div>
@endsection
