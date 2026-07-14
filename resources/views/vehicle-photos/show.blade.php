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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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

        {{-- Comments & Activity --}}
        @php $palette = ['primary', 'success', 'danger', 'warning', 'info', 'secondary']; @endphp
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Comments &amp; Activity</h5>
                <span class="badge bg-label-secondary rounded-pill">{{ $photo->comments->count() }}</span>
            </div>
            <div class="card-body">
                {{-- Composer --}}
                <form method="POST" action="{{ route('vehicle-photos.comments.store', $photo) }}" class="mb-4">
                    @csrf
                    <x-form.rich-editor name="body" label="Tambah komentar" placeholder="Tulis komentar..." minHeight="140px" />
                    <div class="d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-send me-1"></i> Kirim
                        </button>
                    </div>
                </form>

                {{-- Thread --}}
                @forelse ($photo->comments as $comment)
                    @php
                        $name = $comment->user?->name ?? 'Pengguna';
                        $initials = collect(explode(' ', trim($name)))->filter()->take(2)->map(fn (string $w): string => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                        $color = $palette[($comment->user_id ?? 0) % count($palette)];
                        $isOwn = $comment->user_id === auth()->id();
                    @endphp
                    <div class="d-flex gap-3 mb-3">
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-label-{{ $color }}">{{ $initials ?: '?' }}</span>
                        </div>
                        <div class="flex-grow-1 rounded p-3 {{ $isOwn ? 'bg-label-primary' : 'bg-lighter border' }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold">{{ $name }}</span>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted">{{ $comment->created_at?->diffForHumans() }}</small>
                                    @if ($isOwn)
                                        <form method="POST" action="{{ route('vehicle-photos.comments.destroy', [$photo, $comment]) }}"
                                            onsubmit="return confirm('Hapus komentar ini?');" class="lh-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm p-0 border-0 text-danger" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="comment-body">{!! $comment->body !!}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada komentar.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .comment-body :last-child { margin-bottom: 0; }
            .comment-body p { margin-bottom: 0.4rem; }
            .comment-body img { max-width: 100%; height: auto; border-radius: 0.375rem; }
            .bg-lighter { background-color: var(--bs-gray-100, #f5f5f9); }
        </style>
    @endpush

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
