<?php

namespace App\Http\Requests;

use App\Models\Driver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DriverStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.drivers.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Driver::validationRules(firstPhoneNumber: $this->input('no_phone_1'));
    }

    public function messages(): array
    {
        return Driver::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'no_identity' => $this->normalizeDigits($this->input('no_identity')),
            'no_phone_1' => $this->normalizeDigits($this->input('no_phone_1')),
            'no_phone_2' => $this->normalizeDigits($this->input('no_phone_2')),
        ]);
    }

    protected function normalizeDigits(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('/\D+/', '', $value);
    }
}
