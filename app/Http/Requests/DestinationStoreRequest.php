<?php

namespace App\Http\Requests;

use App\Models\Destination;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DestinationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.destinations.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Destination::validationRules();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => is_string($this->input('code')) ? trim($this->input('code')) : $this->input('code'),
            'province_code' => is_string($this->input('province_code')) ? trim($this->input('province_code')) : $this->input('province_code'),
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'meta' => is_string($this->input('meta')) ? trim($this->input('meta')) : $this->input('meta'),
        ]);
    }
}
