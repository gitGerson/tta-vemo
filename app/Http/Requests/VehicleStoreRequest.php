<?php

namespace App\Http\Requests;

use App\Models\FuelType;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VehicleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicles.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = Vehicle::validationRules();
        $rules['bpkb_image.*'] = [app(FilePondUploadService::class)->imageUploadValidationRule()];
        $rules['stnk_image.*'] = [app(FilePondUploadService::class)->imageUploadValidationRule()];

        return $rules;
    }

    public function messages(): array
    {
        return Vehicle::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicle_type_id' => $this->nullIfBlank($this->input('vehicle_type_id')),
            'type' => $this->resolveVehicleTypeLabel(),
            'entry_year' => $this->normalizeText($this->input('entry_year')),
            'mileage' => blank($this->input('mileage')) ? '0' : $this->normalizeText($this->input('mileage')),
            'plat_new' => $this->normalizePlate($this->input('plat_new')),
            'fuel_type_id' => $this->nullIfBlank($this->input('fuel_type_id')),
            'fuel_type' => $this->resolveFuelTypeLabel(),
            'desc' => $this->normalizeText($this->input('desc')),
            'machine_number' => $this->normalizeText($this->input('machine_number')),
            'vin_number' => $this->normalizeText($this->input('vin_number')),
            'purchase_price' => $this->nullIfBlank($this->input('purchase_price')),
            'selling_price' => $this->nullIfBlank($this->input('selling_price')),
            'purchase_date' => $this->nullIfBlank($this->input('purchase_date')),
            'sale_date' => $this->nullIfBlank($this->input('sale_date')),
        ]);
    }

    protected function normalizeText(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    protected function normalizePlate(mixed $value): mixed
    {
        return is_string($value) ? strtoupper(trim($value)) : $value;
    }

    protected function nullIfBlank(mixed $value): mixed
    {
        return blank($value) ? null : $value;
    }

    protected function resolveFuelTypeLabel(): mixed
    {
        $fuelTypeId = $this->nullIfBlank($this->input('fuel_type_id'));

        if ($fuelTypeId !== null) {
            return FuelType::query()->whereKey($fuelTypeId)->value('name') ?? $this->normalizeText($this->input('fuel_type', 'BBM'));
        }

        return $this->normalizeText($this->input('fuel_type', 'BBM'));
    }

    protected function resolveVehicleTypeLabel(): mixed
    {
        $vehicleTypeId = $this->nullIfBlank($this->input('vehicle_type_id'));

        if ($vehicleTypeId !== null) {
            return VehicleType::query()->whereKey($vehicleTypeId)->value('name') ?? $this->normalizeText($this->input('type'));
        }

        return $this->normalizeText($this->input('type'));
    }
}
