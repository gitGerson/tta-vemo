{{--
    Shared create/edit modal for records. Included by records/index.blade.php
    (create + row edit) and records/show.blade.php (edit only, origin="show").
--}}
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
                    'origin' => $origin ?? null,
                ])
            </div>
        </div>
    </div>
</div>

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

    // Setting a <select> to a value it has no <option> for silently yields '',
    // so a legacy destination that predates the city list would come back blank
    // and the user would be forced to change it. Add the stored value as an
    // option; the backend accepts it unchanged (Record::destinationRules).
    function ensureOption(name, value) {
        const el = getForm().querySelector(`[name="${name}"]`);
        if (!el || !value) return;

        if (!Array.from(el.options).some((option) => option.value === value)) {
            el.append(new Option(value, value));
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
        ensureOption('destination', d.destination);
        setField('destination', d.destination, { silent: true });
        setField('start_km', d.startKm, { silent: true });
        setField('finish_km', d.finishKm, { silent: true });
        setField('bbm', d.bbm, { silent: true });
        setField('fuel_type_id', d.fuelTypeId, { silent: true });
        setField('fuel_unit_price', d.fuelUnitPrice, { silent: true });

        recalculateTotal();

        // Deferred to a macrotask on purpose. record-fuel-pricing.js also handles
        // shown.bs.modal, and queues a microtask that re-runs its own prefill and
        // blanks the total again. Microtasks drain before timeouts, so this lands
        // after that pass instead of being clobbered by it.
        setTimeout(() => showStoredTotal(d.idr), 0);
    }

    // Total Harga is derived, not submitted: record-fuel-pricing.js owns it and
    // recomputes on a native 'input' from bbm / fuel_unit_price. The silent
    // jQuery triggers above emit no such event, so drive it explicitly here
    // rather than leaving the prefill to that module's shown.bs.modal hook.
    function recalculateTotal() {
        getForm().querySelector('[data-fuel-unit-price]')
            ?.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Records predating the unit-price field have no Rp/L, so the derived total
    // above resolves to blank. Fall back to the stored idr — the historical
    // figure the reports already use — so the modal isn't showing an empty
    // total for a record that has one. Must run after recalculateTotal(), which
    // would otherwise clear it. Typing a price recomputes and replaces this,
    // which is correct: that computed value is what update() will save.
    function showStoredTotal(idr) {
        const form = getForm();
        const total = form.querySelector('[data-fuel-total-price]');
        const unitPrice = form.querySelector('[data-fuel-unit-price]');

        if (!total || unitPrice?.value !== '' || !idr) return;

        total.value = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 })
            .format(Number(idr));
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
            fuelUnitPrice: btn.dataset.fuelUnitPrice,
            idr: btn.dataset.idr,
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
