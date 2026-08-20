<?php

namespace App\DataTables;

use App\Models\VehicleBrand;
use Illuminate\Database\Eloquent\Builder;

class VehicleBrandTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return VehicleBrand::onlyTrashed()->select([
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
        return 'vehicle-brands-trash-table';
    }

    public function ajax(): string
    {
        return route('vehicle-brands.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('vehicle-brands.restore', $row),
                'permission' => 'master.vehicle_brands.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('vehicle-brands.force-delete', $row),
                'permission' => 'master.vehicle_brands.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen vehicle brand ini?',
            ],
        ];
    }
}
