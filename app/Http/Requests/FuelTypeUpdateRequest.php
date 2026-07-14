<?php

namespace App\Http\Requests;

use App\Models\FuelType;
use Illuminate\Contracts\Validation\ValidationRule;

class FuelTypeUpdateRequest extends FuelTypeStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.fuel_types.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return FuelType::validationRules($this->fuelType(), $this->string('merk')->toString());
    }

    protected function fuelType(): ?FuelType
    {
        $fuelType = $this->route('fuelType');

        if ($fuelType instanceof FuelType) {
            return $fuelType;
        }

        return FuelType::query()->find($fuelType);
    }
}
