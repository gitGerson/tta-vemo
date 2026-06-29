<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Record;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Aggregates fleet financial, utilization and composition metrics for the
 * dashboard. All queries inherit the Vehicle/Maintenance/Record
 * BelongsToCompanyTenant global scope, so results are company-scoped to the
 * acting user (bypassed by management.tenant.bypass) without extra filtering.
 *
 * Month bucketing is done in PHP rather than SQL so the same code works across
 * the MySQL (production) and SQLite (test) connections.
 */
class DashboardMetricsService
{
    /**
     * Donut palette for fleet-composition slices, cycled when there are more
     * vehicle types than colours.
     *
     * @var list<string>
     */
    private const PALETTE = ['#7367f0', '#28c76f', '#ff9f43', '#00cfe8', '#ea5455', '#a8aaae', '#3d4ec0'];

    /**
     * Memoized monthly buckets keyed by the requested window length, so the
     * controller can call the derived methods independently without re-querying.
     *
     * @var array<string, Collection<int, array<string, mixed>>>
     */
    private array $monthlyCache = [];

    /**
     * Current-month and previous-month spend on maintenance and fuel, each with
     * a month-over-month delta (percent, null when there is no prior baseline).
     *
     * @return array<string, mixed>
     */
    public function financialSummary(int $months = 6, ?Carbon $month = null, ?int $companyId = null): array
    {
        $month ??= now()->startOfMonth();
        $monthly = $this->monthlyTotals($months, $month, $companyId);
        $current = $monthly->last();
        $previous = $monthly->count() > 1 ? $monthly->get($monthly->count() - 2) : null;

        return [
            'month_label' => $month->format('M Y'),
            'maintenance' => $this->statBlock($current['maintenance'], $previous['maintenance'] ?? null),
            'fuel' => $this->statBlock($current['fuel'], $previous['fuel'] ?? null),
        ];
    }

    /**
     * Current-month distance driven across the fleet.
     *
     * Fleet-wide fuel efficiency (km/litre) is intentionally not reported: the
     * fleet mixes vehicle and fuel types, so a single blended km/litre figure is
     * not meaningful or actionable.
     *
     * @return array{km: string}
     */
    public function utilizationSummary(?Carbon $month = null, ?int $companyId = null): array
    {
        $current = $this->monthlyTotals(1, $month ?? now()->startOfMonth(), $companyId)->first();

        return [
            'km' => number_format($current['km'], 0, ',', '.'),
        ];
    }

    /**
     * Maintenance vs fuel spend per month for the trend chart.
     *
     * @return array{categories: list<string>, maintenance: list<float>, fuel: list<float>}
     */
    public function spendTrend(int $months = 6, ?Carbon $month = null, ?int $companyId = null): array
    {
        $monthly = $this->monthlyTotals($months, $month ?? now()->startOfMonth(), $companyId);

        return [
            'categories' => $monthly->pluck('label')->all(),
            'maintenance' => $monthly->pluck('maintenance')->map(fn (float $v): float => round($v))->all(),
            'fuel' => $monthly->pluck('fuel')->map(fn (float $v): float => round($v))->all(),
        ];
    }

    /**
     * Routine vs non-routine maintenance counts per month for the volume chart.
     *
     * @return array{categories: list<string>, routine: list<int>, non_routine: list<int>}
     */
    public function maintenanceVolumeTrend(int $months = 6, ?Carbon $month = null, ?int $companyId = null): array
    {
        $monthly = $this->monthlyTotals($months, $month ?? now()->startOfMonth(), $companyId);

        return [
            'categories' => $monthly->pluck('label')->all(),
            'routine' => $monthly->pluck('routine')->all(),
            'non_routine' => $monthly->pluck('non_routine')->all(),
        ];
    }

    /**
     * Vehicle counts grouped by vehicle type for the composition donut.
     *
     * @return list<array{label: string, count: int, hex: string}>
     */
    public function fleetComposition(?int $companyId = null): array
    {
        $counts = Vehicle::query()
            ->selectRaw('vehicle_type_id, COUNT(*) as aggregate')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->groupBy('vehicle_type_id')
            ->pluck('aggregate', 'vehicle_type_id');

        $names = VehicleType::whereIn('id', $counts->keys()->filter()->all())->pluck('name', 'id');

        return $counts->values()
            ->map(fn ($count, int $index): array => [
                'label' => $names->get($counts->keys()[$index]) ?? 'Lainnya',
                'count' => (int) $count,
                'hex' => self::PALETTE[$index % count(self::PALETTE)],
            ])
            ->all();
    }

    /**
     * Build (and memoize) per-month buckets for the trailing $months window,
     * pre-seeded so empty months still render on the charts.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function monthlyTotals(int $months, Carbon $month, ?int $companyId = null): Collection
    {
        $cacheKey = implode(':', [$months, $month->format('Y-m'), $companyId ?? 'all']);

        return $this->monthlyCache[$cacheKey] ??= $this->computeMonthlyTotals($months, $month, $companyId);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function computeMonthlyTotals(int $months, Carbon $month, ?int $companyId = null): Collection
    {
        $end = $month->copy()->endOfMonth();
        $start = $month->copy()->startOfMonth()->subMonths($months - 1);

        $buckets = collect();
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $buckets->put($month->format('Y-m'), [
                'key' => $month->format('Y-m'),
                'label' => $month->format('M'),
                'maintenance' => 0.0,
                'fuel' => 0.0,
                'km' => 0.0,
                'bbm' => 0.0,
                'routine' => 0,
                'non_routine' => 0,
            ]);
        }

        Maintenance::where('status', '!=', Maintenance::STATUS_CANCELLED)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('date_out', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($query) use ($start, $end): void {
                        $query->whereNull('date_out')
                            ->whereBetween('date_in', [$start->toDateString(), $end->toDateString()]);
                    });
            })
            ->get(['total_amount', 'date_in', 'date_out', 'isServiceRoutine'])
            ->each(function (Maintenance $m) use ($buckets): void {
                $date = $m->date_out ?? $m->date_in;
                $key = $date?->format('Y-m');

                if ($key === null || ! $buckets->has($key)) {
                    return;
                }

                $bucket = $buckets->get($key);
                $bucket['maintenance'] += (float) $m->total_amount;
                $m->isServiceRoutine ? $bucket['routine']++ : $bucket['non_routine']++;
                $buckets->put($key, $bucket);
            });

        Record::whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->get(['date', 'idr', 'totals', 'bbm'])
            ->each(function (Record $r) use ($buckets): void {
                $key = $r->date?->format('Y-m');

                if ($key === null || ! $buckets->has($key)) {
                    return;
                }

                $bucket = $buckets->get($key);
                $bucket['fuel'] += $this->toNumber($r->idr);
                $bucket['km'] += $this->toNumber($r->totals);
                $bucket['bbm'] += $this->toNumber($r->bbm);
                $buckets->put($key, $bucket);
            });

        return $buckets->values();
    }

    /**
     * @return array{value: float, formatted: string, delta: ?float}
     */
    private function statBlock(float $current, ?float $previous): array
    {
        $delta = ($previous !== null && $previous > 0)
            ? round((($current - $previous) / $previous) * 100, 1)
            : null;

        return [
            'value' => $current,
            'formatted' => 'Rp '.number_format($current, 0, ',', '.'),
            'delta' => $delta,
        ];
    }

    /**
     * Normalize a stored numeric string (which may contain separators) to a float.
     */
    private function toNumber(?string $value): float
    {
        return (float) preg_replace('/\D/', '', (string) $value);
    }
}
