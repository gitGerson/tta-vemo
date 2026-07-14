<?php

namespace App\Http\Requests\Concerns;

use App\Models\VehicleInsurance;

trait FiltersInsuranceFieldsByPermission
{
    /**
     * Drop validation rules for fields the user lacks the per-field permission
     * to fill. Disabled inputs are not submitted, so this also prevents spurious
     * `required` errors, and any spoofed value for a non-permitted field is
     * ignored because it never appears in the validated data.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function filterRulesByFieldPermission(array $rules): array
    {
        $permitted = VehicleInsurance::permittedFields($this->user());

        return array_filter(
            $rules,
            fn (string $key): bool => in_array(explode('.', $key)[0], $permitted, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
