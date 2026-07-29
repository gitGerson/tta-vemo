<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VehicleDocumentMonitorService
{
    /**
     * Document due-date reminders, keyed by document. Each entry defines the lead time
     * (days before the due date) at which the reminder starts firing and the labels used
     * to render it. STNK is flagged H-30 (≈1 month before), KIR H-7.
     *
     * @var array<string, array{column: string, threshold: int, label: string, type: string}>
     */
    public const DOCUMENTS = [
        'stnk' => ['column' => 'stnk_due_date', 'threshold' => 30, 'label' => 'STNK', 'type' => 'vehicle.stnk-due'],
        'kir' => ['column' => 'kir_due_date', 'threshold' => 7, 'label' => 'KIR', 'type' => 'vehicle.kir-due'],
    ];

    /**
     * Vehicles whose STNK/KIR due date falls within its reminder lead window
     * (any overdue date .. today + threshold).
     * Company-scoped via the Vehicle CompanyTenantScope (bypassed by
     * management.tenant.bypass). Sorted most-urgent first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function dueDocuments(?int $companyId = null): Collection
    {
        $today = Carbon::today();

        return collect(self::DOCUMENTS)
            ->flatMap(fn (array $document): Collection => $this->dueForDocument($document, $today, $companyId))
            ->sortBy('days_left')
            ->values();
    }

    /**
     * @param  array{column: string, threshold: int, label: string, type: string}  $document
     * @return Collection<int, array<string, mixed>>
     */
    private function dueForDocument(array $document, Carbon $today, ?int $companyId): Collection
    {
        return Vehicle::query()
            ->whereNotNull($document['column'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereDate($document['column'], '<=', $today->copy()->addDays($document['threshold'])->toDateString())
            ->get(['id', 'plat_new', $document['column']])
            ->map(function (Vehicle $vehicle) use ($document, $today): array {
                $dueDate = $vehicle->{$document['column']}->copy()->startOfDay();
                $daysLeft = (int) $today->diffInDays($dueDate, false);
                $isOverdue = $daysLeft < 0;

                return [
                    'vehicle_id' => $vehicle->id,
                    'document' => $document['label'],
                    'type' => $document['type'],
                    'plate' => $vehicle->plat_new,
                    'due_date' => $dueDate->format('d M Y'),
                    'days_left' => $daysLeft,
                    'days_overdue' => $isOverdue ? abs($daysLeft) : 0,
                    'state' => $isOverdue ? 'overdue' : 'upcoming',
                    // The due date is part of the notification key so a renewal (new date)
                    // re-fires the reminder even after the prior one was dismissed.
                    'threshold_value' => $dueDate->toDateString(),
                    'link' => route('vehicles.show', $vehicle),
                ];
            });
    }
}
