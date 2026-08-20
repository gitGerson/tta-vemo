<?php

namespace App\DataTables;

use App\Models\Record;
use Illuminate\Database\Eloquent\Builder;

class RecordTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Record::onlyTrashed()->select([
            'id',
            'driver_1',
            'no_pol',
            'destination',
            'totals',
            'date',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('date_label', 'Tanggal', fn (Record $record): string => $record->date?->translatedFormat('d M Y') ?? '-')
                ->searchable(false),
            Column::make('driver_1', 'Supir 1'),
            Column::make('no_pol', 'No Polisi'),
            Column::make('destination', 'Tujuan'),
            Column::make('totals', 'Total'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'records-trash-table';
    }

    public function ajax(): string
    {
        return route('records.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('records.restore', $row),
                'permission' => 'operational.records.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('records.force-delete', $row),
                'permission' => 'operational.records.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen catatan perjalanan ini?',
            ],
        ];
    }
}
