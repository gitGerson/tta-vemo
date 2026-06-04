<?php

namespace App\DataTables;

use App\Models\FuelType;
use Illuminate\Database\Eloquent\Builder;

class FuelTypeDataTable extends DataTable
{
    public function query(): Builder
    {
        return FuelType::query()->select([
            'id',
            'name',
            'type',
            'price',
            'merk',
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
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'fuel-types-table';
    }

    public function ajax(): string
    {
        return route('fuel-types.data');
    }

    public function clickableRoute(): ?string
    {
        return 'fuel-types.show';
    }

    public function detailModal(): ?string
    {
        return 'fuel-type-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('fuel-types.show', $row),
                'permission' => 'master.fuel_types.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.fuel_types.edit',
                'class' => 'btn-edit-fuel-type',
                'data' => [
                    'update-url' => route('fuel-types.update', $row),
                    'name' => $row->name,
                    'type' => $row->type,
                    'price' => $row->price,
                    'merk' => $row->merk,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('fuel-types.destroy', $row),
                'permission' => 'master.fuel_types.delete',
                'confirm' => 'Yakin ingin menghapus fuel type ini?',
            ],
        ];
    }
}
