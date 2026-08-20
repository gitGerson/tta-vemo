<?php

namespace App\Http\Requests;

use App\Models\Driver;
use App\Services\FilePondUploadService;
use Carbon\Carbon;
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
        $rules = Driver::validationRules(firstPhoneNumber: $this->input('no_phone_1'));
        $rules['image'] = ['nullable', app(FilePondUploadService::class)->imageUploadValidationRule()];
        $rules['source_company_code'] = ['nullable', 'string', 'max:20'];
        $rules['source_company_name'] = ['nullable', 'string', 'max:255'];

        return $rules;
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
            'date_birth' => $this->normalizeDate($this->input('date_birth')),
            'entry_date' => $this->normalizeDate($this->input('entry_date')),
            'out_date' => $this->normalizeDate($this->input('out_date')),
        ]);
    }

    protected function normalizeDigits(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('/\D+/', '', $value);
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
