<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Keep generic reusable validation in the model.
     * Use this request explicitly for store-only or feature-only rules that
     * should not become part of the shared Product validation contract.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return Product::validationRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Product::validationMessages();
    }
}
