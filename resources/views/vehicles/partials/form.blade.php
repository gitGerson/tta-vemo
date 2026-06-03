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
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="vehicle-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="vehicle-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-md-6">
            <x-form.text name="type" label="Tipe" :value="$vehicle?->type" inputClass="form-control-sm" required maxlength="50" />
        </div>

        <div class="col-md-6">
            <x-form.text name="entry_year" label="Tahun Masuk" :value="$vehicle?->entry_year" inputClass="form-control-sm" required maxlength="5" />
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
            <x-form.text name="fuel_type" label="Bahan Bakar" :value="$vehicle?->fuel_type ?? 'BBM'" inputClass="form-control-sm" required />
        </div>

        <div class="col-md-4">
            <x-form.select name="status" label="Status" :options="$statusOptions" :value="$vehicle?->status ?? 'ACTIVE'" inputClass="form-select-sm" required />
        </div>

        <div class="col-md-4">
            <x-form.select name="mtc" label="Maintenance" :options="$yesNoOptions" :value="$vehicle?->mtc ?? 'NO'" inputClass="form-select-sm" required />
        </div>

        <div class="col-md-4">
            <x-form.select name="isprivate" label="Private" :options="$privateOptions" option-value="value" option-label="label" :value="(string) (int) ($vehicle?->isprivate ?? false)" inputClass="form-select-sm" required />
        </div>

        <div class="col-12">
            <x-form.textarea name="desc" label="Keterangan" :value="$vehicle?->desc" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
