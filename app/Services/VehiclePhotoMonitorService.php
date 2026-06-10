<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Support\Collection;

class VehiclePhotoMonitorService
{
    /**
     * Active vehicles that have no photo documented for the current month. Vehicles are
     * company-scoped via the Vehicle CompanyTenantScope (bypassed by management.tenant.bypass).
     * Sorted by plate for stable display.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function incompleteVehicles(): Collection
    {
        $period = now()->startOfMonth();

        $documentedVehicleIds = VehiclePhoto::query()
            ->whereDate('period', $period->toDateString())
            ->pluck('vehicle_id')
            ->all();

        return Vehicle::where('status', 'ACTIVE')
            ->whereNotIn('id', $documentedVehicleIds)
            ->orderBy('plat_new')
            ->get(['id', 'plat_new'])
            ->map(fn (Vehicle $vehicle): array => [
                'vehicle_id' => $vehicle->id,
                'plate' => $vehicle->plat_new,
                'period_value' => $period->format('Y-m'),
                'period_label' => $period->translatedFormat('F Y'),
                'link' => route('vehicles.show', $vehicle),
            ])
            ->values();
    }
}
