<?php

namespace App\Http\Requests;

use App\Models\VehicleType;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleTypeUpdateRequest extends VehicleTypeStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicle_types.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return VehicleType::validationRules($this->vehicleType());
    }

    protected function vehicleType(): ?VehicleType
    {
        $vehicleType = $this->route('vehicleType');

        if ($vehicleType instanceof VehicleType) {
            return $vehicleType;
        }

        return VehicleType::query()->find($vehicleType);
    }
}
