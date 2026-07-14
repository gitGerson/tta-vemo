@php
    $insurance = $insurance ?? null;
    $selectedVehicleId = $selectedVehicleId ?? $insurance?->vehicle_id;
    $vehicleOptions = ($vehicles ?? collect())->mapWithKeys(fn ($vehicle) => [
        $vehicle->id => "{$vehicle->plat_new}" . ($vehicle->type ? " ({$vehicle->type})" : ''),
    ])->all();
    $canField = fn (string $field) => auth()->user()?->hasPermission(\App\Models\VehicleInsurance::fieldPermission($field)) ?? false;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <h6 class="fw-semibold mb-3"><i class="bx bx-shield-quarter me-1 text-primary"></i> Informasi Polis</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <x-form.select name="vehicle_id" label="Kendaraan" :options="$vehicleOptions" :value="$selectedVehicleId" placeholder="Pilih kendaraan..." mode="select2" inputClass="form-select-sm" required :disabled="! $canField('vehicle_id')" />
        </div>

        <div class="col-md-6">
            <x-form.text name="insurer_name" label="Perusahaan Asuransi" :value="$insurance?->insurer_name" inputClass="form-control-sm" required maxlength="255" :readonly="! $canField('insurer_name')" />
        </div>

        <div class="col-md-6">
            <x-form.text name="policy_number" label="Nomor Polis" :value="$insurance?->policy_number" inputClass="form-control-sm" required maxlength="255" :readonly="! $canField('policy_number')" />
        </div>

        <div class="col-md-6">
            <x-form.select name="insurance_type" label="Jenis Asuransi" :options="\App\Models\VehicleInsurance::INSURANCE_TYPES" :value="$insurance?->insurance_type" placeholder="Pilih jenis asuransi..." inputClass="form-select-sm" required :disabled="! $canField('insurance_type')" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-calendar me-1 text-primary"></i> Masa Berlaku &amp; Nilai</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <x-form.date name="start_date" label="Tanggal Mulai" :value="$insurance?->start_date?->format('d-m-Y')" mode="daterangepicker" :single="true" :autoApply="true" format="DD-MM-YYYY" placeholder="Pilih tanggal..." inputClass="form-control-sm" required :disabled="! $canField('start_date')" />
        </div>

        <div class="col-md-3">
            <x-form.date name="end_date" label="Tanggal Berakhir" :value="$insurance?->end_date?->format('d-m-Y')" mode="daterangepicker" :single="true" :autoApply="true" format="DD-MM-YYYY" placeholder="Pilih tanggal..." inputClass="form-control-sm" required :disabled="! $canField('end_date')" />
        </div>

        <div class="col-md-3">
            <x-form.money name="coverage_value" label="Nilai Pertanggungan" :value="$insurance?->coverage_value" :readonly="! $canField('coverage_value')" />
        </div>

        <div class="col-md-3">
            <x-form.money name="premium" label="Premi" :value="$insurance?->premium" :readonly="! $canField('premium')" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-note me-1 text-primary"></i> Keterangan</h6>
    <div class="row g-3">
        <div class="col-12">
            <x-form.textarea name="notes" label="Keterangan" :value="$insurance?->notes" rows="3" inputClass="form-control-sm" :readonly="! $canField('notes')" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-paperclip me-1 text-primary"></i> Lampiran</h6>
    <div class="row g-3">
        <div class="col-12">
            <x-form.file name="attachments" label="Lampiran" mode="filepond" multiple
                maxFileSize="50MB"
                :filepondProcessUrl="route('uploads.attachments.process')"
                :existingFiles="$insurance?->attachmentUrls() ?? []"
                help="Semua jenis file diperbolehkan. Maksimal 50MB per file."
                :disabled="! $canField('attachments')" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('vehicle-insurances.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
