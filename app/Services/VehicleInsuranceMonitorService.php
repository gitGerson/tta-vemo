<?php

namespace App\Services;

use App\Models\VehicleInsurance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VehicleInsuranceMonitorService
{
    /**
     * Days-before-expiry at which a policy is flagged. Each policy surfaces in the
     * tightest bucket its remaining days fall into; the bucket value is part of the
     * notification key, so crossing into a closer threshold re-fires the reminder
     * even after the looser one was dismissed.
     *
     * @var list<int>
     */
    public const THRESHOLDS = [30, 14, 7, 3];

    /**
     * Insurance policies whose `end_date` falls within the widest reminder window
     * (today .. today + max threshold). Already-expired policies are excluded.
     * Company-scoped via the VehicleInsurance CompanyTenantScope (bypassed by
     * management.tenant.bypass). Sorted most-urgent first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function expiringPolicies(?Carbon $today = null, ?int $companyId = null): Collection
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $maxThreshold = max(self::THRESHOLDS);

        return VehicleInsurance::query()
            ->with('vehicle')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereDate('end_date', '<=', $today->copy()->addDays($maxThreshold)->toDateString())
            ->get()
            ->map(function (VehicleInsurance $insurance) use ($today): ?array {
                $daysLeft = (int) $today->diffInDays($insurance->end_date->copy()->startOfDay(), false);
                $threshold = $this->bucketFor($daysLeft);

                if ($threshold === null) {
                    return null;
                }

                return [
                    'insurance_id' => $insurance->id,
                    'vehicle_id' => $insurance->vehicle_id,
                    'plate' => $insurance->vehicle?->plat_new ?? '—',
                    'insurer' => $insurance->insurer_name,
                    'policy_number' => $insurance->policy_number,
                    'end_date' => $insurance->end_date->format('d M Y'),
                    'days_left' => $daysLeft,
                    'threshold' => $threshold,
                    'threshold_value' => $threshold,
                    'state' => 'expiring',
                    'link' => route('vehicle-insurances.show', $insurance),
                ];
            })
            ->filter()
            ->sortBy('days_left')
            ->values();
    }

    /**
     * Vehicles whose coverage has lapsed: their most recent policy (by end_date) is
     * already expired. Deduped to the latest policy per vehicle so a vehicle that has
     * since been renewed is not flagged for an older, superseded policy. Company-scoped
     * via the VehicleInsurance CompanyTenantScope. Sorted most-overdue first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function overduePolicies(?Carbon $today = null, ?int $companyId = null): Collection
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return $this->latestPolicyPerVehicle($companyId)
            ->filter(fn (VehicleInsurance $insurance): bool => $insurance->end_date->copy()->startOfDay()->lt($today))
            ->map(fn (VehicleInsurance $insurance): array => [
                'insurance_id' => $insurance->id,
                'vehicle_id' => $insurance->vehicle_id,
                'plate' => $insurance->vehicle?->plat_new ?? '—',
                'insurer' => $insurance->insurer_name,
                'policy_number' => $insurance->policy_number,
                'end_date' => $insurance->end_date->format('d M Y'),
                'days_overdue' => (int) $insurance->end_date->copy()->startOfDay()->diffInDays($today),
                'state' => 'overdue',
                'link' => route('vehicle-insurances.show', $insurance),
            ])
            ->sortByDesc('days_overdue')
            ->values();
    }

    /**
     * The most recent policy (largest end_date) for each vehicle in the tenant scope.
     *
     * @return Collection<int, VehicleInsurance>
     */
    private function latestPolicyPerVehicle(?int $companyId = null): Collection
    {
        return VehicleInsurance::query()
            ->with('vehicle')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->get()
            ->groupBy('vehicle_id')
            ->map(fn (Collection $policies): VehicleInsurance => $policies
                ->sortByDesc(fn (VehicleInsurance $insurance): int => $insurance->end_date->getTimestamp())
                ->first())
            ->values();
    }

    /**
     * The smallest threshold the remaining days fall within (e.g. 10 days → 14),
     * or null when outside every window.
     */
    private function bucketFor(int $daysLeft): ?int
    {
        if ($daysLeft < 0) {
            return null;
        }

        foreach (collect(self::THRESHOLDS)->sort()->values() as $threshold) {
            if ($daysLeft <= $threshold) {
                return $threshold;
            }
        }

        return null;
    }
}
