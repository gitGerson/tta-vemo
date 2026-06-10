@extends('layouts.sneat')

@section('title')
    Detail Foto Kendaraan
@endsection

@php
    $backUrl = route('vehicle-photos.create', ['vehicle_id' => $vehicle?->id]);
    $periodLabel = $photo->period?->translatedFormat('F Y') ?? '-';
    $sides = \App\Models\VehiclePhoto::SIDES;
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">{{ $vehicle?->plat_new ?? 'Kendaraan' }} — {{ $periodLabel }}</h4>
                <span class="badge {{ $photo->isComplete() ? 'bg-label-success' : 'bg-label-warning' }}">
                    {{ $photo->completedCount() }}/{{ count($sides) }} {{ $photo->isComplete() ? 'Lengkap' : 'Belum lengkap' }}
                </span>
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission('operational.vehicle_photos.edit'))
                    <a href="{{ route('vehicle-photos.edit', $photo) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Photo gallery --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bx bx-camera me-1 text-primary"></i> Foto Bulanan</h6>
                <div class="row g-3">
                    @foreach ($sides as $side => $label)
                        @php $url = $photo->sideUrl($side); @endphp
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded h-100 overflow-hidden">
                                @if ($url)
                                    <button type="button" class="btn p-0 border-0 w-100 d-block" data-bs-toggle="modal"
                                        data-bs-target="#photoLightbox" data-photo-url="{{ $url }}" data-photo-label="{{ $label }}">
                                        <img src="{{ $url }}" alt="Foto {{ $label }}" class="w-100"
                                            style="aspect-ratio: 4 / 3; object-fit: cover;">
                                    </button>
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted bg-light"
                                        style="aspect-ratio: 4 / 3;">
                                        <i class="bx bx-image-alt mb-1" style="font-size: 2rem;"></i>
                                        <small>Belum ada foto</small>
                                    </div>
                                @endif
                                <div class="px-2 py-2 border-top d-flex align-items-center justify-content-between">
                                    <span class="fw-medium small">{{ $label }}</span>
                                    @if ($url)
                                        <i class="bx bx-check-circle text-success"></i>
                                    @else
                                        <i class="bx bx-x-circle text-warning"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted text-uppercase d-block">Kendaraan</small><span class="fw-medium">{{ $vehicle?->plat_new ?? '-' }}{{ $vehicle?->type ? " ({$vehicle->type})" : '' }}</span></div>
                    <div class="col-md-4"><small class="text-muted text-uppercase d-block">Bulan</small><span class="fw-medium">{{ $periodLabel }}</span></div>
                    <div class="col-md-4"><small class="text-muted text-uppercase d-block">Diunggah</small><span class="fw-medium">{{ $photo->created_at?->format('d M Y H:i') ?? '-' }}</span></div>
                    <div class="col-12"><small class="text-muted text-uppercase d-block">Keterangan</small><span class="fw-medium">{{ $photo->notes ?: '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lightbox --}}
    <div class="modal fade" id="photoLightbox" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoLightboxLabel">Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" alt="" id="photoLightboxImage" class="w-100" style="object-fit: contain; max-height: 75vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('photoLightbox');
            if (!modal) return;

            modal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;
                modal.querySelector('#photoLightboxImage').src = trigger.getAttribute('data-photo-url') || '';
                modal.querySelector('#photoLightboxLabel').textContent = 'Foto ' + (trigger.getAttribute('data-photo-label') || '');
            });
        });
    </script>
@endpush
