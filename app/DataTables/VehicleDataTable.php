<?php

namespace App\DataTables;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

class VehicleDataTable extends DataTable
{
    public function query(): Builder
    {
        return Vehicle::query()->with('company')->select([
            'id',
            'company_id',
            'type',
            'entry_year',
            'mileage',
            'plat_new',
            'plat_old',
            'status',
            'fuel_type',
            'mtc',
            'desc',
            'isprivate',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('type', 'Tipe'),
            Column::computed('company_name', 'Perusahaan', fn (Vehicle $vehicle): string => $vehicle->company?->name ?? '-')
                ->orderable(false)
                ->searchable(false),
            Column::make('plat_new', 'Plat Baru'),
            Column::make('plat_old', 'Plat Lama'),
            Column::make('status', 'Status'),
            Column::make('fuel_type', 'Bahan Bakar'),
            Column::make('mtc', 'MTC'),
            Column::computed('isprivate_label', 'Private', fn (Vehicle $vehicle): string => $vehicle->isprivate ? 'Yes' : 'No')
                ->orderable(false)
                ->searchable(false),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'vehicles-table';
    }

    public function ajax(): string
    {
        return route('vehicles.data');
    }

    public function clickableRoute(): ?string
    {
        return 'vehicles.show';
    }

    public function detailModal(): ?string
    {
        return 'vehicle-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('vehicles.show', $row),
                'permission' => 'master.vehicles.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.vehicles.edit',
                'class' => 'btn-edit-vehicle',
                'data' => [
                    'update-url' => route('vehicles.update', $row),
                    'type' => $row->type,
                    'entry-year' => $row->entry_year,
                    'mileage' => $row->mileage,
                    'plat-new' => $row->plat_new,
                    'plat-old' => $row->plat_old,
                    'status' => $row->status,
                    'fuel-type' => $row->fuel_type,
                    'mtc' => $row->mtc,
                    'isprivate' => $row->isprivate ? 1 : 0,
                    'desc' => $row->desc,
                    'company-id' => $row->company_id,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('vehicles.destroy', $row),
                'permission' => 'master.vehicles.delete',
                'confirm' => 'Yakin ingin menghapus kendaraan ini?',
            ],
        ];
    }
}
