<?php

namespace App\Http\Requests;

use App\Models\Product;

class ProductUpdateRequest extends ProductStoreRequest
{
    /**
     * Use this request for update-specific or feature-specific rules only.
     * Shared reusable Product validation should remain in Product::validationRules()
     * and Product::validationMessages().
     */
    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return Product::validationRules($this->route('product'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Product::validationMessages($this->route('product'));
    }
}
