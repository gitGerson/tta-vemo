@php
    $insurance = $insurance ?? null;
    $selectedVehicleId = $selectedVehicleId ?? $insurance?->vehicle_id;
    $vehicleOptions = ($vehicles ?? collect())->mapWithKeys(fn ($vehicle) => [
        $vehicle->id => "{$vehicle->plat_new}" . ($vehicle->type ? " ({$vehicle->type})" : ''),
    ])->all();
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <h6 class="fw-semibold mb-3"><i class="bx bx-shield-quarter me-1 text-primary"></i> Informasi Polis</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <x-form.select name="vehicle_id" label="Kendaraan" :options="$vehicleOptions" :value="$selectedVehicleId" placeholder="Pilih kendaraan..." mode="select2" inputClass="form-select-sm" required />
        </div>

        <div class="col-md-6">
            <x-form.text name="insurer_name" label="Perusahaan Asuransi" :value="$insurance?->insurer_name" inputClass="form-control-sm" required maxlength="255" />
        </div>

        <div class="col-md-6">
            <x-form.text name="policy_number" label="Nomor Polis" :value="$insurance?->policy_number" inputClass="form-control-sm" required maxlength="255" />
        </div>

        <div class="col-md-6">
            <x-form.select name="insurance_type" label="Jenis Asuransi" :options="\App\Models\VehicleInsurance::INSURANCE_TYPES" :value="$insurance?->insurance_type" placeholder="Pilih jenis asuransi..." mode="select2" inputClass="form-select-sm" required />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-calendar me-1 text-primary"></i> Masa Berlaku &amp; Nilai</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <x-form.date name="start_date" label="Tanggal Mulai" :value="$insurance?->start_date?->format('d-m-Y')" mode="daterangepicker" :single="true" :autoApply="true" format="DD-MM-YYYY" placeholder="Pilih tanggal..." inputClass="form-control-sm" required />
        </div>

        <div class="col-md-3">
            <x-form.date name="end_date" label="Tanggal Berakhir" :value="$insurance?->end_date?->format('d-m-Y')" mode="daterangepicker" :single="true" :autoApply="true" format="DD-MM-YYYY" placeholder="Pilih tanggal..." inputClass="form-control-sm" required />
        </div>

        <div class="col-md-3">
            <x-form.number name="coverage_value" label="Nilai Pertanggungan" :value="$insurance?->coverage_value" min="0" step="0.01" inputClass="form-control-sm" />
        </div>

        <div class="col-md-3">
            <x-form.number name="premium" label="Premi" :value="$insurance?->premium" min="0" step="0.01" inputClass="form-control-sm" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-note me-1 text-primary"></i> Keterangan</h6>
    <div class="row g-3">
        <div class="col-12">
            <x-form.textarea name="notes" label="Keterangan" :value="$insurance?->notes" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('vehicle-insurances.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
