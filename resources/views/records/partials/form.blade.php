@php
    $record = $record ?? null;

    $companyOptions = ($companies ?? collect())->mapWithKeys(fn($company) => [
        $company->id => "{$company->name}" . ($company->code ? " ({$company->code})" : ''),
    ])->all();
    $driverOptions = ($drivers ?? collect())->pluck('name', 'id')->all();
    $vehicleOptions = ($vehicles ?? collect())->pluck('plat_new', 'plat_new')->all();
    $destinationOptions = ($destinations ?? collect())->pluck('name', 'name')->all();

    // Legacy records store destinations that predate the city list, so without
    // this the select would silently fall back to the placeholder and the edit
    // would blank a value the user never touched. The modal JS does the same for
    // rows opened from the datatable, where no $record is in scope.
    $storedDestination = old('destination', $record?->destination);

    if (filled($storedDestination) && ! array_key_exists($storedDestination, $destinationOptions)) {
        $destinationOptions[$storedDestination] = $storedDestination;
    }
    $fuelTypeOptions = ($fuelTypes ?? collect())->mapWithKeys(fn($fuelType) => [
        $fuelType->id => "{$fuelType->name} ({$fuelType->merk} - " . ucfirst($fuelType->type) . ')',
    ])->all();
    $fuelTypePrices = ($fuelTypes ?? collect())->pluck('price', 'id')->all();

    // Records store the driver NAME, so resolve back to the id for the dropdown.
    $selectedDriver1 = $record ? optional(($drivers ?? collect())->firstWhere('name', $record->driver_1))->id : null;
    $selectedDriver2 = $record ? optional(($drivers ?? collect())->firstWhere('name', $record->driver_2))->id : null;

    // plat_new => mileage, for the KM-awal autofill on vehicle change.
    $vehicleMileage = ($vehicles ?? collect())->pluck('mileage', 'plat_new')->all();
@endphp

<form method="POST" action="{{ $action }}" data-record-fuel-pricing-form
    data-fuel-type-prices="{{ json_encode($fuelTypePrices) }}">
    @csrf
    <input type="hidden" name="_method" id="record-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="record-form-action" value="{{ $action }}">
    {{-- Tells RecordController::update to send the user back to the detail page. --}}
    @if(($origin ?? null) === 'show')
        <input type="hidden" name="origin" value="show">
    @endif

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
            <x-form.select name="driver_1" label="Driver Utama" :options="$driverOptions" :value="$selectedDriver1"
                placeholder="Pilih driver utama..." mode="select2" inputClass="form-select-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.select name="driver_2" label="Driver Pendamping" :options="$driverOptions" :value="$selectedDriver2"
                placeholder="Pilih driver pendamping..." mode="select2" inputClass="form-select-sm" />
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
            <x-form.number name="bbm" label="Jumlah BBM (Liter)" :value="$record?->bbm" min="0" step="0.01"
                inputClass="form-control-sm" required />
        </div>
        <div class="col-md-4">
            <x-form.select name="fuel_type_id" label="Jenis BBM" :options="$fuelTypeOptions"
                :value="$record?->fuel_type_id" placeholder="Pilih jenis BBM..." mode="select2"
                inputClass="form-select-sm" required />
        </div>
        <div class="col-md-4">
            <label for="fuel_unit_price" class="form-label">
                Harga per Liter <span class="text-danger">*</span>
            </label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input type="number" id="fuel_unit_price" name="fuel_unit_price"
                    value="{{ old('fuel_unit_price', $record?->fuel_unit_price) }}"
                    min="0" max="{{ \App\Models\FuelType::MAX_PRICE }}" step="0.01" placeholder="0" required
                    data-fuel-unit-price
                    @class(['form-control', 'is-invalid' => $errors->has('fuel_unit_price')])>
            </div>
            <x-form.error name="fuel_unit_price" />
        </div>
        <div class="col-md-4">
            <label for="fuel_total_price" class="form-label">Total Harga</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input type="text" id="fuel_total_price" class="form-control" placeholder="0" readonly
                    data-fuel-total-price>
            </div>
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
            const $gated = $('#date, #driver_1, #driver_2, #no_pol, #destination, #start_km, #finish_km, #bbm, #fuel_type_id, #fuel_unit_price, #fuel_total_price');

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
