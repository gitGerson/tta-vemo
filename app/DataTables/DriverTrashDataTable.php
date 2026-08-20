<?php

namespace App\DataTables;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Builder;

class DriverTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Driver::onlyTrashed()->select([
            'id',
            'name',
            'no_identity',
            'no_phone_1',
            'status',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::make('no_identity', 'No Identitas'),
            Column::make('no_phone_1', 'No Telepon'),
            Column::make('status', 'Status'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'drivers-trash-table';
    }

    public function ajax(): string
    {
        return route('drivers.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('drivers.restore', $row),
                'permission' => 'master.drivers.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('drivers.force-delete', $row),
                'permission' => 'master.drivers.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen driver ini?',
            ],
        ];
    }
}
