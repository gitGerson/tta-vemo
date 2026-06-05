<?php

namespace App\DataTables;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Maintenance::onlyTrashed()->select([
            'id',
            'code_mt',
            'no_pol',
            'date_in',
            'total_amount',
            'status',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('code_mt', 'Kode'),
            Column::make('no_pol', 'No Polisi'),
            Column::computed('date_in_label', 'Tanggal Masuk', fn (Maintenance $row): string => $row->date_in?->translatedFormat('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('total_label', 'Total', fn (Maintenance $row): string => 'Rp '.number_format((float) $row->total_amount, 0, ',', '.'))
                ->searchable(false),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'maintenances-trash-table';
    }

    public function ajax(): string
    {
        return route('maintenances.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('maintenances.restore', $row),
                'permission' => 'operational.maintenances.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('maintenances.force-delete', $row),
                'permission' => 'operational.maintenances.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen data pemeliharaan ini?',
            ],
        ];
    }
}
