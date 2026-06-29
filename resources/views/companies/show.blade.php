@extends('layouts.sneat')

@section('title')
    Detail Perusahaan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Header breadcrumb --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Perusahaan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $company->name }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ route('companies.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Left summary column --}}
            <div class="col-lg-4 col-md-5">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        @if($company->imageUrl())
                            <img src="{{ $company->imageUrl() }}" alt="Logo {{ $company->name }}" class="rounded mb-3" style="height: 96px; width: 96px; object-fit: contain;">
                        @else
                            <span class="badge bg-label-primary rounded p-4 mb-3"><i class="bx bx-buildings" style="font-size: 3rem;"></i></span>
                        @endif
                        <h4 class="mb-1">{{ $company->name }}</h4>
                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                            <span class="badge bg-label-primary">{{ $company->code }}</span>
                            <span class="badge bg-label-secondary">Perusahaan</span>
                        </div>
                        <div class="d-flex justify-content-around border-top pt-3">
                            <div>
                                <h5 class="mb-0">{{ $company->vehicles_count }}</h5>
                                <small class="text-muted">Kendaraan</small>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $company->drivers_count }}</h5>
                                <small class="text-muted">Driver</small>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $company->users_count }}</h5>
                                <small class="text-muted">User</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($company->notes)
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-2"><i class="bx bx-note me-1 text-primary"></i> Keterangan</h6>
                            <p class="mb-0 text-muted">{{ $company->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right detail column --}}
            <div class="col-lg-8 col-md-7">
                {{-- Quick info chips --}}
                <div class="row g-3 mb-4">
                    @foreach ([
                        ['label' => 'Kode', 'value' => $company->code, 'color' => 'primary'],
                        ['label' => 'Nama', 'value' => $company->name, 'color' => 'info'],
                        ['label' => 'Email', 'value' => $company->email ?: '-', 'color' => 'success'],
                        ['label' => 'Telepon', 'value' => $company->phone ?: '-', 'color' => 'warning'],
                    ] as $chip)
                        <div class="col-sm-6 col-xl-3">
                            <div class="card h-100 border-start border-3" style="border-left-color: rgba(var(--bs-{{ $chip['color'] }}-rgb), 0.4) !important;">
                                <div class="card-body p-3">
                                    <small class="text-muted text-uppercase d-block">{{ $chip['label'] }}</small>
                                    <span class="fw-semibold">{{ $chip['value'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Kontak --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-label-info rounded p-2 me-2"><i class="bx bx-envelope"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Kontak</h6>
                                <small class="text-muted">Email dan nomor telepon perusahaan.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Email</small><span class="fw-medium">{{ $company->email ?: '-' }}</span></div>
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Telepon</small><span class="fw-medium">{{ $company->phone ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Media --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-label-warning rounded p-2 me-2"><i class="bx bx-image"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Media</h6>
                                <small class="text-muted">Referensi gambar atau logo perusahaan.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <small class="text-muted text-uppercase d-block mb-2">Gambar</small>
                                @if($company->imageUrl())
                                    <img src="{{ $company->imageUrl() }}" alt="Logo {{ $company->name }}" class="img-fluid rounded border" style="max-height: 160px;">
                                @else
                                    <span class="fw-medium">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Audit --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-label-secondary rounded p-2 me-2"><i class="bx bx-history"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Audit</h6>
                                <small class="text-muted">Riwayat pembuatan dan pembaruan.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Dibuat Pada</small><span class="fw-medium">{{ $company->created_at?->format('d M Y H:i') ?? '-' }}</span></div>
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Diperbarui Pada</small><span class="fw-medium">{{ $company->updated_at?->format('d M Y H:i') ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-0">Data Terkait</h5>
                <small class="text-muted">Record yang terhubung dengan perusahaan ini.</small>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" id="company-drivers-tab" data-bs-toggle="tab" data-bs-target="#company-drivers-sheet" role="tab" aria-controls="company-drivers-sheet" aria-selected="true">
                    <i class="bx bx-id-card me-1"></i> Driver
                    <span class="badge bg-label-primary ms-1">{{ $company->drivers_count }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" id="company-vehicles-tab" data-bs-toggle="tab" data-bs-target="#company-vehicles-sheet" role="tab" aria-controls="company-vehicles-sheet" aria-selected="false">
                    <i class="bx bx-car me-1"></i> Kendaraan
                    <span class="badge bg-label-primary ms-1">{{ $company->vehicles_count }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" id="company-users-tab" data-bs-toggle="tab" data-bs-target="#company-users-sheet" role="tab" aria-controls="company-users-sheet" aria-selected="false">
                    <i class="bx bx-user me-1"></i> User
                    <span class="badge bg-label-primary ms-1">{{ $company->users_count }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content p-0">
            <div class="tab-pane fade show active" id="company-drivers-sheet" role="tabpanel" aria-labelledby="company-drivers-tab" tabindex="0">
                <x-table :table="$driverTable" engine="tabulator" layout="fitDataStretch" title="Driver" />
            </div>
            <div class="tab-pane fade" id="company-vehicles-sheet" role="tabpanel" aria-labelledby="company-vehicles-tab" tabindex="0">
                <x-table :table="$vehicleTable" engine="tabulator" layout="fitDataStretch" title="Kendaraan" />
            </div>
            <div class="tab-pane fade" id="company-users-sheet" role="tabpanel" aria-labelledby="company-users-tab" tabindex="0">
                <x-table :table="$userTable" engine="tabulator" layout="fitDataStretch" title="User" />
            </div>
        </div>
    </div>
@endsection
