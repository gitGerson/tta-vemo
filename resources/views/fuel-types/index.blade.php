@extends('layouts.sneat')

@section('title')
    Daftar Fuel Type
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" title="Daftar Fuel Type">
            <x-slot:actions>
                @if(auth()->user()->hasPermission('master.fuel_types.create'))
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-fuel-type" data-bs-toggle="modal" data-bs-target="#fuel-type-modal">
                        <i class="bx bx-plus me-1"></i> Tambah Fuel Type
                    </button>
                @endif
            </x-slot:actions>
        </x-table>
    </div>

    <div class="modal fade" id="fuel-type-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fuel-type-modal-title">Tambah Fuel Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('fuel-types.partials.form', [
                        'action' => route('fuel-types.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Simpan',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fuel-type-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Fuel Type</h5>
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
    const modalEl = document.getElementById('fuel-type-modal');
    if (!modalEl) return;

    const createUrl = @json(route('fuel-types.store'));
    const createLabel = 'Simpan';
    let pendingEditData = null;

    function getForm() {
        return modalEl.querySelector('form');
    }

    function setField(name, value) {
        const el = getForm().querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }

    function formatRupiah(value) {
        const cleaned = String(value ?? '').replace(/[^\d.]/g, '');

        if (!cleaned) {
            return '';
        }

        const [whole, decimal] = cleaned.split('.');
        const formattedWhole = Number(whole || 0).toLocaleString('en-US');

        return decimal !== undefined ? `${formattedWhole}.${decimal.slice(0, 2)}` : formattedWhole;
    }

    function enhancePriceInput() {
        const input = getForm().querySelector('[data-rupiah-input]');

        if (!input || input.dataset.rupiahEnhanced === '1') {
            return;
        }

        input.dataset.rupiahEnhanced = '1';
        input.value = formatRupiah(input.value);
        input.addEventListener('input', function () {
            const cursorAtEnd = input.selectionStart === input.value.length;
            input.value = formatRupiah(input.value);

            if (cursorAtEnd) {
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });
    }

    function resetToCreate() {
        const form = getForm();
        form.reset();
        form.action = createUrl;
        document.getElementById('fuel-type-form-method').value = '';
        document.getElementById('fuel-type-form-action').value = createUrl;
        document.getElementById('fuel-type-modal-title').textContent = 'Tambah Fuel Type';
        form.querySelector('[type="submit"]').textContent = createLabel;
        setField('price', '');
    }

    function applyEdit(d) {
        const form = getForm();
        form.action = d.updateUrl;
        document.getElementById('fuel-type-form-method').value = 'PUT';
        document.getElementById('fuel-type-form-action').value = d.updateUrl;
        document.getElementById('fuel-type-modal-title').textContent = 'Edit Fuel Type: ' + d.name;
        form.querySelector('[type="submit"]').textContent = 'Perbarui';

        setField('name', d.name);
        setField('type', d.type);
        setField('price', formatRupiah(d.price));
        setField('merk', d.merk);
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        document.dispatchEvent(new Event('form:enhance'));
        enhancePriceInput();

        if (pendingEditData) {
            applyEdit(pendingEditData);
            pendingEditData = null;
        }
    });

    modalEl.addEventListener('hidden.bs.modal', resetToCreate);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-fuel-type');
        if (!btn) return;

        pendingEditData = {
            updateUrl: btn.dataset.updateUrl,
            name: btn.dataset.name,
            type: btn.dataset.type,
            price: btn.dataset.price,
            merk: btn.dataset.merk,
        };

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    @if($errors->any())
        @if(old('_method') === 'PUT')
            (function () {
                const form = getForm();
                form.action = @json(old('_form_action'));
                document.getElementById('fuel-type-form-method').value = 'PUT';
                document.getElementById('fuel-type-modal-title').textContent = 'Edit Fuel Type: ' + @json(old('name'));
                form.querySelector('[type="submit"]').textContent = 'Perbarui';
            })();
        @endif

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
