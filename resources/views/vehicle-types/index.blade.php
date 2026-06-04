@extends('layouts.sneat')

@section('title')
    Daftar Vehicle Type
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" title="Daftar Vehicle Type">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('master.vehicle_types.create'))
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-vehicle-type" data-bs-toggle="modal" data-bs-target="#vehicle-type-modal">
                        <i class="bx bx-plus me-1"></i> Tambah Vehicle Type
                    </button>
                @endif
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="vehicle-type-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicle-type-modal-title">Tambah Vehicle Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('vehicle-types.partials.form', [
                        'action' => route('vehicle-types.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vehicle-type-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Vehicle Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- AJAX-loaded detail fragment --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('vehicle-type-modal');
    if (!modalEl) return;

    const createUrl = @json(route('vehicle-types.store'));
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
        document.getElementById('vehicle-type-form-method').value = '';
        document.getElementById('vehicle-type-form-action').value = createUrl;
        document.getElementById('vehicle-type-modal-title').textContent = 'Tambah Vehicle Type';
        form.querySelector('[type="submit"]').textContent = createLabel;
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('vehicle-type-form-method').value = 'PUT';
        document.getElementById('vehicle-type-form-action').value = d.updateUrl;
        document.getElementById('vehicle-type-modal-title').textContent = 'Edit Vehicle Type: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('code', d.code);
        setField('name', d.name);
        setField('descriptions', d.descriptions);
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
        const btn = e.target.closest('.btn-edit-vehicle-type');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            code: btn.dataset.code,
            name: btn.dataset.name,
            descriptions: btn.dataset.descriptions,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('vehicle-type-form-method').value = 'PUT';
                document.getElementById('vehicle-type-modal-title').textContent = 'Edit Vehicle Type: ' + @json(old('name'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
