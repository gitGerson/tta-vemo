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
            ->with(['company', 'driver1', 'driver2', 'vehicle.vehicleType'])
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
            Column::make('driver_1', 'Driver Utama'),
            Column::make('driver_2', 'Driver Pendamping'),
            Column::make('no_pol', 'No Polisi'),
            Column::computed('vehicle_type_label', 'Tipe Kendaraan', fn (Record $record): string => $record->vehicle?->vehicleType?->name ?? $record->vehicle?->type ?? '-')
                ->orderable(false)
                ->searchable(false),
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
                'type' => 'edit-modal',
                'permission' => 'operational.records.edit',
                'class' => 'btn-edit-record',
                'data' => [
                    'update-url' => route('records.update', $row),
                    'date' => $row->date?->format('d-m-Y'),
                    'company-id' => $row->company_id,
                    // NB: keys must avoid a digit right after a hyphen — `data-driver-1`
                    // maps to dataset['driver-1'], not dataset.driver1, so the JS read fails.
                    'driver1' => $row->driver1?->id,
                    'driver2' => $row->driver2?->id,
                    'no-pol' => $row->no_pol,
                    'destination' => $row->destination,
                    'start-km' => $row->start_km,
                    'finish-km' => $row->finish_km,
                    'bbm' => $row->bbm,
                    'fuel-type-id' => $row->fuel_type_id,
                ],
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
