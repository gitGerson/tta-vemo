@php
    $photo = $photo ?? null;
    $vehicle = $vehicle ?? null;
    $cancelUrl = route('vehicle-photos.create', ['vehicle_id' => $vehicle?->id]);
    $vehicleOptions = ($vehicles ?? collect())->mapWithKeys(fn ($v) => [
        $v->id => "{$v->plat_new}" . ($v->type ? " ({$v->type})" : ''),
    ])->all();

    // Last 12 months as period options, each flagged done/missing for the known vehicle.
    $currentPeriod = $photo?->period?->format('Y-m');
    $months = collect(range(0, 11))->map(function (int $i): array {
        $m = now()->startOfMonth()->subMonths($i);
        return ['value' => $m->format('Y-m'), 'label' => $m->translatedFormat('F Y')];
    });
    // Months already documented for this vehicle, excluding the record being edited so
    // its own month stays selectable.
    $documentedPeriods = collect($documentedPeriods ?? [])
        ->reject(fn (string $p): bool => $p === $currentPeriod)
        ->values()
        ->all();
    $firstMissing = $months->first(fn (array $m): bool => ! in_array($m['value'], $documentedPeriods, true));
    $selectedPeriod = old('period', $currentPeriod ?? ($firstMissing['value'] ?? now()->format('Y-m')));
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <h6 class="fw-semibold mb-3"><i class="bx bx-camera me-1 text-primary"></i> Foto Bulanan</h6>
    <div class="row g-3">
        <div class="col-md-6">
            @if ($vehicle !== null)
                <input type="hidden" name="vehicle_id" value="{{ old('vehicle_id', $vehicle->id) }}">
                <x-form.group name="vehicle" label="Kendaraan">
                    <input type="text" class="form-control form-control-sm" value="{{ $vehicle->plat_new }}{{ $vehicle->type ? " ({$vehicle->type})" : '' }}" readonly>
                </x-form.group>
            @else
                <x-form.select name="vehicle_id" label="Kendaraan" mode="select2" :options="$vehicleOptions" :value="$selectedVehicleId ?? null" placeholder="Pilih kendaraan..." inputClass="form-select-sm" required />
            @endif
        </div>

        <div class="col-md-6">
            <x-form.group name="period" label="Bulan" :required="true">
                <select name="period" id="period" required data-period-select data-current-period="{{ $currentPeriod }}"
                    @class(['form-select', 'form-select-sm', 'is-invalid' => $errors->has('period')])>
                    @foreach ($months as $m)
                        @php $done = in_array($m['value'], $documentedPeriods, true); @endphp
                        <option value="{{ $m['value'] }}" data-label="{{ $m['label'] }}" data-done="{{ $done ? '1' : '0' }}" @disabled($done) @selected($m['value'] === $selectedPeriod)>
                            {{ $m['label'] }} — {{ $done ? '✓ Sudah ada Foto' : 'Belum ada Foto' }}
                        </option>
                    @endforeach
                </select>
            </x-form.group>
        </div>

        @foreach (\App\Models\VehiclePhoto::SIDES as $side => $label)
            <div class="col-md-6">
                <x-form.file name="photos[{{ $side }}]" label="Foto {{ $label }}" mode="filepond" accept="image/*"
                    :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']" maxFileSize="2MB"
                    :existingFiles="$photo?->sideUrl($side) ? [$photo->sideUrl($side)] : []" />
            </div>
        @endforeach
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-note me-1 text-primary"></i> Keterangan</h6>
    <div class="row g-3">
        <div class="col-12">
            <x-form.textarea name="notes" label="Keterangan" :value="$photo?->notes" rows="3" inputClass="form-control-sm" required placeholder="foto kendaraan lengkap" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('styles')
    <style>
        /* Render the select2 widgets at the small (form-select-sm) size. */
        .form-select-sm+.select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 0.5rem + 2px);
            height: calc(1.5em + 0.5rem + 2px);
            padding-top: 0.18rem;
            padding-bottom: 0.18rem;
            font-size: 0.8125rem;
        }

        .form-select-sm+.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.3;
            padding-left: 0.5rem;
        }

        .form-select-sm+.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.5rem);
        }
    </style>
@endpush

@if ($vehicle === null)
    {{-- Standalone page: vehicle is chosen client-side, so refresh the month statuses on change. --}}
    <script type="application/json" id="periods-by-vehicle">{!! json_encode($periodsByVehicle ?? [], JSON_UNESCAPED_UNICODE) !!}</script>
@endif

@push('scripts')
    <script>
        // Standalone page: refresh the month statuses when the vehicle changes. Bind the
        // change through jQuery (delegated) since Select2 fires its change via jQuery,
        // which native addEventListener('change') would miss.
        (function () {
            const periodSelect = document.getElementById('period');
            const dataEl = document.getElementById('periods-by-vehicle');
            if (!periodSelect || !dataEl) return;

            let periodsByVehicle = {};
            try {
                periodsByVehicle = JSON.parse(dataEl.textContent || '{}');
            } catch (e) {
                periodsByVehicle = {};
            }
            const current = periodSelect.dataset.currentPeriod || '';

            function refresh() {
                const vehicleId = (window.jQuery ? window.jQuery('#vehicle_id').val() : (document.getElementById('vehicle_id') || {}).value) || '';
                const documented = (periodsByVehicle[vehicleId] || []).filter(function (p) {
                    return p !== current;
                });
                let firstEnabled = null;

                Array.prototype.forEach.call(periodSelect.options, function (opt) {
                    const isDone = documented.indexOf(opt.value) !== -1;
                    const label = opt.dataset.label || opt.value;
                    opt.disabled = isDone;
                    opt.dataset.done = isDone ? '1' : '0';
                    opt.textContent = label + ' — ' + (isDone ? '✓ Sudah ada Foto' : 'Belum ada Foto');
                    if (!isDone && firstEnabled === null) {
                        firstEnabled = opt.value;
                    }
                });

                if (periodSelect.selectedOptions[0] && periodSelect.selectedOptions[0].disabled && firstEnabled !== null) {
                    periodSelect.value = firstEnabled;
                }
            }

            if (window.jQuery) {
                window.jQuery(document).on('change', '#vehicle_id', refresh);
            } else {
                const vehicleSelect = document.getElementById('vehicle_id');
                if (vehicleSelect) vehicleSelect.addEventListener('change', refresh);
            }
            refresh();
        })();
    </script>
@endpush
