<?php

namespace App\Http\Requests;

use App\Models\VehicleInsurance;
use App\Services\FilePondUploadService;
use Carbon\Carbon;
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
        $rules = VehicleInsurance::validationRules();
        $rules['attachments.*'] = [app(FilePondUploadService::class)->fileUploadValidationRule()];

        return $rules;
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
            'start_date' => $this->normalizeDate($this->input('start_date')),
            'end_date' => $this->normalizeDate($this->input('end_date')),
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

    protected function normalizeDate(mixed $value): mixed
    {
        if (
            ! is_string($value)
            || ! preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)
            || ! Carbon::canBeCreatedFromFormat($value, 'd-m-Y')
        ) {
            return $value;
        }

        return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
    }
}
