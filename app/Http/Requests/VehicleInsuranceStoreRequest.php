<?php

namespace App\Http\Requests;

use App\Models\VehicleInsurance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VehicleInsuranceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicle_insurances.create') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return VehicleInsurance::validationRules();
    }

    public function messages(): array
    {
        return VehicleInsurance::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'insurer_name' => $this->normalizeText($this->input('insurer_name')),
            'policy_number' => $this->normalizeText($this->input('policy_number')),
            'insurance_type' => $this->normalizeText($this->input('insurance_type')),
            'coverage_value' => $this->nullIfBlank($this->input('coverage_value')),
            'premium' => $this->nullIfBlank($this->input('premium')),
            'notes' => $this->normalizeText($this->input('notes')),
        ]);
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
