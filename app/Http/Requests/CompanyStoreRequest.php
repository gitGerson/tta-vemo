<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompanyStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.companies.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = Company::validationRules();
        $rules['image'] = ['nullable', app(FilePondUploadService::class)->imageUploadValidationRule()];

        return $rules;
    }

    public function messages(): array
    {
        return Company::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => is_string($this->input('code')) ? strtoupper(trim($this->input('code'))) : $this->input('code'),
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
            'image' => is_string($this->input('image')) ? trim($this->input('image')) : $this->input('image'),
            'notes' => is_string($this->input('notes')) ? trim($this->input('notes')) : $this->input('notes'),
        ]);
    }
}
