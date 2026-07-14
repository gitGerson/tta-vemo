@extends('layouts.sneat')

@section('title')
    Data Perjalanan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter bar --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('records.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="start_date">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="end_date">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="company">Perusahaan</label>
                        <select id="company" name="company" class="form-select form-select-sm">
                            <option value="">Semuanya</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) $companyId === $company->id)>{{ $company->code ?? $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <x-table :table="$table" engine="tabulator" layout="fitDataFill" title="Catatan Perjalanan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('operational.records.delete'))
                        <a href="{{ route('records.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Sampah
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('operational.records.view'))
                        <a href="{{ route('records.export', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'company' => $companyId])) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-export me-1"></i> Export Excel
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('operational.records.create'))
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-record" data-bs-toggle="modal" data-bs-target="#record-modal">
                            <i class="bx bx-plus me-1"></i> Tambah
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="record-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="record-modal-title">Tambah Catatan Perjalanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('records.partials.form', [
                        'action' => route('records.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('record-modal');
    if (!modalEl) return;

    const createUrl = @json(route('records.store'));
    const createLabel = 'Simpan';
    let pendingEditData = null;

    function getForm() {
        return modalEl.querySelector('form');
    }

    // silent=true refreshes a select2 widget's display via the namespaced
    // 'change.select2' event WITHOUT firing the interactive 'change' handlers
    // (syncDrivers / mileage autofill) that would otherwise abort prefill.
    function setField(name, value, { silent = false } = {}) {
        const el = getForm().querySelector(`[name="${name}"]`);
        if (!el) return;

        el.value = value ?? '';

        if (window.jQuery) {
            const $el = window.jQuery(el);
            const picker = $el.data('daterangepicker');
            const format = el.dataset.dateFormat || 'YYYY-MM-DD';

            if (picker && value && window.moment) {
                const date = window.moment(value, format, true);

                if (date.isValid()) {
                    picker.setStartDate(date);
                    picker.setEndDate(date);
                }
            }

            $el.trigger(silent ? 'change.select2' : 'change');
        } else {
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function setMode(action, method, title, submitLabel) {
        const form = getForm();

        form.action = action;
        document.getElementById('record-form-method').value = method === 'POST' ? '' : method;
        document.getElementById('record-form-action').value = action;
        document.getElementById('record-modal-title').textContent = title;
        form.querySelector('[type="submit"]').textContent = submitLabel;
    }

    function resetToCreate() {
        getForm().reset();
        setMode(createUrl, 'POST', 'Tambah Catatan Perjalanan', createLabel);

        if (window.jQuery) {
            window.jQuery(getForm()).find('select').trigger('change');
        }
    }

    function applyEdit(d) {
        setMode(d.updateUrl, 'PUT', 'Ubah Catatan Perjalanan: ' + d.noPol, 'Perbarui');

        // company_id stays non-silent: its 'change' runs toggleGate to enable the
        // gated fields (disabled fields neither display as editable nor submit).
        setField('company_id', d.companyId);
        setField('date', d.date, { silent: true });
        setField('driver_1', d.driver1, { silent: true });
        setField('driver_2', d.driver2, { silent: true });
        setField('no_pol', d.noPol, { silent: true });
        setField('destination', d.destination, { silent: true });
        setField('start_km', d.startKm, { silent: true });
        setField('finish_km', d.finishKm, { silent: true });
        setField('bbm', d.bbm, { silent: true });
        setField('fuel_type_id', d.fuelTypeId, { silent: true });
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        document.dispatchEvent(new Event('form:enhance'));

        if (pendingEditData) {
            applyEdit(pendingEditData);
            pendingEditData = null;
        }
    });

    modalEl.addEventListener('hidden.bs.modal', resetToCreate);

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-edit-record');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            date: btn.dataset.date,
            companyId: btn.dataset.companyId,
            driver1: btn.dataset.driver1,
            driver2: btn.dataset.driver2,
            noPol: btn.dataset.noPol,
            destination: btn.dataset.destination,
            startKm: btn.dataset.startKm,
            finishKm: btn.dataset.finishKm,
            bbm: btn.dataset.bbm,
            fuelTypeId: btn.dataset.fuelTypeId,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            setMode(@json(old('_form_action')), 'PUT', 'Ubah Catatan Perjalanan: ' + @json(old('no_pol')), 'Perbarui');
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
