<?php

namespace App\Http\Requests;

use App\Models\VehicleBrand;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleBrandUpdateRequest extends VehicleBrandStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicle_brands.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleBrand = $this->vehicleBrand();
        $rules = VehicleBrand::validationRules($vehicleBrand);
        $rules['logo'] = ['nullable', app(FilePondUploadService::class)->imageUploadValidationRule(
            existingReferences: array_filter([$vehicleBrand?->logoUrl()]),
        )];

        return $rules;
    }

    public function messages(): array
    {
        return VehicleBrand::validationMessages();
    }

    protected function vehicleBrand(): ?VehicleBrand
    {
        $vehicleBrand = $this->route('vehicleBrand');

        if ($vehicleBrand instanceof VehicleBrand) {
            return $vehicleBrand;
        }

        return VehicleBrand::query()->find($vehicleBrand);
    }
}
