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
            Column::make('plat_new', 'Plat Nomor'),
            Column::make('fuel_type', 'Bahan Bakar'),
            Column::computed('status_badges', 'Status', function (Vehicle $vehicle): string {
                $statusClass = $vehicle->status === 'ACTIVE'
                    ? 'bg-label-success text-success'
                    : 'bg-label-secondary text-secondary';

                $badges = ['<span class="badge '.$statusClass.'">'.e($vehicle->status).'</span>'];

                if ($vehicle->mtc === 'YES') {
                    $badges[] = '<span class="badge bg-label-warning text-warning">Under Maintenance</span>';
                }

                $badges[] = $vehicle->isprivate
                    ? '<span class="badge bg-label-info text-info">Kendaraan Pribadi</span>'
                    : '<span class="badge bg-label-secondary text-secondary">Kendaraan Umum</span>';

                return '<div class="d-flex flex-wrap gap-1">'.implode('', $badges).'</div>';
            })
                ->orderable(false)
                ->searchable(false)
                ->raw(),
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

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
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
