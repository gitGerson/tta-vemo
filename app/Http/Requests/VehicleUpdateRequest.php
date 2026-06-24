<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleUpdateRequest extends VehicleStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicles.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicle = $this->vehicle();
        $rules = Vehicle::validationRules($vehicle);

        $filePond = app(FilePondUploadService::class);
        $rules['bpkb_image.*'] = [$filePond->imageUploadValidationRule(existingReferences: $vehicle?->bpkbImageUrls() ?? [])];
        $rules['stnk_image.*'] = [$filePond->imageUploadValidationRule(existingReferences: $vehicle?->stnkImageUrls() ?? [])];
        $rules['kir_image.*'] = [$filePond->imageUploadValidationRule(existingReferences: $vehicle?->kirImageUrls() ?? [])];

        return $rules;
    }

    protected function vehicle(): ?Vehicle
    {
        $vehicle = $this->route('vehicle');

        if ($vehicle instanceof Vehicle) {
            return $vehicle;
        }

        return Vehicle::query()->find($vehicle);
    }
}
