<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class ServiceReminderService
{
    /**
     * Mileage a vehicle may travel after a routine service before the next is due.
     */
    public const SERVICE_INTERVAL_KM = 10000;

    /**
     * Vehicles due for service: current odometer has reached the mileage of the last
     * completed routine service plus the service interval. Vehicles without a usable
     * routine-service baseline are excluded. Sorted most-overdue first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function dueVehicles(?int $companyId = null): Collection
    {
        $vehicles = Vehicle::where('status', 'ACTIVE')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->get(['id', 'plat_new', 'mileage']);

        $routineByPlate = Maintenance::where('isServiceRoutine', true)
            ->where('status', Maintenance::STATUS_COMPLETED)
            ->whereIn('no_pol', $vehicles->pluck('plat_new'))
            ->get(['no_pol', 'mileage', 'date_out', 'date_in'])
            ->groupBy('no_pol');

        return $vehicles
            ->map(function (Vehicle $vehicle) use ($routineByPlate): ?array {
                $baseline = $routineByPlate->get($vehicle->plat_new)
                    ?->sortByDesc(fn (Maintenance $m): string => ($m->date_out ?? $m->date_in)?->toDateString() ?? '')
                    ->first(fn (Maintenance $m): bool => $this->toKm($m->mileage) > 0);

                if ($baseline === null) {
                    return null; // No baseline to compute from — excluded.
                }

                $threshold = $this->toKm($baseline->mileage) + self::SERVICE_INTERVAL_KM;
                $current = $this->toKm($vehicle->mileage);
                $over = $current - $threshold;

                if ($over < 0) {
                    return null; // Not due yet.
                }

                $lastService = $baseline->date_out ?? $baseline->date_in;

                return [
                    'vehicle_id' => $vehicle->id,
                    'plate' => $vehicle->plat_new,
                    'current' => number_format($current, 0, ',', '.'),
                    'threshold' => number_format($threshold, 0, ',', '.'),
                    'threshold_value' => $threshold,
                    'over' => number_format($over, 0, ',', '.'),
                    'over_value' => $over,
                    'last_service' => $lastService?->format('d M Y') ?? '—',
                    'link' => route('maintenances.create'),
                ];
            })
            ->filter()
            ->sortByDesc('over_value')
            ->values();
    }

    /**
     * Normalize a stored mileage string (which may contain separators) to an integer km.
     */
    private function toKm(?string $mileage): int
    {
        return (int) preg_replace('/\D/', '', (string) $mileage);
    }
}
