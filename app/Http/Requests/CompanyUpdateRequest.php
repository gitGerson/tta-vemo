<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyUpdateRequest extends CompanyStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.companies.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Company::validationRules($this->company());
    }

    protected function company(): ?Company
    {
        $company = $this->route('company');

        if ($company instanceof Company) {
            return $company;
        }

        return Company::query()->find($company);
    }
}
