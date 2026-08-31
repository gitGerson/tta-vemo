<?php

namespace App\DataTables;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;

class DestinationTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Destination::onlyTrashed()->select([
            'id',
            'name',
            'desc',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Tujuan'),
            Column::make('desc', 'Keterangan'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'destinations-trash-table';
    }

    public function ajax(): string
    {
        return route('destinations.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('destinations.restore', $row),
                'permission' => 'master.destinations.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('destinations.force-delete', $row),
                'permission' => 'master.destinations.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen tujuan ini?',
            ],
        ];
    }
}
