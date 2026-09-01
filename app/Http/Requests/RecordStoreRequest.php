<?php

namespace App\Http\Requests;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RecordStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('operational.records.create') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Record::validationRules();
    }

    public function messages(): array
    {
        return Record::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date' => $this->normalizeDate($this->input('date')),
            'start_km' => $this->stripSign($this->input('start_km')),
            'finish_km' => $this->stripSign($this->input('finish_km')),
            'bbm' => is_string($this->input('bbm')) ? trim($this->input('bbm')) : $this->input('bbm'),
            'fuel_unit_price' => is_string($this->input('fuel_unit_price'))
                ? trim($this->input('fuel_unit_price'))
                : $this->input('fuel_unit_price'),
            'idr' => is_string($this->input('idr')) ? trim($this->input('idr')) : $this->input('idr'),
        ]);
    }

    protected function stripSign(mixed $value): mixed
    {
        return is_string($value) ? (string) Str::of($value)->remove('-')->trim() : $value;
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
