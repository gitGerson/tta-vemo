<?php

namespace App\DataTables;

use App\Models\VehicleBrand;
use Illuminate\Database\Eloquent\Builder;

class VehicleBrandDataTable extends DataTable
{
    public function query(): Builder
    {
        return VehicleBrand::query()->select([
            'id',
            'code',
            'name',
            'logo',
            'descriptions',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('logo_preview', 'Logo', function (VehicleBrand $vehicleBrand): string {
                $logoUrl = $vehicleBrand->logoUrl();

                if (! $logoUrl) {
                    return '-';
                }

                return '<img src="'.e($logoUrl).'" alt="'.e($vehicleBrand->name).'" class="rounded border" style="height: 32px; width: auto;">';
            })
                ->orderable(false)
                ->searchable(false)
                ->raw(),
            Column::make('code', 'Kode'),
            Column::make('name', 'Nama'),
            Column::make('descriptions', 'Deskripsi'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'vehicle-brands-table';
    }

    public function ajax(): string
    {
        return route('vehicle-brands.data');
    }

    public function clickableRoute(): ?string
    {
        return 'vehicle-brands.show';
    }

    public function detailModal(): ?string
    {
        return 'vehicle-brand-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('vehicle-brands.show', $row),
                'permission' => 'master.vehicle_brands.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.vehicle_brands.edit',
                'class' => 'btn-edit-vehicle-brand',
                'data' => [
                    'update-url' => route('vehicle-brands.update', $row),
                    'code' => $row->code,
                    'name' => $row->name,
                    'logo-url' => $row->logoUrl(),
                    'descriptions' => $row->descriptions,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('vehicle-brands.destroy', $row),
                'permission' => 'master.vehicle_brands.delete',
                'confirm' => 'Yakin ingin menghapus vehicle brand ini?',
            ],
        ];
    }
}
