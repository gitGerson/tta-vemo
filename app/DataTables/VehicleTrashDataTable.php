<?php

namespace App\DataTables;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

class VehicleTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Vehicle::onlyTrashed()->select([
            'id',
            'type',
            'plat_new',
            'status',
            'fuel_type',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('type', 'Tipe'),
            Column::make('plat_new', 'Plat Baru'),
            Column::make('status', 'Status'),
            Column::make('fuel_type', 'Bahan Bakar'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'vehicles-trash-table';
    }

    public function ajax(): string
    {
        return route('vehicles.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('vehicles.restore', $row),
                'permission' => 'master.vehicles.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('vehicles.force-delete', $row),
                'permission' => 'master.vehicles.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen kendaraan ini?',
            ],
        ];
    }
}
