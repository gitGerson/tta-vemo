<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VehiclePhotoMonitorService
{
    /**
     * Active vehicles whose current-month photo set is not complete (all four sides). A
     * vehicle clears the flag only when its current-month record `isComplete()`; no record
     * or a partial record (n/4) keeps it flagged. Vehicles are company-scoped via the Vehicle
     * CompanyTenantScope (bypassed by management.tenant.bypass). Sorted by plate.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function incompleteVehicles(?Carbon $period = null, ?int $companyId = null): Collection
    {
        $period ??= now()->startOfMonth();
        $total = count(VehiclePhoto::SIDES);

        $records = VehiclePhoto::query()
            ->whereDate('period', $period->toDateString())
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->get(['vehicle_id', 'photos']);

        $completeVehicleIds = $records->filter->isComplete()->pluck('vehicle_id')->all();
        $completedById = $records->mapWithKeys(fn (VehiclePhoto $photo): array => [$photo->vehicle_id => $photo->completedCount()]);

        return Vehicle::where('status', 'ACTIVE')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereNotIn('id', $completeVehicleIds)
            ->orderBy('plat_new')
            ->get(['id', 'plat_new'])
            ->map(fn (Vehicle $vehicle): array => [
                'vehicle_id' => $vehicle->id,
                'plate' => $vehicle->plat_new,
                'period_value' => $period->format('Y-m'),
                'period_label' => $period->translatedFormat('F Y'),
                'completed' => $completedById[$vehicle->id] ?? 0,
                'total' => $total,
                'link' => route('vehicles.show', $vehicle),
            ])
            ->values();
    }
}
