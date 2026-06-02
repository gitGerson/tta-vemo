<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleUpdateRequest extends VehicleStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.vehicles.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Vehicle::validationRules($this->vehicle());
    }

    protected function vehicle(): ?Vehicle
    {
        $vehicle = $this->route('vehicle');

        if ($vehicle instanceof Vehicle) {
            return $vehicle;
        }

        return Vehicle::query()->find($vehicle);
    }
}
