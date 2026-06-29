<?php

namespace App\Http\Requests;

use App\Models\VehicleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class VehicleTypeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicle_types.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return VehicleType::validationRules();
    }

    public function messages(): array
    {
        return VehicleType::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? Str::upper(trim($this->input('name'))) : $this->input('name'),
            'descriptions' => is_string($this->input('descriptions')) ? trim($this->input('descriptions')) : $this->input('descriptions'),
        ]);
    }
}
