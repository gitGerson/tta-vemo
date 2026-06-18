@extends('layouts.sneat')

@section('title', 'Scan QR Kendaraan')

@push('styles')
<style>
    /* ── Scanner widget ──────────────────────────────────── */
    #qr-reader { width: 100%; border: none !important; }
    #qr-reader video { border-radius: .5rem; width: 100% !important; }
    #qr-reader__scan_region { border-radius: .5rem; overflow: hidden; }
    #qr-reader__status_span,
    #qr-reader img[alt="Info icon"] { display: none !important; }
    #qr-reader__dashboard_section_fsr span { font-size: .82rem; color: #697a8d; }
    #qr-reader select,
    #qr-reader button {
        font-size: .82rem; padding: .3rem .65rem;
        border-radius: .375rem; border: 1px solid #d9dee3; cursor: pointer;
    }
    #qr-reader button { border: none; }

    /* ── Camera placeholder ──────────────────────────────── */
    .camera-placeholder {
        aspect-ratio: 1 / 1;
        max-height: 280px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        background: #f4f5fb; border-radius: .75rem;
        border: 2px dashed #c5cbe4; color: #a1acb8; gap: 10px;
        transition: border-color .2s;
    }
    .camera-placeholder:hover { border-color: #696cff; }

    /* ── Badge ───────────────────────────────────────────── */
    .badge-type-vehicle { background: #e8e8ff; color: #696cff; }

    /* ── Animations ─────────────────────────────────────── */
    .result-appear { animation: slideUp .25s ease; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Scan divider ────────────────────────────────────── */
    .scan-divider {
        display: flex; align-items: center; gap: .75rem;
        color: #a1acb8; font-size: .75rem;
    }
    .scan-divider::before, .scan-divider::after {
        content: ''; flex: 1; height: 1px; background: #e7e7e7;
    }

    /* ── Scan button group ───────────────────────────────── */
    #btn-start, #btn-stop {
        font-size: .875rem; padding: .5rem 1rem;
    }

    /* ── Info label ──────────────────────────────────────── */
    .result-field-label {
        font-size: .62rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        color: #a1acb8; margin-bottom: .2rem;
    }
    .result-field-value {
        font-size: .82rem; font-weight: 600;
        color: #566a7f; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }

    /* ── Result item image ───────────────────────────────── */
    .result-item-img {
        width: 72px; height: 72px;
        border-radius: .5rem; object-fit: contain;
        border: 1px solid #e7e7e7; background: #fff; padding: 4px;
    }
    .result-item-avatar {
        width: 72px; height: 72px; border-radius: .5rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11 col-xl-9">

            {{-- Page header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('vehicles.index') }}" class="btn btn-icon btn-outline-secondary btn-sm rounded-circle flex-shrink-0"
                   title="Kembali">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <div>
                    <h5 class="mb-0 fw-bold">Scan QR Kendaraan</h5>
                    <div class="text-muted" style="font-size:.78rem;">Scan label kendaraan atau masukkan plat nomor secara manual</div>
                </div>
            </div>

            <div class="row g-4 align-items-start">

                {{-- ── Scanner panel ──────────────────────────────── --}}
                <div class="col-12 col-md-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">

                            {{-- Camera placeholder / reader --}}
                            <div class="position-relative mb-3">
                                <div id="qr-reader" class="w-100" style="border-radius:.75rem;overflow:hidden;"></div>
                                <div class="camera-placeholder w-100" id="camera-placeholder">
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm"
                                             style="width:64px;height:64px;">
                                            <i class="bx bx-qr-scan text-primary" style="font-size:2rem;"></i>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-semibold" style="font-size:.82rem;color:#566a7f;">Kamera belum aktif</div>
                                            <div class="text-muted" style="font-size:.72rem;">Tekan tombol di bawah untuk mulai scan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Camera buttons --}}
                            <div class="d-grid gap-2 mb-3">
                                <button id="btn-start" class="btn btn-primary">
                                    <i class="bx bx-camera me-1"></i>Mulai Scan
                                </button>
                                <button id="btn-stop" class="btn btn-outline-secondary d-none">
                                    <i class="bx bx-stop-circle me-1"></i>Hentikan Kamera
                                </button>
                            </div>

                            {{-- Divider --}}
                            <div class="scan-divider mb-3">atau input manual</div>

                            {{-- Manual input --}}
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 ps-3">
                                    <i class="bx bx-barcode text-muted"></i>
                                </span>
                                <input id="manual-input" type="text"
                                       class="form-control border-start-0 ps-1"
                                       placeholder="Plat Nomor"
                                       autocomplete="off">
                                <button class="btn btn-primary px-3" id="btn-lookup">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>

                            {{-- Status --}}
                            <div id="status-wrap" class="mt-3 d-none">
                                <div id="status-error" class="alert alert-danger mb-0 py-2 small d-none"></div>
                                <div id="status-loading" class="d-flex align-items-center gap-2 text-primary small d-none">
                                    <div class="spinner-border spinner-border-sm"></div>
                                    <span>Mencari kendaraan...</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── Result panel ────────────────────────────────── --}}
                <div class="col-12 col-md-7">

                    {{-- Empty state --}}
                    <div id="result-empty" class="card border-0 shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted py-5">
                            <div class="rounded-circle bg-body-tertiary d-flex align-items-center justify-content-center mb-3"
                                 style="width:80px;height:80px;">
                                <i class="bx bx-qr-scan text-primary" style="font-size:2.5rem; opacity:.4;"></i>
                            </div>
                            <div class="fw-semibold mb-1" style="font-size:.9rem;">Belum ada kendaraan yang discan</div>
                            <p class="mb-0 text-center" style="font-size:.78rem;">
                                Arahkan kamera ke QR pada label kendaraan,<br>
                                atau ketik plat nomornya secara manual.
                            </p>
                        </div>
                    </div>

                    {{-- Result card --}}
                    <div id="result-card" class="card border-0 shadow-sm d-none result-appear">

                        {{-- Header --}}
                        <div class="px-3 pt-3 pb-2 d-flex justify-content-between align-items-center gap-2 border-bottom">
                            <div class="d-flex align-items-center gap-2 min-w-0 overflow-hidden">
                                <i class="bx bx-check-circle text-success flex-shrink-0" style="font-size:1.15rem;"></i>
                                <span class="fw-semibold text-nowrap" style="font-size:.82rem;">Kendaraan Ditemukan</span>
                                <span id="result-type-badge" class="badge rounded-pill flex-shrink-0 badge-type-vehicle" style="font-size:.65rem;">Kendaraan</span>
                            </div>
                            <button id="btn-reset" class="btn btn-sm btn-outline-secondary flex-shrink-0 py-1 px-2">
                                <i class="bx bx-refresh"></i>
                                <span class="d-none d-sm-inline ms-1">Scan Ulang</span>
                            </button>
                        </div>

                        {{-- Identity --}}
                        <div class="d-flex align-items-start gap-3 p-3 border-bottom">
                            <div id="result-img-wrap" class="flex-shrink-0"></div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-truncate mb-1" id="result-identifier"
                                     style="font-size:1.05rem; letter-spacing:-.01em;"></div>
                                <div class="text-muted small text-truncate mb-2" id="result-name"></div>
                                <div id="result-status-wrap" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>

                        {{-- Info grid --}}
                        <div class="p-3 border-bottom bg-body-tertiary">
                            <div class="row g-3" id="result-info-grid"></div>
                        </div>

                        {{-- Actions --}}
                        <div class="p-3 pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-bolt-circle text-primary" style="font-size:1rem;"></i>
                                <span class="result-field-label mb-0">Aksi Cepat</span>
                            </div>
                            <div id="result-actions"></div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    const RESOLVE_URL = @json(route('qr-scan.resolve'));
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const INITIAL_BARCODE = @json($initialBarcode);

    // ── DOM refs ─────────────────────────────────────────────────────
    const btnStart    = document.getElementById('btn-start');
    const btnStop     = document.getElementById('btn-stop');
    const btnReset    = document.getElementById('btn-reset');
    const btnLookup   = document.getElementById('btn-lookup');
    const manualInput = document.getElementById('manual-input');
    const placeholder = document.getElementById('camera-placeholder');
    const statusWrap    = document.getElementById('status-wrap');
    const statusError   = document.getElementById('status-error');
    const statusLoading = document.getElementById('status-loading');
    const resultEmpty = document.getElementById('result-empty');
    const resultCard  = document.getElementById('result-card');

    let scanner     = null;
    let scanning    = false;
    let lastBarcode = null;

    // ── Scanner ──────────────────────────────────────────────────────
    function startScanner() {
        if (scanning) return;
        placeholder.classList.add('d-none');
        scanner = new Html5Qrcode('qr-reader');

        Html5Qrcode.getCameras()
            .then(cameras => {
                if (!cameras.length) { showError('Kamera tidak terdeteksi.'); return; }
                const cam = cameras.find(c => /back|rear|environment/i.test(c.label))
                    ?? cameras[cameras.length - 1];
                return scanner.start(
                    cam.id,
                    { fps: 10, qrbox: { width: 180, height: 180 }, aspectRatio: 1 },
                    onScanSuccess, () => {}
                );
            })
            .then(() => {
                scanning = true;
                btnStart.classList.add('d-none');
                btnStop.classList.remove('d-none');
            })
            .catch(err => {
                placeholder.classList.remove('d-none');
                showError('Gagal mengakses kamera: ' + (err?.message ?? err));
            });
    }

    function stopScanner() {
        if (scanner && scanning) { scanner.stop().catch(() => {}); scanning = false; }
        btnStart.classList.remove('d-none');
        btnStop.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }

    function onScanSuccess(decodedText) {
        if (decodedText === lastBarcode) return;
        lastBarcode = decodedText;
        stopScanner();
        lookupBarcode(decodedText);
    }

    // ── API ──────────────────────────────────────────────────────────
    async function lookupBarcode(barcode) {
        showLoading();
        try {
            const res  = await fetch(RESOLVE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ barcode }),
            });
            const data = await res.json();
            if (!res.ok) { showError(data.error ?? 'Kendaraan tidak ditemukan.'); return; }
            hideStatus();
            renderResult(data);
            // On mobile, scroll result into view
            if (window.innerWidth < 768) {
                setTimeout(() => resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
            }
        } catch {
            showError('Gagal menghubungi server. Periksa koneksi jaringan Anda.');
        }
    }

    // ── Render result ────────────────────────────────────────────────
    const BUTTON_CONFIG = {
        maintenance: { bsCls: 'btn-dark',            icon: 'bx-wrench',  label: 'Tambah Pemeliharaan' },
        photo:       { bsCls: 'btn-primary',         icon: 'bx-camera',  label: 'Tambah Foto Bulanan' },
        view:        { bsCls: 'btn-outline-primary', icon: 'bx-show',    label: 'Lihat Detail' },
        label:       { bsCls: 'btn-outline-secondary', icon: 'bx-barcode', label: 'Cetak Label' },
    };

    // Display order and pairing of action buttons (2 per row)
    const BUTTON_PAIRS = [
        ['maintenance', 'photo'],
        ['view',        'label'],
    ];

    function renderResult(data) {
        const item = data.item;
        const acts = data.actions;

        // Image / avatar
        const imgWrap = document.getElementById('result-img-wrap');
        if (item.image_url) {
            imgWrap.innerHTML = `<img src="${esc(item.image_url)}" class="result-item-img" alt="">`;
        } else {
            imgWrap.innerHTML = `<div class="result-item-avatar bg-label-primary">
                <i class="bx bx-car text-primary"></i>
            </div>`;
        }

        // Identifier & name
        document.getElementById('result-identifier').textContent = item.identifier ?? '—';
        document.getElementById('result-name').textContent       = item.name || 'Tipe tidak diketahui';

        // Status badge
        const statusEl = document.getElementById('result-status-wrap');
        statusEl.innerHTML = item.status
            ? `<span class="badge px-2 py-1" style="background:${esc(item.status.color)};font-size:.68rem;">${esc(item.status.name)}</span>`
            : '';

        // Info grid (dynamic — skip empty fields)
        const infoFields = [
            { label: 'Perusahaan',  value: item.company },
            { label: 'Tahun Masuk', value: item.entry_year },
            { label: 'Kilometer',   value: item.mileage },
            { label: 'Bahan Bakar', value: item.fuel },
        ].filter(f => f.value);

        const infoGrid = document.getElementById('result-info-grid');
        infoGrid.innerHTML = infoFields.length
            ? infoFields.map(f => `
                <div class="col-6">
                    <div class="result-field-label">${esc(f.label)}</div>
                    <div class="result-field-value">${esc(f.value)}</div>
                </div>`).join('')
            : `<div class="col-12 text-muted small">Tidak ada informasi tambahan.</div>`;

        // Action buttons
        renderActionButtons(acts, document.getElementById('result-actions'));

        resultEmpty.classList.add('d-none');
        resultCard.classList.remove('d-none');
    }

    function renderActionButtons(acts, container) {
        container.innerHTML = '';

        BUTTON_PAIRS.forEach(pair => {
            const visible = pair.filter(key => {
                const act = acts[key];
                const cfg = BUTTON_CONFIG[key];
                return act && cfg && act.allowed && act.url;
            });

            if (!visible.length) return;

            const row = document.createElement('div');
            row.className = 'row g-2 mb-2';

            visible.forEach(key => {
                const act = acts[key];
                const cfg = BUTTON_CONFIG[key];
                const col = document.createElement('div');
                col.className = visible.length === 1 ? 'col-12' : 'col-6';

                const a = document.createElement('a');
                a.href      = act.url;
                a.className = `btn ${cfg.bsCls} btn-sm w-100`;
                a.innerHTML = `<i class="bx ${esc(cfg.icon)} me-1"></i>${esc(cfg.label)}`;
                col.appendChild(a);
                row.appendChild(col);
            });

            container.appendChild(row);
        });

        if (!container.children.length) {
            container.innerHTML = `<div class="text-muted small">Tidak ada aksi yang tersedia untuk Anda.</div>`;
        }
    }

    // ── Status helpers ───────────────────────────────────────────────
    function showLoading() {
        statusWrap.classList.remove('d-none');
        statusError.classList.add('d-none');
        statusLoading.classList.remove('d-none');
        resultCard.classList.add('d-none');
        resultEmpty.classList.remove('d-none');
    }

    function showError(msg) {
        statusWrap.classList.remove('d-none');
        statusLoading.classList.add('d-none');
        statusError.classList.remove('d-none');
        statusError.textContent = msg;
        lastBarcode = null;
    }

    function hideStatus() { statusWrap.classList.add('d-none'); }

    function resetAll() {
        lastBarcode = null;
        hideStatus();
        resultCard.classList.add('d-none');
        resultEmpty.classList.remove('d-none');
        manualInput.value = '';
    }

    // ── Escape ───────────────────────────────────────────────────────
    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Events ───────────────────────────────────────────────────────
    btnStart.addEventListener('click', startScanner);
    btnStop.addEventListener('click', stopScanner);
    btnReset.addEventListener('click', resetAll);
    btnLookup.addEventListener('click', () => {
        const val = manualInput.value.trim();
        if (!val) return;
        lastBarcode = val;
        lookupBarcode(val);
    });
    manualInput.addEventListener('keydown', e => { if (e.key === 'Enter') btnLookup.click(); });
    window.addEventListener('beforeunload', stopScanner);

    if (INITIAL_BARCODE) {
        manualInput.value = INITIAL_BARCODE;
        lastBarcode = INITIAL_BARCODE;
        lookupBarcode(INITIAL_BARCODE);
    }
}());
</script>
@endpush
