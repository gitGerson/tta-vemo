@extends('layouts.sneat')

@section('title')
    {{ isset($maintenance) ? 'Detail Pemeliharaan' : 'Input Data Pemeliharaan' }}
@endsection

@php
    $maintenance = $maintenance ?? null;
    $isReadOnly = $maintenance?->isReadOnly() ?? false;
    $canCreate = auth()->user()?->hasPermission('operational.maintenances.create');
    $canEdit = auth()->user()?->hasPermission('operational.maintenances.edit');
    $canApprove = auth()->user()?->hasPermission('operational.maintenances.approve');
    $canEditOrCreate = (! $maintenance && $canCreate) || ($maintenance && $canEdit);
    $routineValue = old('isServiceRoutine', $maintenance ? ($maintenance->isServiceRoutine ? '1' : '0') : '1');
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('maintenances.index') }}">Data Pemeliharaan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $maintenance ? 'Detail · ' . $maintenance->code_mt : 'Input Data Baru' }}
                    </li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
                @if ($maintenance && in_array($maintenance->status, ['DRAFT', 'COMPLETED'], true) && ! $maintenance->is_confirmed && $canApprove)
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                        data-bs-target="#confirmCancelModal" data-id="{{ $maintenance->getRouteKey() }}">
                        <i class="bx bx-x me-1"></i> Batalkan
                    </button>
                @endif
                @if (! $isReadOnly && $canEditOrCreate)
                    <button type="submit" form="mainForm" class="btn btn-sm btn-success">
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                @endif
            </div>
        </div>

        {{-- Status Badge --}}
        @if ($maintenance)
            <div class="mb-3">
                @if ($maintenance->status === 'DRAFT')
                    <span class="badge bg-secondary fs-6">DRAFT</span>
                @elseif ($maintenance->status === 'COMPLETED')
                    @if ($maintenance->is_confirmed)
                        <span class="badge bg-info fs-6">DIKONFIRMASI</span>
                    @else
                        <span class="badge bg-success fs-6">SELESAI</span>
                    @endif
                @elseif ($maintenance->status === 'CANCELLED')
                    <span class="badge bg-danger fs-6">DIBATALKAN</span>
                @endif
            </div>
        @endif

        @if (! $isReadOnly)
            <div class="alert alert-light border alert-dismissible d-flex gap-2 mb-4" role="note" id="maintenanceInfoBanner">
                <i class="bx bx-info-circle text-primary fs-4 flex-shrink-0"></i>
                <div class="small pe-3">
                    <strong>Simpan Data Pemeliharaan</strong> butuh semua kolom wajib (<span class="text-danger">*</span>), minimal 1 bukti pembayaran, dan centang konfirmasi.
                    Belum lengkap? Pakai <strong>Simpan sebagai Draf</strong> untuk menyimpan sementara dan melanjutkannya nanti.
                </div>
                <button type="button" class="btn-close" aria-label="Tutup" id="maintenanceInfoBannerClose"></button>
            </div>
        @endif

        {{-- Main Form --}}
        <form id="mainForm" method="POST"
            action="{{ $maintenance ? route('maintenances.update', $maintenance) : route('maintenances.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($maintenance)
                @method('PUT')
            @endif

            <div class="row">
                {{-- LEFT COLUMN --}}
                <div class="col-lg-8">
                    {{-- Informasi Kendaraan --}}
                    <div class="card mb-4 info-kendaraan-card">
                        <div class="card-header card-header-compact">
                            <h6 class="mb-0 info-kendaraan-title"><i class="bx bx-car text-success me-2"></i>Informasi Kendaraan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="no_pol" class="form-label info-kendaraan-label">Plat Nomor <small class="text-danger">*</small></label>
                                    <select class="form-select form-select-sm select2" name="no_pol" id="no_pol" required {{ $isReadOnly ? 'disabled' : '' }}>
                                        <option value="" disabled selected>- Pilih Kendaraan -</option>
                                        @foreach ($vehicles as $row)
                                            <option {{ old('no_pol', $maintenance?->no_pol ?? request()->query('vehicle')) == $row->plat_new ? 'selected' : '' }}
                                                value="{{ $row->plat_new }}" data-mileage="{{ $row->mileage }}">
                                                {{ $row->plat_new }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="mileage" class="form-label info-kendaraan-label">Kilometer Terakhir <small class="text-danger">*</small></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" inputmode="numeric" id="mileage" placeholder="Cth. 50000"
                                            name="mileage" class="form-control validate-number"
                                            value="{{ old('mileage', $maintenance?->mileage) }}" readonly>
                                    </div>
                                    <small class="text-info" id="mileage-info"></small>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_in" class="form-label info-kendaraan-label">Tanggal Masuk <small class="text-danger">*</small></label>
                                    <input type="date" id="date_in" name="date_in" class="form-control form-control-sm"
                                        value="{{ old('date_in', $maintenance?->date_in?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                        required {{ $isReadOnly ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_out" class="form-label info-kendaraan-label">Tanggal Keluar <small class="text-danger">*</small></label>
                                    <input type="date" id="date_out" name="date_out" class="form-control form-control-sm"
                                        value="{{ old('date_out', $maintenance?->date_out?->format('Y-m-d')) }}"
                                        required {{ $isReadOnly ? 'readonly' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Servis --}}
                    <div class="card mb-4">
                        <div class="card-header card-header-compact">
                            <h6 class="mb-0"><i class="bx bx-wrench text-success me-2"></i>Detail Servis</h6>
                        </div>
                        <div class="card-body">
                            <div class="detail-servis-panel mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">Servis Rutin?</div>
                                        <div class="text-muted small">Apakah ini bagian dari jadwal Pemeliharaan rutin?</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="isServiceRoutineToggle"
                                            {{ $routineValue === '0' ? 'checked' : '' }} {{ $isReadOnly ? 'disabled' : '' }}>
                                        <input type="hidden" name="isServiceRoutine" id="isServiceRoutineValue" value="{{ $routineValue }}">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label for="desc" class="form-label detail-servis-label">Catatan</label>
                                <textarea name="desc" id="desc" rows="4" class="form-control detail-servis-textarea"
                                    placeholder="Jelaskan masalah yang dilaporkan atau pekerjaan yang diperlukan..."
                                    {{ $isReadOnly ? 'readonly' : '' }}>{{ old('desc', $maintenance?->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Perbaikan: shared x-table component in local (client-side) mode --}}
                    <div class="mb-4">
                        <x-table :table="$detailTable" engine="tabulator" title="Detail Perbaikan"
                            :show-search="false" :show-colvis="false" :show-export="false" />
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header card-header-compact d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bx bx-receipt text-info me-2"></i>Ringkasan & Bukti</h6>
                            @if (! $isReadOnly)
                                <button type="button" class="btn btn-sm btn-outline-info" id="maintenanceHintsToggle" title="Tampilkan petunjuk pengisian">
                                    <i class="bx bx-info-circle me-1"></i> Petunjuk
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="text-muted">Total Estimasi Biaya</span>
                                <span class="fs-4 fw-bold text-success">Rp <span id="totalDisplay">{{ $maintenance ? number_format($maintenance->total_amount ?? 0, 0, ',', '.') : '0' }}</span></span>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">UNGGAH BUKTI PEMBAYARAN
                                    @if (! $maintenance)
                                        <small class="text-danger">* (Wajib)</small>
                                    @endif
                                </label>

                                @if ($maintenance && isset($notes))
                                    @foreach ($notes as $note)
                                        @php
                                            $ext = strtolower(pathinfo($note->image, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg']);
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2">
                                            @if ($isImage)
                                                <a href="javascript:void(0)" class="text-decoration-none text-truncate preview-existing-image" data-url="{{ $note->imageUrl() }}" style="max-width: 200px; cursor: pointer;">
                                                    <i class="bx bx-image me-1"></i> {{ basename($note->image) }}
                                                </a>
                                            @else
                                                <a href="{{ $note->imageUrl() }}" target="_blank" class="text-decoration-none text-truncate" style="max-width: 200px;">
                                                    <i class="bx bx-file me-1"></i> {{ basename($note->image) }}
                                                </a>
                                            @endif
                                            @if (! $isReadOnly)
                                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-note-id="{{ $note->id }}"
                                                    data-href="{{ route('maintenances.note.destroy', $note->id) }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                @if (! $isReadOnly)
                                    <input type="file"
                                        class="filepond"
                                        name="fields[]"
                                        multiple
                                        data-allow-reorder="true"
                                        data-max-file-size="3MB"
                                        accept="image/png, image/jpeg, image/gif, image/svg+xml, application/pdf">
                                @endif
                            </div>

                            @if (! $isReadOnly)
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="agreeCheckbox">
                                    <label class="form-check-label small" for="agreeCheckbox">
                                        Data dan bukti yang dimasukkan sudah benar dan valid.
                                    </label>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" id="submitButton" class="btn btn-success btn-lg" disabled>
                                        <i class="bx bx-check me-1"></i> Simpan Data Pemeliharaan
                                    </button>
                                    <small id="submitHint" class="text-muted text-center d-none">
                                        <i class="bx bx-lock-alt me-1"></i><span id="submitHintText"></span>
                                    </small>

                                    <div class="d-flex align-items-center text-muted small my-1">
                                        <hr class="flex-grow-1 my-0">
                                        <span class="px-2">atau</span>
                                        <hr class="flex-grow-1 my-0">
                                    </div>

                                    <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-secondary" formnovalidate>
                                        <i class="bx bx-save me-1"></i> Simpan sebagai Draf
                                    </button>
                                    <small class="text-muted text-center">Tersimpan sebagai draf — bisa dilengkapi &amp; diselesaikan nanti.</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Confirm card --}}
                    @if ($maintenance && $maintenance->status === 'COMPLETED')
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success-subtle card-header-compact mb-3">
                                <h6 class="mb-0"><i class="bx bx-check-shield me-2"></i>Konfirmasi Pemeliharaan</h6>
                            </div>
                            <div class="card-body">
                                @if (! $maintenance->is_confirmed && $canApprove)
                                    <div class="mb-3">
                                        <label for="confirmation_notes" class="form-label detail-servis-label">Catatan Konfirmasi</label>
                                        <textarea name="confirmation_notes" id="confirmation_notes" rows="3"
                                            class="form-control detail-servis-textarea" form="confirmForm"
                                            placeholder="Tambahkan catatan konfirmasi (opsional)..."
                                            {{ $maintenance->confirmation_notes ? 'readonly' : '' }}>{{ $maintenance->confirmation_notes }}</textarea>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" form="confirmForm" class="btn btn-success">
                                            <i class="bx bx-check me-1"></i> Konfirmasi
                                        </button>
                                    </div>
                                @elseif ($maintenance->is_confirmed)
                                    <div class="alert alert-success d-flex align-items-center mb-0">
                                        <i class="bx bx-check-circle fs-4 me-2"></i>
                                        <div>
                                            <strong>Dikonfirmasi</strong> oleh {{ $maintenance->confirmer?->name ?? 'N/A' }}
                                            pada {{ $maintenance->confirmed_at?->format('d M Y H:i') ?? '-' }}
                                            @if ($maintenance->confirmation_notes)
                                                <div class="text-muted small mt-2">Catatan: {{ $maintenance->confirmation_notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if ($maintenance)
        <form method="POST" action="{{ route('maintenances.confirm', $maintenance) }}" id="confirmForm" class="d-none">
            @csrf
        </form>
    @endif

    <div id="maintenancesPageConfig" class="d-none"
        data-uploads-process="{{ route('uploads.process') }}"
        data-uploads-revert="{{ route('uploads.revert') }}"
        data-uploads-load="{{ route('uploads.load') }}?source="
        data-require-upload="1"
        data-existing-upload-count="{{ isset($notes) ? $notes->count() : 0 }}"></div>

    @include('maintenances._modals', ['maintenance' => $maintenance])
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    @include('maintenances._styles')
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    @include('maintenances._scripts')
@endpush
