<?php

namespace App\Http\Requests;

use App\Models\FuelType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class FuelTypeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.fuel_types.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return FuelType::validationRules(merk: $this->string('merk')->toString());
    }

    public function messages(): array
    {
        return FuelType::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? Str::upper(trim($this->input('name'))) : $this->input('name'),
            'type' => is_string($this->input('type')) ? Str::lower(trim($this->input('type'))) : $this->input('type'),
            'price' => is_string($this->input('price')) ? str_replace(['Rp', 'rp', ' ', ','], '', $this->input('price')) : $this->input('price'),
            'merk' => FuelType::normalizeMerk($this->input('merk')),
        ]);
    }
}
