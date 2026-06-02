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
            'destination' => is_string($this->input('destination')) ? trim($this->input('destination')) : $this->input('destination'),
            'desc' => is_string($this->input('desc')) ? trim($this->input('desc')) : $this->input('desc'),
        ]);
    }
}
