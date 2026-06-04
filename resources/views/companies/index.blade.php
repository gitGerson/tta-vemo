@extends('layouts.sneat')

@section('title')
    Daftar Perusahaan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" title="Daftar Perusahaan">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('master.companies.create'))
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-company" data-bs-toggle="modal" data-bs-target="#company-modal">
                        <i class="bx bx-plus me-1"></i> Tambah Perusahaan
                    </button>
                @endif
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="company-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="company-modal-title">Tambah Perusahaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('companies.partials.form', [
                        'action' => route('companies.store'),
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
    const modalEl = document.getElementById('company-modal');
    if (!modalEl) return;

    const createUrl = @json(route('companies.store'));
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
        document.getElementById('company-form-method').value = '';
        document.getElementById('company-form-action').value = createUrl;
        document.getElementById('company-modal-title').textContent = 'Tambah Perusahaan';
        form.querySelector('[type="submit"]').textContent = createLabel;
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('company-form-method').value = 'PUT';
        document.getElementById('company-form-action').value = d.updateUrl;
        document.getElementById('company-modal-title').textContent = 'Edit Perusahaan: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('code', d.code);
        setField('name', d.name);
        setField('email', d.email);
        setField('phone', d.phone);
        setField('image', d.image);
        setField('notes', d.notes);
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
        const btn = e.target.closest('.btn-edit-company');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            code: btn.dataset.code,
            name: btn.dataset.name,
            email: btn.dataset.email,
            phone: btn.dataset.phone,
            image: btn.dataset.image,
            notes: btn.dataset.notes,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('company-form-method').value = 'PUT';
                document.getElementById('company-modal-title').textContent = 'Edit Perusahaan: ' + @json(old('name'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
