@extends('layouts.sneat')

@section('title')
    Daftar Tujuan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" layout="fitColumns" title="Daftar Tujuan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.destinations.delete'))
                        <a href="{{ route('destinations.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Sampah
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('master.destinations.create'))
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-destination" data-bs-toggle="modal" data-bs-target="#destination-modal">
                            <i class="bx bx-plus me-1"></i> Tambah Tujuan
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="destination-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="destination-modal-title">Tambah Tujuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('destinations.partials.form', [
                        'action' => route('destinations.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="destination-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tujuan</h5>
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
    const modalEl = document.getElementById('destination-modal');
    if (!modalEl) return;

    const createUrl = @json(route('destinations.store'));
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
        document.getElementById('destination-form-method').value = '';
        document.getElementById('destination-form-action').value = createUrl;
        document.getElementById('destination-modal-title').textContent = 'Tambah Tujuan';
        form.querySelector('[type="submit"]').textContent = createLabel;
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('destination-form-method').value = 'PUT';
        document.getElementById('destination-form-action').value = d.updateUrl;
        document.getElementById('destination-modal-title').textContent = 'Ubah Tujuan: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('name', d.name);
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
        const btn = e.target.closest('.btn-edit-destination');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            name: btn.dataset.name,
            desc: btn.dataset.desc,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('destination-form-method').value = 'PUT';
                document.getElementById('destination-modal-title').textContent = 'Ubah Tujuan: ' + @json(old('name'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
