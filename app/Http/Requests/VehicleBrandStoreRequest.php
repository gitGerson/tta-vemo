<?php

namespace App\Http\Requests;

use App\Models\VehicleBrand;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class VehicleBrandStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicle_brands.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = VehicleBrand::validationRules();
        $rules['logo'] = ['nullable', app(FilePondUploadService::class)->imageUploadValidationRule()];

        return $rules;
    }

    public function messages(): array
    {
        return VehicleBrand::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->normalizeUpper($this->input('code')),
            'name' => $this->normalizeUpper($this->input('name')),
        ]);
    }

    protected function normalizeUpper(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return Str::upper(trim($value));
    }
}
