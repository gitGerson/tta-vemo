<?php

namespace App\Http\Requests;

use App\Models\FuelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FuelTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var FuelType|null $fuelType */
        $fuelType = $this->route('fuel_type');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fuel_types', 'name')
                    ->ignore($fuelType?->id),
            ],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => $this->normalizePrice($this->input('price')),
        ]);
    }

    private function normalizePrice(mixed $price): mixed
    {
        if (! is_string($price)) {
            return $price;
        }

        return str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $price) ?? $price);
    }
}
