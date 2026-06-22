<?php

namespace App\DataTables;

use App\Models\FuelType;
use Illuminate\Database\Eloquent\Builder;

class FuelTypeTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return FuelType::onlyTrashed()->select([
            'id',
            'name',
            'type',
            'price',
            'merk',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::make('type', 'Tipe'),
            Column::computed('price_label', 'Harga', fn (FuelType $fuelType): string => 'Rp '.number_format((float) $fuelType->price, 0, ',', '.'))
                ->searchable(false),
            Column::make('merk', 'Merk'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'fuel-types-trash-table';
    }

    public function ajax(): string
    {
        return route('fuel-types.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('fuel-types.restore', $row),
                'permission' => 'master.fuel_types.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('fuel-types.force-delete', $row),
                'permission' => 'master.fuel_types.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen fuel type ini?',
            ],
        ];
    }
}
