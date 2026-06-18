@php
    $vehicle = $vehicle ?? null;
    $statusOptions = [
        'ACTIVE' => 'ACTIVE',
        'PASSIVE' => 'PASSIVE',
    ];
    $yesNoOptions = [
        'NO' => 'NO',
        'YES' => 'YES',
    ];
    // Explicit value/label list: a ['0' => ..., '1' => ...] map collapses to a PHP
    // list (numeric keys), which <x-form.select> reads as value=label and would
    // submit "Yes"/"No" instead of 1/0, breaking the boolean validation.
    $privateOptions = [
        ['value' => '1', 'label' => 'Yes'],
        ['value' => '0', 'label' => 'No'],
    ];
    $companyOptions = ($companies ?? collect())->mapWithKeys(fn ($company) => [
        $company->id => "{$company->name} ({$company->code})",
    ])->all();
    $vehicleTypeOptions = ($vehicleTypes ?? collect())->mapWithKeys(fn ($vehicleType) => [
        $vehicleType->id => "{$vehicleType->name} ({$vehicleType->code})",
    ])->all();
    $vehicleBrandOptions = ($vehicleBrands ?? collect())->mapWithKeys(fn ($vehicleBrand) => [
        $vehicleBrand->id => "{$vehicleBrand->name} ({$vehicleBrand->code})",
    ])->all();
    $fuelTypeOptions = ($fuelTypes ?? collect())->mapWithKeys(fn ($fuelType) => [
        $fuelType->id => "{$fuelType->name} ({$fuelType->merk} - " . ucfirst($fuelType->type) . ")",
    ])->all();
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <h6 class="fw-semibold mb-3"><i class="bx bx-car me-1 text-primary"></i> Informasi Kendaraan</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <x-form.select name="vehicle_type_id" label="Tipe" :options="$vehicleTypeOptions" :value="$vehicle?->vehicle_type_id" placeholder="Pilih tipe kendaraan..." inputClass="form-select-sm" />
            <input type="hidden" name="type" value="{{ old('type', $vehicle?->type) }}">
        </div>

        <div class="col-md-6">
            <x-form.text name="entry_year" label="Tahun Masuk" :value="$vehicle?->entry_year" inputClass="form-control-sm" required maxlength="5" />
        </div>

        <div class="col-md-6">
            <x-form.select name="vehicle_brand_id" label="Brand" :options="$vehicleBrandOptions" :value="$vehicle?->vehicle_brand_id" placeholder="Pilih brand kendaraan..." inputClass="form-select-sm" />
        </div>

        <div class="col-md-6">
            <x-form.select name="company_id" label="Perusahaan" :options="$companyOptions" :value="$vehicle?->company_id" placeholder="Pilih perusahaan..." inputClass="form-select-sm" />
        </div>

        <div class="col-md-6">
            <x-form.text name="plat_new" label="Plat Nomor" :value="$vehicle?->plat_new" inputClass="form-control-sm" required maxlength="20" />
        </div>

        <div class="col-md-6">
            <x-form.text name="mileage" label="Kilometer" :value="$vehicle?->mileage ?? '0'" inputClass="form-control-sm" />
        </div>

        <div class="col-md-6">
            <x-form.select name="fuel_type_id" label="Bahan Bakar" :options="$fuelTypeOptions" :value="$vehicle?->fuel_type_id" placeholder="Pilih bahan bakar..." inputClass="form-select-sm" />
            <input type="hidden" name="fuel_type" value="{{ old('fuel_type', $vehicle?->fuel_type ?? 'BBM') }}">
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-toggle-right me-1 text-primary"></i> Status &amp; Operasional</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <x-form.select name="status" label="Status" :options="$statusOptions" :value="$vehicle?->status ?? 'ACTIVE'" inputClass="form-select-sm" required />
        </div>

        <div class="col-md-4">
            <x-form.select name="mtc" label="Maintenance" :options="$yesNoOptions" :value="$vehicle?->mtc ?? 'NO'" inputClass="form-select-sm" required />
        </div>

        <div class="col-md-4">
            <x-form.select name="isprivate" label="Private" :options="$privateOptions" option-value="value" option-label="label" :value="(string) (int) ($vehicle?->isprivate ?? false)" inputClass="form-select-sm" required />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-barcode me-1 text-primary"></i> Identitas Mesin</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <x-form.text name="machine_number" label="Nomor Mesin" :value="$vehicle?->machine_number" inputClass="form-control-sm" maxlength="100" />
        </div>

        <div class="col-md-6">
            <x-form.text name="vin_number" label="Nomor Rangka (VIN)" :value="$vehicle?->vin_number" inputClass="form-control-sm" maxlength="100" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-money me-1 text-primary"></i> Keuangan</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <x-form.number name="purchase_price" label="Harga Beli" :value="$vehicle?->purchase_price" min="0" step="0.01" inputClass="form-control-sm" />
        </div>

        <div class="col-md-3">
            <x-form.number name="selling_price" label="Harga Jual" :value="$vehicle?->selling_price" min="0" step="0.01" inputClass="form-control-sm" />
        </div>

        <div class="col-md-3">
            <x-form.date name="purchase_date" label="Tanggal Beli" :value="$vehicle?->purchase_date?->format('Y-m-d')" inputClass="form-control-sm" />
        </div>

        <div class="col-md-3">
            <x-form.date name="sale_date" label="Tanggal Jual" :value="$vehicle?->sale_date?->format('Y-m-d')" inputClass="form-control-sm" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-file me-1 text-primary"></i> Dokumen</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <x-form.file name="bpkb_image" label="Foto BPKB" mode="filepond" multiple accept="image/*"
                :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']" maxFileSize="2MB"
                :existingFiles="$vehicle?->bpkbImageUrls() ?? []" />
        </div>

        <div class="col-md-6">
            <x-form.file name="stnk_image" label="Foto STNK" mode="filepond" multiple accept="image/*"
                :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']" maxFileSize="2MB"
                :existingFiles="$vehicle?->stnkImageUrls() ?? []" />
        </div>
    </div>

    <hr class="my-4 border-2 opacity-50">

    <h6 class="fw-semibold mb-3"><i class="bx bx-note me-1 text-primary"></i> Keterangan</h6>
    <div class="row g-3">
        <div class="col-12">
            <x-form.textarea name="desc" label="Keterangan" :value="$vehicle?->desc" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
