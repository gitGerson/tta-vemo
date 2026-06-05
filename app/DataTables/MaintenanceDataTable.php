<?php

namespace App\DataTables;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceDataTable extends DataTable
{
    public function __construct(
        private ?string $startDate = null,
        private ?string $endDate = null,
        private ?string $status = null,
    ) {}

    public function query(): Builder
    {
        return Maintenance::query()
            ->withCount('details')
            ->when($this->startDate && $this->endDate, fn (Builder $q) => $q->whereBetween('date_in', [$this->startDate, $this->endDate]))
            ->when($this->status, fn (Builder $q) => $q->where('status', $this->status))
            ->latest();
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('no_pol', 'No Polisi'),
            Column::computed('date_in_label', 'Tanggal Masuk', fn (Maintenance $row): string => $row->date_in?->translatedFormat('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('date_out_label', 'Tanggal Keluar', fn (Maintenance $row): string => $row->date_out?->translatedFormat('d M Y') ?? 'Belum selesai')
                ->searchable(false),
            Column::make('mileage', 'KM'),
            Column::computed('status_label', 'Status', fn (Maintenance $row): string => $this->statusBadge($row))
                ->raw()
                ->searchable(false),
            Column::computed('details_count_label', 'Item Perbaikan', fn (Maintenance $row): string => '<span class="badge bg-label-primary">'.(int) $row->details_count.' item</span>')
                ->raw()
                ->searchable(false)
                ->orderable(false),
            Column::computed('total_label', 'Total Biaya', fn (Maintenance $row): string => 'Rp '.number_format((float) $row->total_amount, 0, ',', '.'))
                ->searchable(false),
            Column::make('description', 'Keterangan'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'maintenances-table';
    }

    public function ajax(): string
    {
        return route('maintenances.data', array_filter([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'status' => $this->status,
        ]));
    }

    public function clickableRoute(): ?string
    {
        return 'maintenances.show';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
                'url' => route('maintenances.show', $row),
                'permission' => 'operational.maintenances.view',
            ],
        ];
    }

    private function statusBadge(Maintenance $row): string
    {
        $badge = match ($row->status) {
            Maintenance::STATUS_DRAFT => '<span class="badge bg-secondary">DRAFT</span>',
            Maintenance::STATUS_COMPLETED => '<span class="badge bg-success">SELESAI</span>',
            Maintenance::STATUS_CANCELLED => '<span class="badge bg-danger">DIBATALKAN</span>',
            default => '<span class="badge bg-secondary">'.e($row->status).'</span>',
        };

        if ($row->isServiceRoutine) {
            $badge .= ' <span class="badge bg-label-info">Servis Rutin</span>';
        }

        return $badge;
    }
}
