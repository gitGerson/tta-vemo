<?php

namespace App\DataTables;

use App\Models\Record;
use App\Models\RecordSnapshot;
use Illuminate\Database\Eloquent\Builder;

class RecordSnapshotDataTable extends DataTable
{
    public function __construct(private Record $record) {}

    public function query(): Builder
    {
        return RecordSnapshot::query()
            ->with('changedBy')
            ->where('record_id', $this->record->getKey())
            ->latest();
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('snapshot_at', 'Tanggal Snapshot', fn (RecordSnapshot $snapshot): string => $snapshot->created_at->format('d M Y H:i'))
                ->searchable(false),
            Column::computed('date_label', 'Tanggal Record', fn (RecordSnapshot $snapshot): string => $snapshot->date?->translatedFormat('d M Y') ?? '-')
                ->searchable(false),
            Column::make('driver_1', 'Supir 1'),
            Column::make('driver_2', 'Supir 2'),
            Column::make('vehicle_plat', 'No Polisi'),
            Column::make('destination', 'Tujuan'),
            Column::make('company_name', 'Perusahaan'),
            Column::make('fuel_type_name', 'Jenis BBM'),
            Column::make('start_km', 'KM Awal'),
            Column::make('finish_km', 'KM Akhir'),
            Column::make('totals', 'Total'),
            Column::make('bbm', 'Jumlah BBM (Liter)'),
            Column::computed(
                'fuel_unit_price_label',
                'Harga per Liter',
                fn (RecordSnapshot $snapshot): string => $snapshot->fuel_unit_price === null
                    ? '-'
                    : 'Rp '.number_format((float) $snapshot->fuel_unit_price, 2, ',', '.'),
            )->searchable(false),
            Column::computed('idr_label', 'Total Harga', fn (RecordSnapshot $snapshot): string => 'Rp '.number_format((float) $snapshot->idr, 0, ',', '.'))
                ->searchable(false),
            Column::make('scale', 'Skala'),
            Column::computed('changed_by_name', 'Oleh', fn (RecordSnapshot $snapshot): string => $snapshot->changedBy?->name ?? '-')
                ->searchable(false),
        ];
    }

    public function id(): string
    {
        return 'record-snapshot-'.$this->record->getKey().'-table';
    }

    public function ajax(): string
    {
        return route('records.snapshots.data', $this->record);
    }
}
