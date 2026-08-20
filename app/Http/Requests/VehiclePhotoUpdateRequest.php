<?php

namespace App\Http\Requests;

use App\Models\VehiclePhoto;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;

class VehiclePhotoUpdateRequest extends VehiclePhotoStoreRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('operational.vehicle_photos.edit') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $photo = $this->photo();
        $rules = VehiclePhoto::validationRules($photo);

        $filePond = app(FilePondUploadService::class);
        foreach (array_keys(VehiclePhoto::SIDES) as $side) {
            $existing = $photo?->sideUrl($side);
            $rules["photos.$side"] = [$filePond->imageUploadValidationRule(existingReferences: $existing ? [$existing] : [])];
        }

        return $rules;
    }

    protected function photo(): ?VehiclePhoto
    {
        $photo = $this->route('vehiclePhoto');

        if ($photo instanceof VehiclePhoto) {
            return $photo;
        }

        return VehiclePhoto::query()->find($photo);
    }
}
