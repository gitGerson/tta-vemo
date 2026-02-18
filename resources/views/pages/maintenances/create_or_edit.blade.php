@extends('layouts.sneat')

@section('content')
    @php
        $isCompleted = isset($maintenances) && $maintenances->status == 'COMPLETED';
        $isCancelled = isset($maintenances) && $maintenances->status == 'CANCELLED';
        $canCreateMaintenance = auth()->user()?->hasPermission('maintenances.create');
        $canEditMaintenance = auth()->user()?->hasPermission('maintenances.edit');
        $canApproveMaintenance = auth()->user()?->hasPermission('maintenances.approve');
        $canEditOrCreateMaintenance = (!isset($maintenances) && $canCreateMaintenance) || (isset($maintenances) && $canEditMaintenance);
        $isReadOnly = $isCompleted || $isCancelled;
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Alert Messages --}}
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
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ isset($maintenances) ? 'Detail Pemeliharaan' : 'Input Data Pemeliharaan' }}</h4>
                <p class="text-muted mb-0">{{ isset($maintenances) ? 'Kode: ' . $maintenances->code_mt : 'Rekam data servis kendaraan baru dan kelola item perbaikan.' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('maintenances.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
                @if (isset($maintenances) && in_array($maintenances->status, ['DRAFT', 'COMPLETED'], true) && ! $maintenances->is_confirmed)
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                        data-bs-target="#confirmCancelModal" data-id="{{ $maintenances->id }}">
                        <i class="bx bx-x me-1"></i> Batalkan
                    </button>
                @endif
                @if (!$isReadOnly && $canEditOrCreateMaintenance)
                    <button type="submit" form="mainForm" class="btn btn-success">
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                @endif
            </div>
        </div>

        {{-- Status Badge for Existing Records --}}
        @isset($maintenances)
            <div class="mb-3">
                @if ($maintenances->status == 'DRAFT')
                    <span class="badge bg-secondary fs-6">📋 DRAFT</span>
                @elseif($maintenances->status == 'COMPLETED')
                    <span class="badge bg-success fs-6">✅ SELESAI</span>
                    @if ($maintenances->is_confirmed)
                        <span class="badge bg-info fs-6">✓ DIKONFIRMASI</span>
                    @endif
                @elseif($maintenances->status == 'CANCELLED')
                    <span class="badge bg-danger fs-6">❌ DIBATALKAN</span>
                @endif
            </div>
        @endisset

        {{-- Main Form Layout --}}
        <form id="mainForm" method="POST"
            action="{{ isset($maintenances) ? route('maintenances.update', $maintenances->id) : route('maintenances.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if (isset($maintenances))
                @method('PUT')
            @endif

            <div class="row">
                {{-- LEFT COLUMN --}}
                <div class="col-lg-8">
                        {{-- Card 1: Informasi Kendaraan --}}
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
                                            @foreach ($vehicle as $row)
                                                <option {{ old('no_pol', isset($maintenances) ? $maintenances->no_pol : request()->query('vehicle')) == $row->plat_new ? 'selected' : '' }}
                                                    value="{{ $row->plat_new }}" data-mileage="{{ $row->mileage }}">
                                                    {{ $row->plat_new }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mileage" class="form-label info-kendaraan-label">Km Sekarang <small class="text-danger">*</small></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" inputmode="numeric" id="mileage" placeholder="Cth. 50000"
                                                name="mileage" class="form-control validate-number"
                                                value="{{ old('mileage', isset($maintenances) ? $maintenances->mileage : '') }}"
                                                {{ $isReadOnly ? 'readonly' : '' }}>
                                            <span class="input-group-text">km</span>
                                        </div>
                                        <small class="text-info" id="mileage-info"></small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_in" class="form-label info-kendaraan-label">Tanggal Masuk <small class="text-danger">*</small></label>
                                        <input type="date" id="date_in" name="date_in" class="form-control form-control-sm"
                                            value="{{ old('date_in', isset($maintenances) ? $maintenances->date_in : Carbon\Carbon::now()->format('Y-m-d')) }}"
                                            required {{ $isReadOnly ? 'readonly' : '' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_out" class="form-label info-kendaraan-label">Tanggal Keluar <small class="text-danger">*</small></label>
                                        <input type="date" id="date_out" name="date_out" class="form-control form-control-sm"
                                            value="{{ old('date_out', isset($maintenances) ? $maintenances->date_out : '') }}"
                                            required {{ $isReadOnly ? 'readonly' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Detail Servis --}}
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
                                                {{ old('isServiceRoutine', isset($maintenances) ? $maintenances->isServiceRoutine : '') == '0' ? 'checked' : '' }}
                                                {{ $isReadOnly ? 'disabled' : '' }}>
                                            <input type="hidden" name="isServiceRoutine" id="isServiceRoutineValue"
                                                value="{{ old('isServiceRoutine', isset($maintenances) ? $maintenances->isServiceRoutine : '1') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label for="desc" class="form-label detail-servis-label">Catatan</label>
                                    <textarea name="desc" id="desc" rows="4" class="form-control detail-servis-textarea"
                                        placeholder="Jelaskan masalah yang dilaporkan atau pekerjaan yang diperlukan..."
                                        {{ $isReadOnly ? 'readonly' : '' }}>{{ old('desc', isset($maintenances) ? $maintenances->description : '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Detail Perbaikan (Repeater) --}}
                        <div class="card mb-4">
                            <div class="card-header card-header-compact d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h6 class="mb-0"><i class="bx bx-list-ul text-warning me-2"></i>Detail Perbaikan</h6>
                                @if (!$isReadOnly)
                                    <div class="d-flex flex-wrap gap-2">
                                        <!-- <button type="button" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-scan me-1"></i> Scan Nota (OCR)
                                        </button> -->
                                        <button type="button" class="btn btn-sm btn-outline-success" id="addRepeaterItem">
                                            <i class="bx bx-plus me-1"></i> Tambah Item
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <!-- @if (!$isReadOnly)
                                    <div class="detail-perbaikan-ocr mb-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bx bx-file text-primary mt-1"></i>
                                            <div>
                                                <div class="fw-semibold">Auto-fill dari Nota</div>
                                                <div class="text-muted small">Tarik foto nota fisik ke sini atau klik tombol scan untuk mengisi tabel secara otomatis.</div>
                                            </div>
                                        </div>
                                        <div class="detail-perbaikan-dropzone mt-2">
                                            <i class="bx bx-image-alt me-1"></i> Zona Scan Nota
                                        </div>
                                    </div>
                                @endif -->
                                <div id="repeaterContainer">
                                    <div class="detail-perbaikan-table">
                                        <div class="detail-perbaikan-header">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4 text-center">Nama Barang/Jasa</div>
                                                <div class="col-md-3 text-center">Harga Satuan</div>
                                                <div class="col-md-1 text-center">Jumlah</div>
                                                <div class="col-md-3 text-center">Total</div>
                                                <div class="col-md-1 text-center">Aksi</div>
                                            </div>
                                        </div>
                                        <div id="repeaterItems">
                                            @if (isset($detail_maintenances) && $detail_maintenances->isNotEmpty())
                                                @foreach ($detail_maintenances as $detailIndex => $row)
                                                    <div class="repeater-item detail-perbaikan-item" data-existing="true"
                                                        data-subtotal="{{ $row->subtotal ?? ($row->price * $row->qty) }}">
                                                        <div class="row g-2 align-items-center">
                                                            <div class="col-md-4">
                                                                <select class="form-select form-select-sm select2 repeater-name w-100"
                                                                    name="items[{{ $detailIndex }}][maintenance_name]" data-width="100%" disabled>
                                                                    <option value="" disabled>- Pilih jenis -</option>
                                                                    @foreach ($type ?? [] as $typeRow)
                                                                        <option value="{{ $typeRow->name }}" {{ $typeRow->name === $row->maintenance_name ? 'selected' : '' }}>
                                                                            {{ $typeRow->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3 pe-0">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text">Rp</span>
                                                                    <input type="text" class="form-control form-control-sm repeater-price"
                                                                        value="{{ $row->price }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-1 pe-0 text-center">
                                                                <input type="number" class="form-control form-control-sm text-center repeater-qty"
                                                                    value="{{ $row->qty }}" readonly>
                                                            </div>
                                                            <div class="col-md-3 text-center pe-0">
                                                                <span class="fw-semibold text-success repeater-subtotal">Rp {{ number_format($row->subtotal ?? ($row->price * $row->qty), 0, ',', '.') }}</span>
                                                            </div>
                                                            <div class="col-md-1 text-end ps-0">
                                                                @if (!$isReadOnly)
                                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-existing-item"
                                                                        data-id="{{ $row->id }}" data-href="{{ route('detail_maintenances.destroy', $row->id) }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if (!$isReadOnly)
                                    {{-- Hidden Template --}}
                                    <template id="repeaterTemplate">
                                        <div class="repeater-item detail-perbaikan-item">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4">
                                                    <select class="form-select form-select-sm select2 repeater-name w-100" name="items[__INDEX__][maintenance_name]" data-width="100%" required>
                                                        <option value="" disabled selected>- Pilih jenis -</option>
                                                        @foreach ($type ?? [] as $row)
                                                            <option value="{{ $row->name }}">{{ $row->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3 pe-0">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" inputmode="numeric" class="form-control form-control-sm repeater-price" name="items[__INDEX__][price]" placeholder="0" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-1 pe-0 text-center">
                                                    <input type="number" class="form-control form-control-sm text-center repeater-qty" name="items[__INDEX__][qty]" value="1" min="1" required>
                                                </div>
                                                <div class="col-md-3 text-center pe-0">
                                                    <span class="fw-semibold text-success repeater-subtotal">Rp 0</span>
                                                </div>
                                                <div class="col-md-1 text-end ps-0">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-repeater-item">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                @else
                                    @if (!isset($detail_maintenances) || $detail_maintenances->isEmpty())
                                        <div class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle me-1"></i> Belum ada item perbaikan
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="col-lg-4">
                        {{-- Card: Ringkasan & Bukti --}}
                        <div class="card mb-4">
                            <div class="card-header card-header-compact">
                                <h6 class="mb-0"><i class="bx bx-receipt text-info me-2"></i>Ringkasan & Bukti</h6>
                            </div>
                            <div class="card-body">
                                {{-- Total Estimasi --}}
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="text-muted">Total Estimasi Biaya</span>
                                    <span class="fs-4 fw-bold text-success">Rp <span id="totalDisplay">{{ isset($maintenances) ? number_format($maintenances->total_amount ?? 0, 0, ',', '.') : '0' }}</span></span>
                                </div>

                                {{-- Hidden input for total --}}
                                <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_amount', isset($maintenances) ? $maintenances->total_amount : '') }}">

                                {{-- Upload Section --}}
                                <div class="mb-4">
                                    <label class="form-label">UNGGAH BUKTI PEMBAYARAN
                                        @if (!isset($maintenances))
                                            <small class="text-danger">* (Wajib)</small>
                                        @endif
                                    </label>

                                    {{-- Existing Files --}}
                                    @if (isset($maintenances) && isset($notes))
                                        @foreach ($notes as $note)
                                            @php
                                                $ext = strtolower(pathinfo($note->image, PATHINFO_EXTENSION));
                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg']);
                                            @endphp
                                            <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2">
                                                @if ($isImage)
                                                    <a href="javascript:void(0)" class="text-decoration-none text-truncate preview-existing-image" data-url="{{ $note->url }}" style="max-width: 200px; cursor: pointer;">
                                                        <i class="bx bx-image me-1"></i> {{ $note->image }}
                                                    </a>
                                                @else
                                                    <a href="{{ $note->url }}" target="_blank" class="text-decoration-none text-truncate" style="max-width: 200px;">
                                                        <i class="bx bx-file me-1"></i> {{ $note->image }}
                                                    </a>
                                                @endif
                                                @if (!$isReadOnly)
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-note-id="{{ $note->id }}">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- Upload Area with FilePond --}}
                                    @if (!$isReadOnly)
                                        <input type="file" 
                                            class="filepond" 
                                            name="fields[]" 
                                            multiple 
                                            data-allow-reorder="true"
                                            data-max-file-size="3MB"
                                            accept="image/png, image/jpeg, image/gif, image/svg+xml, application/pdf">
                                    @endif
                                </div>

                                {{-- Confirmation Checkbox --}}
                                @if (!$isReadOnly)
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="agreeCheckbox">
                                        <label class="form-check-label" for="agreeCheckbox">
                                            Saya menyatakan bahwa data yang dimasukkan benar dan bukti valid.
                                        </label>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="d-grid gap-2">
                                        <button type="submit" id="submitButton" class="btn btn-success btn-lg" disabled>
                                            <i class="bx bx-check me-1"></i> Simpan Data Pemeliharaan
                                        </button>
                                        <button type="submit" name="save_as_draft" value="1" class="btn btn-link text-muted">
                                            Simpan sebagai Draf
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                    {{-- Card: Confirm Actions (for completed records) --}}
                    @if (isset($maintenances) && $maintenances->status === 'COMPLETED')
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success-subtle card-header-compact mb-3">
                                <h6 class="mb-0"><i class="bx bx-check-shield me-2"></i>Konfirmasi Pemeliharaan</h6>
                            </div>
                            <div class="card-body">
                                @if (! $maintenances->is_confirmed && $canApproveMaintenance)
                                    <div class="mb-3">
                                        <label for="confirmation_notes" class="form-label detail-servis-label">Catatan Konfirmasi</label>
                                        <textarea name="confirmation_notes" id="confirmation_notes" rows="3"
                                            class="form-control detail-servis-textarea"
                                            form="confirmForm"
                                            placeholder="Tambahkan catatan konfirmasi (opsional)..."
                                            {{ $maintenances->confirmation_notes ? 'readonly' : '' }}>{{ $maintenances->confirmation_notes }}</textarea>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" form="confirmForm" class="btn btn-success">
                                            <i class="bx bx-check me-1"></i> Konfirmasi
                                        </button>
                                    </div>
                                @elseif ($maintenances->is_confirmed)
                                    <div class="alert alert-success d-flex align-items-center mb-0">
                                        <i class="bx bx-check-circle fs-4 me-2"></i>
                                        <div>
                                            <strong>Dikonfirmasi</strong> oleh {{ $maintenances->confirmer?->name ?? 'N/A' }}
                                            pada {{ $maintenances->confirmed_at?->format('d M Y H:i') ?? '-' }}
                                            @if ($maintenances->confirmation_notes)
                                                <div class="text-muted small mt-2">Catatan: {{ $maintenances->confirmation_notes }}</div>
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

    @if (isset($maintenances))
        <form method="POST" action="{{ route('maintenances.confirm', $maintenances->id) }}" id="confirmForm" class="d-none">
            @csrf
        </form>
    @endif

    <div id="maintenancesPageConfig" class="d-none"
        data-uploads-process="{{ route('uploads.process') }}"
        data-uploads-revert="{{ route('uploads.revert') }}"
        data-uploads-load="{{ route('uploads.load') }}?source="
        data-require-upload="1"
        data-existing-upload-count="{{ isset($notes) ? $notes->count() : 0 }}">
    </div>

    {{-- Modals --}}
    @include('pages.maintenances._modals')
@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- FilePond CSS --}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    @vite(['resources/css/page/maintenances.css'])
@endpush

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- FilePond JS --}}
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    @vite(['resources/js/page/maintenances.js'])
@endpush
