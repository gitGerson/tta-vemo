<?php

namespace App\Http\Requests;

use App\Models\VehiclePhoto;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class VehiclePhotoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('operational.vehicle_photos.create') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = VehiclePhoto::validationRules();

        $filePond = app(FilePondUploadService::class);
        foreach (array_keys(VehiclePhoto::SIDES) as $side) {
            $rules["photos.$side"] = [$filePond->imageUploadValidationRule()];
        }

        return $rules;
    }

    public function messages(): array
    {
        return VehiclePhoto::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicle_id' => $this->nullIfBlank($this->input('vehicle_id')),
            'period' => $this->normalizePeriod($this->input('period')),
            'notes' => $this->normalizeText($this->input('notes')),
        ]);
    }

    /**
     * Normalize a `YYYY-MM` (or any parseable date) input to the first day of that month.
     */
    protected function normalizePeriod(mixed $value): mixed
    {
        if (blank($value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->startOfMonth()->toDateString();
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function normalizeText(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    protected function nullIfBlank(mixed $value): mixed
    {
        return blank($value) ? null : $value;
    }
}
