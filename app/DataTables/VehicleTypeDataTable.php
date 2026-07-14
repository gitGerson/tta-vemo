<?php

namespace App\DataTables;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Builder;

class VehicleTypeDataTable extends DataTable
{
    public function query(): Builder
    {
        return VehicleType::query()->select([
            'id',
            'code',
            'name',
            'descriptions',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('code', 'Kode'),
            Column::make('name', 'Nama'),
            Column::make('descriptions', 'Deskripsi'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'vehicle-types-table';
    }

    public function ajax(): string
    {
        return route('vehicle-types.data');
    }

    public function clickableRoute(): ?string
    {
        return 'vehicle-types.show';
    }

    public function detailModal(): ?string
    {
        return 'vehicle-type-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('vehicle-types.show', $row),
                'permission' => 'master.vehicle_types.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.vehicle_types.edit',
                'class' => 'btn-edit-vehicle-type',
                'data' => [
                    'update-url' => route('vehicle-types.update', $row),
                    'code' => $row->code,
                    'name' => $row->name,
                    'descriptions' => $row->descriptions,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('vehicle-types.destroy', $row),
                'permission' => 'master.vehicle_types.delete',
                'confirm' => 'Yakin ingin menghapus vehicle type ini?',
            ],
        ];
    }
}
