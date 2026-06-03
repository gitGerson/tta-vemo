@extends('layouts.sneat')

@section('title')
    Daftar Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" layout="fitDataStretch" title="Daftar Kendaraan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.vehicles.delete'))
                        <a href="{{ route('vehicles.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Trash
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('master.vehicles.create'))
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-vehicle" data-bs-toggle="modal" data-bs-target="#vehicle-modal">
                            <i class="bx bx-plus me-1"></i> Tambah Kendaraan
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="vehicle-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicle-modal-title">Tambah Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('vehicles.partials.form', [
                        'action' => route('vehicles.store'),
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
    const modalEl = document.getElementById('vehicle-modal');
    if (!modalEl) return;

    const createUrl = @json(route('vehicles.store'));
    const createLabel = 'Simpan';
    let pendingEditData = null;

    function getForm() {
        return modalEl.querySelector('form');
    }

    function setField(name, value) {
        const el = getForm().querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }

    function resetToCreate() {
        const form = getForm();
        form.reset();
        form.action = createUrl;
        document.getElementById('vehicle-form-method').value = '';
        document.getElementById('vehicle-form-action').value = createUrl;
        document.getElementById('vehicle-modal-title').textContent = 'Tambah Kendaraan';
        form.querySelector('[type="submit"]').textContent = createLabel;
        setField('mileage', '0');
        setField('fuel_type', 'BBM');
        setField('status', 'ACTIVE');
        setField('mtc', 'NO');
        setField('isprivate', '0');
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('vehicle-form-method').value = 'PUT';
        document.getElementById('vehicle-form-action').value = d.updateUrl;
        document.getElementById('vehicle-modal-title').textContent = 'Edit Kendaraan: ' + d.platNew;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('type', d.type);
        setField('entry_year', d.entryYear);
        setField('company_id', d.companyId);
        setField('mileage', d.mileage);
        setField('plat_new', d.platNew);
        setField('status', d.status);
        setField('fuel_type', d.fuelType);
        setField('mtc', d.mtc);
        setField('isprivate', d.isprivate);
        setField('desc', d.desc);
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        document.dispatchEvent(new Event('form:enhance'));

        if (pendingEditData) {
            applyEdit(pendingEditData);
            pendingEditData = null;
        }
    });

    modalEl.addEventListener('hidden.bs.modal', resetToCreate);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-vehicle');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            type: btn.dataset.type,
            entryYear: btn.dataset.entryYear,
            companyId: btn.dataset.companyId,
            mileage: btn.dataset.mileage,
            platNew: btn.dataset.platNew,
            status: btn.dataset.status,
            fuelType: btn.dataset.fuelType,
            mtc: btn.dataset.mtc,
            isprivate: btn.dataset.isprivate,
            desc: btn.dataset.desc,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('vehicle-form-method').value = 'PUT';
                document.getElementById('vehicle-modal-title').textContent = 'Edit Kendaraan: ' + @json(old('plat_new'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
