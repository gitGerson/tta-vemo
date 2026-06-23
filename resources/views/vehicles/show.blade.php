@extends('layouts.sneat')

@section('title')
    Detail Kendaraan
@endsection

@php
    $statusClass = $vehicle->status === 'ACTIVE' ? 'bg-label-success' : 'bg-label-secondary';
    $vehicleTypeLabel = $vehicle->vehicleType?->name ?? $vehicle->type;
    $bpkb = $vehicle->bpkbImageUrls();
    $stnk = $vehicle->stnkImageUrls();
    $brandLogo = $vehicle->vehicleBrand?->logoUrl();
    $brandName = $vehicle->vehicleBrand?->name;
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Header breadcrumb --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Kendaraan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $vehicle->plat_new }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission('master.vehicles.edit'))
                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('vehicles.label', $vehicle) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-barcode me-1"></i> Label
                </a>
                <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Left summary column --}}
            <div class="col-lg-4 col-md-5">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <span class="badge bg-label-primary rounded p-4 mb-3 d-inline-flex align-items-center justify-content-center">
                            @if($brandLogo)
                                <img src="{{ $brandLogo }}" alt="{{ $brandName }}" style="height: 3rem; width: auto;" onerror="this.outerHTML='<i class=&quot;bx bx-car&quot; style=&quot;font-size: 3rem;&quot;></i>'">
                            @else
                                <i class="bx bx-car" style="font-size: 3rem;"></i>
                            @endif
                        </span>
                        <h4 class="mb-1">{{ $vehicle->plat_new }}</h4>
                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                            <span class="badge {{ $statusClass }}">{{ $vehicle->status === 'ACTIVE' ? 'Aktif' : 'Pasif' }}</span>
                            @if($vehicle->mtc === 'YES')
                                <span class="badge bg-label-warning">Dalam Pemeliharaan</span>
                            @endif
                            <span class="badge {{ $vehicle->isprivate ? 'bg-label-info' : 'bg-label-secondary' }}">
                                {{ $vehicle->isprivate ? 'Kendaraan Pribadi' : 'Kendaraan Umum' }}
                            </span>
                            @php $photoComplete = $photoThisMonth?->isComplete() ?? false; $photoSides = $photoThisMonth?->completedCount() ?? 0; @endphp
                            <span class="badge {{ $photoComplete ? 'bg-label-success' : 'bg-label-warning' }}">
                                <i class="bx bx-camera me-1"></i>{{ $photoComplete ? 'Foto bulan ini lengkap' : 'Foto bulan ini belum lengkap (' . $photoSides . '/4)' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-around border-top pt-3">
                            <div>
                                <h5 class="mb-0">{{ $vehicle->mileage ?? '0' }}</h5>
                                <small class="text-muted">Kilometer</small>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $brandName ?? '-' }}</h5>
                                <small class="text-muted">Merek</small>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $vehicle->fuelType?->name ?? $vehicle->fuel_type }}</h5>
                                <small class="text-muted">Bahan Bakar</small>
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
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Dibuat Pada</small><span class="fw-medium">{{ $vehicle->created_at?->format('d M Y H:i') ?? '-' }}</span></div>
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Diperbarui Pada</small><span class="fw-medium">{{ $vehicle->updated_at?->format('d M Y H:i') ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>

                @if($vehicle->desc)
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-2"><i class="bx bx-note me-1 text-primary"></i> Catatan</h6>
                            <p class="mb-0 text-muted">{{ $vehicle->desc }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right detail column --}}
            <div class="col-lg-8 col-md-7">
                {{-- Quick info chips --}}
                <div class="row g-3 mb-4">
                    @foreach ([
                        ['label' => 'Plat Nomor', 'value' => $vehicle->plat_new, 'color' => 'primary'],
                        ['label' => 'Tipe', 'value' => $vehicleTypeLabel, 'color' => 'info'],
                        ['label' => 'Perusahaan', 'value' => $vehicle->company?->name ?? '-', 'color' => 'success'],
                        ['label' => 'Tahun Masuk', 'value' => $vehicle->entry_year, 'color' => 'warning'],
                    ] as $chip)
                        <div class="col-sm-6 col-xl">
                            <div class="card h-100 border-start border-3" style="border-left-color: rgba(var(--bs-{{ $chip['color'] }}-rgb), 0.4) !important;">
                                <div class="card-body p-3">
                                    <small class="text-muted text-uppercase d-block">{{ $chip['label'] }}</small>
                                    <span class="fw-semibold">{{ $chip['value'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Identitas Mesin --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-label-info rounded p-2 me-2"><i class="bx bx-barcode"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Identitas Mesin</h6>
                                <small class="text-muted">Nomor mesin dan rangka kendaraan.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Nomor Mesin</small><span class="fw-medium">{{ $vehicle->machine_number ?: '-' }}</span></div>
                            <div class="col-md-6"><small class="text-muted text-uppercase d-block">Nomor Rangka (VIN)</small><span class="fw-medium">{{ $vehicle->vin_number ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Dokumen --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-label-primary rounded p-2 me-2"><i class="bx bx-file"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Dokumen</h6>
                                <small class="text-muted">Foto BPKB dan STNK kendaraan.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted text-uppercase d-block mb-1">Foto BPKB</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($bpkb as $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="BPKB" class="rounded border" style="height: 56px; width: auto;"></a>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted text-uppercase d-block mb-1">Foto STNK</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($stnk as $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="STNK" class="rounded border" style="height: 56px; width: auto;"></a>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Keuangan --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-label-warning rounded p-2 me-2"><i class="bx bx-money"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Keuangan</h6>
                                <small class="text-muted">Harga dan tanggal pembelian / penjualan.</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3"><small class="text-muted text-uppercase d-block">Harga Beli</small><span class="fw-medium">{{ $vehicle->purchase_price !== null ? 'Rp ' . number_format((float) $vehicle->purchase_price, 0, ',', '.') : '-' }}</span></div>
                            <div class="col-md-3"><small class="text-muted text-uppercase d-block">Harga Jual</small><span class="fw-medium">{{ $vehicle->selling_price !== null ? 'Rp ' . number_format((float) $vehicle->selling_price, 0, ',', '.') : '-' }}</span></div>
                            <div class="col-md-3"><small class="text-muted text-uppercase d-block">Tanggal Beli</small><span class="fw-medium">{{ $vehicle->purchase_date?->format('d M Y') ?? '-' }}</span></div>
                            <div class="col-md-3"><small class="text-muted text-uppercase d-block">Tanggal Jual</small><span class="fw-medium">{{ $vehicle->sale_date?->format('d M Y') ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPermission('master.vehicle_insurances.view'))
            <div class="mb-4">
                <x-table :table="$vehicleInsuranceTable" engine="tabulator" layout="fitDataStretch" title="Asuransi">
                    <x-slot:actions>
                        @if(auth()->user()->hasPermission('master.vehicle_insurances.create'))
                            <a href="{{ route('vehicle-insurances.create', ['vehicle_id' => $vehicle->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-plus me-1"></i> Tambah Asuransi
                            </a>
                        @endif
                    </x-slot:actions>
                </x-table>
            </div>
        @endif

        @if(auth()->user()->hasPermission('operational.vehicle_photos.view'))
            <div class="mb-4">
                <x-table :table="$photoTable" engine="tabulator" layout="fitDataStretch" title="Foto Kendaraan">
                    <x-slot:actions>
                        @if(auth()->user()->hasPermission('operational.vehicle_photos.create'))
                            <a href="{{ route('vehicle-photos.create', ['vehicle_id' => $vehicle->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-plus me-1"></i> Upload Foto
                            </a>
                        @endif
                    </x-slot:actions>
                </x-table>
            </div>
        @endif

        <x-table :table="$snapshotTable" engine="tabulator" layout="fitDataStretch" title="Riwayat Perubahan" />
    </div>
@endsection
