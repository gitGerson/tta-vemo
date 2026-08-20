<?php

namespace App\DataTables;

use App\Models\TypeMaintenances;
use Illuminate\Database\Eloquent\Builder;

class TypeMaintenanceTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return TypeMaintenances::onlyTrashed()->select([
            'id',
            'name',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'type-maintenances-trash-table';
    }

    public function ajax(): string
    {
        return route('type-maintenances.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('type-maintenances.restore', $row),
                'permission' => 'master.type_maintenances.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('type-maintenances.force-delete', $row),
                'permission' => 'master.type_maintenances.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen tipe maintenance ini?',
            ],
        ];
    }
}
