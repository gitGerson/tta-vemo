@extends('layouts.sneat')

@section('title')
    Daftar Vehicle Brand
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" title="Daftar Vehicle Brand">
            <x-slot:actions>
                <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
                @if(auth()->user()->hasPermission('master.vehicle_brands.create'))
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-vehicle-brand" data-bs-toggle="modal" data-bs-target="#vehicle-brand-modal">
                        <i class="bx bx-plus me-1"></i> Tambah Vehicle Brand
                    </button>
                @endif
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="vehicle-brand-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicle-brand-modal-title">Tambah Vehicle Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('vehicle-brands.partials.form', [
                        'action' => route('vehicle-brands.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vehicle-brand-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Vehicle Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('vehicle-brand-modal');
    if (!modalEl) return;

    const createUrl = @json(route('vehicle-brands.store'));
    const createLabel = 'Simpan';
    let pendingEditData = null;

    function getForm() {
        return modalEl.querySelector('form');
    }

    function getPond() {
        const input = modalEl.querySelector('.js-filepond');
        return input ? window.FilePond?.find(input) : null;
    }

    function setField(name, value) {
        const el = getForm().querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }

    function resetToCreate() {
        const form = getForm();
        form.reset();
        form.action = createUrl;
        document.getElementById('vehicle-brand-form-method').value = '';
        document.getElementById('vehicle-brand-form-action').value = createUrl;
        document.getElementById('vehicle-brand-modal-title').textContent = 'Tambah Vehicle Brand';
        form.querySelector('[type="submit"]').textContent = createLabel;
        getPond()?.removeFiles();
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('vehicle-brand-form-method').value = 'PUT';
        document.getElementById('vehicle-brand-form-action').value = d.updateUrl;
        document.getElementById('vehicle-brand-modal-title').textContent = 'Edit Vehicle Brand: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('code', d.code);
        setField('name', d.name);
        setField('descriptions', d.descriptions);

        const pond = getPond();
        if (pond) {
            pond.removeFiles();
            if (d.logoUrl) {
                pond.addFile(d.logoUrl, {
                    type: 'local',
                    metadata: { poster: d.logoUrl, originalSource: d.logoUrl },
                });
            }
        }
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
        const btn = e.target.closest('.btn-edit-vehicle-brand');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            code: btn.dataset.code,
            name: btn.dataset.name,
            logoUrl: btn.dataset.logoUrl,
            descriptions: btn.dataset.descriptions,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('vehicle-brand-form-method').value = 'PUT';
                document.getElementById('vehicle-brand-modal-title').textContent = 'Edit Vehicle Brand: ' + @json(old('name'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
