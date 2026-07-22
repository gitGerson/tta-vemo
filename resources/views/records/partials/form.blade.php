@php
    $record = $record ?? null;

    $companyOptions = ($companies ?? collect())->mapWithKeys(fn($company) => [
        $company->id => "{$company->name}" . ($company->code ? " ({$company->code})" : ''),
    ])->all();
    $driverOptions = ($drivers ?? collect())->pluck('name', 'id')->all();
    $vehicleOptions = ($vehicles ?? collect())->pluck('plat_new', 'plat_new')->all();
    $destinationOptions = ($destinations ?? collect())->pluck('name', 'name')->all();
    $fuelTypeOptions = ($fuelTypes ?? collect())->mapWithKeys(fn($fuelType) => [
        $fuelType->id => "{$fuelType->name} - Rp " . number_format((float) $fuelType->price, 0, ',', '.'),
    ])->all();

    // Records store the driver NAME, so resolve back to the id for the dropdown.
    $selectedDriver1 = $record ? optional(($drivers ?? collect())->firstWhere('name', $record->driver_1))->id : null;
    $selectedDriver2 = $record ? optional(($drivers ?? collect())->firstWhere('name', $record->driver_2))->id : null;

    // plat_new => mileage, for the KM-awal autofill on vehicle change.
    $vehicleMileage = ($vehicles ?? collect())->pluck('mileage', 'plat_new')->all();
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="record-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="record-form-action" value="{{ $action }}">

    <h6 class="fw-semibold mb-3"><i class="bx bx-navigation me-1 text-primary"></i> Informasi Perjalanan</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <x-form.select name="company_id" label="Perusahaan" :options="$companyOptions" :value="$record?->company_id"
                placeholder="Pilih perusahaan..." mode="select2" inputClass="form-select-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.select name="no_pol" label="No Polisi" :options="$vehicleOptions" :value="$record?->no_pol"
                placeholder="Pilih kendaraan..." mode="select2" inputClass="form-select-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.date name="date" label="Tanggal" :value="$record?->date?->format('d-m-Y')" mode="daterangepicker"
                :single="true" :autoApply="true" format="DD-MM-YYYY" placeholder="Pilih tanggal..." inputClass="form-control-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.select name="destination" label="Tujuan" :options="$destinationOptions"
                :value="$record?->destination" placeholder="Pilih tujuan..." mode="select2" inputClass="form-select-sm"
                required />
        </div>
        <div class="col-md-4">
            <x-form.select name="driver_1" label="Supir 1" :options="$driverOptions" :value="$selectedDriver1"
                placeholder="Pilih supir 1..." mode="select2" inputClass="form-select-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.select name="driver_2" label="Supir 2" :options="$driverOptions" :value="$selectedDriver2"
                placeholder="Pilih supir 2..." mode="select2" inputClass="form-select-sm" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-tachometer me-1 text-primary"></i> Kilometer &amp; Bahan Bakar</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <x-form.number name="start_km" id="start_km" label="KM Awal" :value="$record?->start_km" min="0"
                inputClass="form-control-sm" />
        </div>
        <div class="col-md-4">
            <x-form.number name="finish_km" label="KM Akhir" :value="$record?->finish_km" min="0"
                inputClass="form-control-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.number name="bbm" label="BBM / Liter" :value="$record?->bbm" min="0" step="0.01"
                inputClass="form-control-sm" required />
        </div>
        <div class="col-md-12">
            <x-form.select name="fuel_type_id" label="Jenis BBM" :options="$fuelTypeOptions"
                :value="$record?->fuel_type_id" placeholder="Pilih jenis BBM..." mode="select2"
                inputClass="form-select-sm" required />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
    <script>
        // Bind through jQuery: select2 fires its change event via jQuery, which
        // native addEventListener('change') handlers do not reliably receive.
        (function () {
            const $ = window.jQuery;
            if (!$) return;

            const vehicleMileage = @json($vehicleMileage);
            const $company = $('#company_id');
            const $noPol = $('#no_pol');
            const $startKm = $('#start_km');
            const $driver1 = $('#driver_1');
            const $driver2 = $('#driver_2');
            let syncingDrivers = false;
            const driver1Options = $driver1.find('option').map(function () {
                return {
                    value: this.value,
                    text: this.text,
                };
            }).get();
            const driver2Options = $driver2.find('option').map(function () {
                return {
                    value: this.value,
                    text: this.text,
                };
            }).get();

            // Every field except Perusahaan is gated until a company is chosen.
            const $gated = $('#date, #driver_1, #driver_2, #no_pol, #destination, #start_km, #finish_km, #bbm, #fuel_type_id');

            function toggleGate() {
                const enabled = $company.val() !== null && $company.val() !== '';
                $gated.prop('disabled', !enabled);
                $gated.filter('select').trigger('change.select2');
            }

            // KM Awal mirrors the selected vehicle's current mileage.
            $noPol.on('change', function () {
                const mileage = vehicleMileage[this.value];
                $startKm.val(mileage !== undefined && mileage !== null ? mileage : '');
            });

            function rebuildDriverSelect($select, options, excludedValue, selectedValue) {
                const resolvedValue = selectedValue !== excludedValue ? selectedValue : null;

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.empty();

                options.forEach(function (option) {
                    if (option.value !== '' && option.value === excludedValue) {
                        return;
                    }

                    $select.append(new Option(option.text, option.value, false, option.value === resolvedValue));
                });

                if (!resolvedValue) {
                    $select.val(null);
                }
            }

            // Keep Supir 1 and Supir 2 mutually exclusive in both directions.
            function syncDrivers(changedField) {
                if (syncingDrivers) return;

                syncingDrivers = true;

                let selectedDriver1 = $driver1.val();
                let selectedDriver2 = $driver2.val();

                if (selectedDriver1 && selectedDriver1 === selectedDriver2) {
                    if (changedField === 'driver_2') {
                        selectedDriver1 = null;
                    } else {
                        selectedDriver2 = null;
                    }
                }

                rebuildDriverSelect($driver1, driver1Options, selectedDriver2, selectedDriver1);
                rebuildDriverSelect($driver2, driver2Options, selectedDriver1, selectedDriver2);
                document.dispatchEvent(new Event('form:enhance'));
                $driver1.trigger('change.select2');
                $driver2.trigger('change.select2');

                syncingDrivers = false;
            }

            $driver1.on('change', function () {
                syncDrivers('driver_1');
            });
            $driver2.on('change', function () {
                syncDrivers('driver_2');
            });
            $company.on('change', toggleGate);

            // Initial state: create starts locked, edit has a company pre-filled.
            toggleGate();
            syncDrivers('driver_1');
        })();
    </script>
@endpush
