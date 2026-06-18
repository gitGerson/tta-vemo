<?php

namespace App\Http\Requests;

use App\Models\TypeMaintenances;
use Illuminate\Contracts\Validation\ValidationRule;

class TypeMaintenanceUpdateRequest extends TypeMaintenanceStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.type_maintenances.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return TypeMaintenances::validationRules($this->typeMaintenance());
    }

    protected function typeMaintenance(): ?TypeMaintenances
    {
        $typeMaintenance = $this->route('typeMaintenance');

        if ($typeMaintenance instanceof TypeMaintenances) {
            return $typeMaintenance;
        }

        return TypeMaintenances::query()->find($typeMaintenance);
    }
}
