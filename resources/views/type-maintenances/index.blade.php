@extends('layouts.sneat')

@section('title')
    Daftar Tipe Pemeliharaan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" title="Daftar Tipe Pemeliharaan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.type_maintenances.delete'))
                        <a href="{{ route('type-maintenances.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Sampah
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('master.type_maintenances.create'))
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-type-maintenance" data-bs-toggle="modal" data-bs-target="#type-maintenance-modal">
                            <i class="bx bx-plus me-1"></i> Tambah Tipe
                        </button>
                    @endif
                </div>
            </x-slot:actions>

            @if(auth()->user()->hasPermission('master.type_maintenances.create'))
                <x-slot:tools>
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dropdown-toggle aria-expanded="false">
                            <i class="bx bx-upload me-1"></i> Import
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-1 table-tabulator-menu">
                            <form id="type-maintenance-import-form" action="{{ route('type-maintenances.import') }}" method="POST" enctype="multipart/form-data" class="mb-0">
                                @csrf
                                <label class="dropdown-item mb-0" style="cursor: pointer;">
                                    <i class="me-1"></i>Excel
                                    <input type="file" name="excelFile" accept=".xlsx,.xls,.csv" class="d-none" onchange="document.getElementById('type-maintenance-import-form').submit();">
                                </label>
                            </form>
                        </div>
                    </div>
                </x-slot:tools>
            @endif
        </x-table>
    </div>

    <div class="modal fade" id="type-maintenance-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="type-maintenance-modal-title">Tambah Tipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('type-maintenances.partials.form', [
                        'action' => route('type-maintenances.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="type-maintenance-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tipe Pemeliharaan</h5>
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
    const modalEl = document.getElementById('type-maintenance-modal');
    if (!modalEl) return;

    const createUrl = @json(route('type-maintenances.store'));
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
        document.getElementById('type-maintenance-form-method').value = '';
        document.getElementById('type-maintenance-form-action').value = createUrl;
        document.getElementById('type-maintenance-modal-title').textContent = 'Tambah Tipe';
        form.querySelector('[type="submit"]').textContent = createLabel;
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('type-maintenance-form-method').value = 'PUT';
        document.getElementById('type-maintenance-form-action').value = d.updateUrl;
        document.getElementById('type-maintenance-modal-title').textContent = 'Ubah Tipe: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('name', d.name);
        setField('jenis', d.jenis);
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
        const btn = e.target.closest('.btn-edit-type-maintenance');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            name: btn.dataset.name,
            jenis: btn.dataset.jenis,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('type-maintenance-form-method').value = 'PUT';
                document.getElementById('type-maintenance-modal-title').textContent = 'Ubah Tipe: ' + @json(old('name'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
