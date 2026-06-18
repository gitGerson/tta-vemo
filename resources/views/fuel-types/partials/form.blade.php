@php
    use App\Models\FuelType;

    $fuelType = $fuelType ?? null;
    $merkOptions = collect(FuelType::MERKS)->mapWithKeys(fn (string $merk): array => [$merk => $merk])->all();
    $typeOptions = FuelType::TYPES;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="fuel-type-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="fuel-type-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-md-6">
            <x-form.text name="name" label="Nama" :value="$fuelType?->name" inputClass="form-control-sm" required maxlength="255" />
        </div>

        <div class="col-md-6">
            <x-form.select name="type" label="Tipe" :options="$typeOptions" :value="$fuelType?->type ?? 'bensin'" inputClass="form-select-sm" required />
        </div>

        <div class="col-md-6">
            @php
                $priceValue = old('price', $fuelType?->price);
                $normalizedPriceValue = is_string($priceValue) ? preg_replace('/[^\d.]/', '', $priceValue) : $priceValue;
                $priceDisplay = is_numeric($normalizedPriceValue) ? number_format((float) $normalizedPriceValue, 0, '.', ',') : $priceValue;
                $priceInvalid = $errors->has('price');
            @endphp

            <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input
                    type="text"
                    id="price"
                    name="price"
                    value="{{ $priceDisplay }}"
                    class="form-control {{ $priceInvalid ? 'is-invalid' : '' }}"
                    inputmode="decimal"
                    required
                    data-rupiah-input
                >
                @if($priceInvalid)
                    <div class="invalid-feedback">{{ $errors->first('price') }}</div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <x-form.select name="merk" label="Merk" :options="$merkOptions" :value="$fuelType?->merk ?? 'Pertamina'" inputClass="form-select-sm" required />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
