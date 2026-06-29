<?php

namespace App\DataTables;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Builder;

class VehicleTypeTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return VehicleType::onlyTrashed()->select([
            'id',
            'code',
            'name',
            'descriptions',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('code', 'Kode'),
            Column::make('name', 'Nama'),
            Column::make('descriptions', 'Deskripsi'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'vehicle-types-trash-table';
    }

    public function ajax(): string
    {
        return route('vehicle-types.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('vehicle-types.restore', $row),
                'permission' => 'master.vehicle_types.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('vehicle-types.force-delete', $row),
                'permission' => 'master.vehicle_types.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen vehicle type ini?',
            ],
        ];
    }
}
