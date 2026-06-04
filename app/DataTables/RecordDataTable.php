<?php

namespace App\DataTables;

use App\Models\Record;
use Illuminate\Database\Eloquent\Builder;

class RecordDataTable extends DataTable
{
    public function __construct(
        private ?string $startDate = null,
        private ?string $endDate = null,
        private ?int $companyId = null,
    ) {}

    public function query(): Builder
    {
        return Record::query()
            ->with('company')
            ->when($this->startDate && $this->endDate, fn (Builder $q) => $q->whereBetween('date', [$this->startDate, $this->endDate]))
            ->when($this->companyId, fn (Builder $q) => $q->where('company_id', $this->companyId))
            ->latest();
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('date_label', 'Tanggal', fn (Record $record): string => $record->date?->translatedFormat('d M Y') ?? '-')
                ->searchable(false),
            Column::make('driver_1', 'Supir 1'),
            Column::make('driver_2', 'Supir 2'),
            Column::make('no_pol', 'No Polisi'),
            Column::make('destination', 'Tujuan'),
            Column::make('start_km', 'KM Awal'),
            Column::make('finish_km', 'KM Akhir'),
            Column::make('totals', 'Total'),
            Column::make('bbm', 'BBM'),
            Column::computed('idr_label', 'IDR', fn (Record $record): string => 'Rp '.number_format((float) $record->idr, 0, ',', '.'))
                ->searchable(false),
            Column::make('scale', 'Skala'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'records-table';
    }

    public function ajax(): string
    {
        return route('records.data', array_filter([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'company' => $this->companyId,
        ]));
    }

    public function clickableRoute(): ?string
    {
        return 'records.show';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
                'url' => route('records.show', $row),
                'permission' => 'operational.records.view',
            ],
            [
                'type' => 'edit-link',
                'url' => route('records.edit', $row),
                'permission' => 'operational.records.edit',
            ],
            [
                'type' => 'delete',
                'url' => route('records.destroy', $row),
                'permission' => 'operational.records.delete',
                'confirm' => 'Yakin ingin menghapus catatan perjalanan ini?',
            ],
        ];
    }
}
