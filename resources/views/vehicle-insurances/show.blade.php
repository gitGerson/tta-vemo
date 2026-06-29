@extends('layouts.sneat')

@section('title')
    Detail Asuransi Kendaraan
@endsection

@php
    $coverageLabel = $insurance->coverage_value !== null ? 'Rp ' . number_format((float) $insurance->coverage_value, 0, ',', '.') : '-';
    $premiumLabel = $insurance->premium !== null ? 'Rp ' . number_format((float) $insurance->premium, 0, ',', '.') : '-';
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Header breadcrumb --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('vehicle-insurances.index') }}">Asuransi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $insurance->code }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission('master.vehicle_insurances.edit'))
                    <a href="{{ route('vehicle-insurances.edit', $insurance) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('vehicle-insurances.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <span class="badge bg-label-primary rounded p-2 me-2"><i class="bx bx-shield-quarter"></i></span>
                    <div>
                        <h6 class="mb-0 fw-semibold">{{ $insurance->insurer_name }}</h6>
                        <small class="text-muted">Polis {{ $insurance->policy_number }} &middot; {{ $insurance->insurance_type }}</small>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted text-uppercase d-block">Kode</small><span class="fw-medium">{{ $insurance->code }}</span></div>
                    <div class="col-md-4"><small class="text-muted text-uppercase d-block">Kendaraan</small><span class="fw-medium">{{ $insurance->vehicle?->plat_new ?? '-' }}</span></div>
                    <div class="col-md-4"><small class="text-muted text-uppercase d-block">Jenis Asuransi</small><span class="fw-medium">{{ $insurance->insurance_type }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Tanggal Mulai</small><span class="fw-medium">{{ $insurance->start_date?->format('d M Y') ?? '-' }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Tanggal Berakhir</small><span class="fw-medium">{{ $insurance->end_date?->format('d M Y') ?? '-' }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Nilai Pertanggungan</small><span class="fw-medium">{{ $coverageLabel }}</span></div>
                    <div class="col-md-3"><small class="text-muted text-uppercase d-block">Premi</small><span class="fw-medium">{{ $premiumLabel }}</span></div>
                    @if($insurance->notes)
                        <div class="col-12"><small class="text-muted text-uppercase d-block">Keterangan</small><span class="fw-medium">{{ $insurance->notes }}</span></div>
                    @endif
                    <div class="col-12">
                        <small class="text-muted text-uppercase d-block mb-1">Lampiran</small>
                        @php($attachments = $insurance->attachmentFiles())
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($attachments as $attachment)
                                <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-paperclip me-1"></i> {{ $attachment['name'] }}
                                </a>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-table :table="$snapshotTable" engine="tabulator" layout="fitDataStretch" title="Riwayat Perubahan" />
    </div>
@endsection
