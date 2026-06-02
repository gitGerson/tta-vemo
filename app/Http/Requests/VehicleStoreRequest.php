<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
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
        return Vehicle::validationRules();
    }

    public function messages(): array
    {
        return Vehicle::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->normalizeText($this->input('type')),
            'entry_year' => $this->normalizeText($this->input('entry_year')),
            'mileage' => blank($this->input('mileage')) ? '0' : $this->normalizeText($this->input('mileage')),
            'plat_new' => $this->normalizePlate($this->input('plat_new')),
            'plat_old' => $this->normalizePlate($this->input('plat_old')),
            'fuel_type' => $this->normalizeText($this->input('fuel_type', 'BBM')),
            'desc' => $this->normalizeText($this->input('desc')),
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
}
