@extends('layouts.sneat')

@section('title')
    Daftar Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable.card title="Daftar Driver">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.drivers.delete'))
                        <a href="{{ route('drivers.trash') }}" class="btn btn-secondary">
                            <i class="bx bx-trash me-1"></i> Trash
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('master.drivers.create'))
                        <button type="button" class="btn btn-primary" id="btn-tambah-driver" data-bs-toggle="modal" data-bs-target="#driver-modal">
                            <i class="bx bx-plus me-1"></i> Tambah Driver
                        </button>
                    @endif
                </div>
            </x-slot:actions>

            <x-datatable.table
                id="drivers-table"
                :columns="['No', 'Nama', 'No Identitas', 'No Telepon', 'Status', 'Aksi']"
            />
        </x-datatable.card>
    </div>

    <div class="modal fade" id="driver-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="driver-modal-title">Tambah Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('drivers.partials.form', [
                        'action'      => route('drivers.store'),
                        'method'      => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

<x-datatable.scripts table-id="drivers-table" ajax="{{ route('drivers.data') }}" clickable-rows>
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'no_identity', name: 'no_identity' },
    { data: 'no_phone_1', name: 'no_phone_1' },
    { data: 'status', name: 'status' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('driver-modal');
    if (!modalEl) return;

    const createUrl  = @json(route('drivers.store'));
    const createLabel = 'Simpan';
    let pendingEditData = null;

    // ── helpers ──────────────────────────────────────────────────────────────

    function getForm()  { return modalEl.querySelector('form'); }
    function getPond()  {
        const input = modalEl.querySelector('.js-filepond');
        return input ? window.FilePond?.find(input) : null;
    }

    function setField(name, value) {
        const el = getForm().querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }

    // ── create mode ──────────────────────────────────────────────────────────

    function resetToCreate() {
        const form = getForm();
        form.reset();
        form.action = createUrl;
        document.getElementById('driver-form-method').value = '';
        document.getElementById('driver-modal-title').textContent = 'Tambah Driver';
        form.querySelector('[type="submit"]').textContent = createLabel;
        getPond()?.removeFiles();
    }

    // ── edit mode ────────────────────────────────────────────────────────────

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('driver-form-method').value = 'PUT';
        document.getElementById('driver-modal-title').textContent = 'Edit Driver: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('nik',         d.nik);
        setField('name',        d.name);
        setField('no_identity', d.noIdentity);
        setField('residence',   d.residence);
        setField('date_birth',  d.dateBirth);
        setField('no_phone_1',  d.noPhone1);
        setField('no_phone_2',  d.noPhone2);
        setField('address',     d.address);
        setField('entry_date',  d.entryDate);
        setField('out_date',    d.outDate);
        setField('status',      d.status);

        const pond = getPond();
        if (pond) {
            pond.removeFiles();
            if (d.imageUrl) {
                pond.addFile(d.imageUrl, {
                    type: 'local',
                    metadata: { poster: d.imageUrl, originalSource: d.imageUrl },
                });
            }
        }
    }

    // ── modal events ─────────────────────────────────────────────────────────

    modalEl.addEventListener('shown.bs.modal', function () {
        document.dispatchEvent(new Event('form:enhance'));

        if (pendingEditData) {
            applyEdit(pendingEditData);
            pendingEditData = null;
        }
    });

    modalEl.addEventListener('hidden.bs.modal', resetToCreate);

    // ── edit button clicks (delegated — works after DataTable renders) ────────

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-driver');
        if (!btn) return;

        pendingEditData = {
            updateUrl:  btn.dataset.updateUrl,
            name:       btn.dataset.name,
            nik:        btn.dataset.nik,
            noIdentity: btn.dataset.noIdentity,
            residence:  btn.dataset.residence,
            dateBirth:  btn.dataset.dateBirth,
            noPhone1:   btn.dataset.noPhone1,
            noPhone2:   btn.dataset.noPhone2,
            address:    btn.dataset.address,
            entryDate:  btn.dataset.entryDate,
            outDate:    btn.dataset.outDate,
            status:     btn.dataset.status,
            imageUrl:   btn.dataset.imageUrl,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // ── reopen on validation failure (create only) ────────────────────────────

    @if($errors->any())
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
