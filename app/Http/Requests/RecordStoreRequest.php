<?php

namespace App\Http\Requests;

use App\Models\Record;
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
            'start_km' => $this->stripSign($this->input('start_km')),
            'finish_km' => $this->stripSign($this->input('finish_km')),
            'bbm' => $this->stripSign($this->input('bbm')),
        ]);
    }

    protected function stripSign(mixed $value): mixed
    {
        return is_string($value) ? (string) Str::of($value)->remove('-')->trim() : $value;
    }
}
