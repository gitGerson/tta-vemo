<?php

namespace App\DataTables;

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

class CompanyVehicleDataTable extends DataTable
{
    public function __construct(private readonly Company $company) {}

    public function query(): Builder
    {
        return Vehicle::query()
            ->with(['fuelType', 'vehicleType', 'vehicleBrand'])
            ->whereBelongsTo($this->company)
            ->select([
                'id',
                'company_id',
                'fuel_type_id',
                'vehicle_type_id',
                'vehicle_brand_id',
                'type',
                'entry_year',
                'mileage',
                'plat_new',
                'status',
                'fuel_type',
                'mtc',
                'isprivate',
                'machine_number',
                'vin_number',
            ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('vehicle_type_label', 'Tipe', fn (Vehicle $vehicle): string => $vehicle->vehicleType?->name ?? $vehicle->type)
                ->orderable(false)
                ->searchable(false),
            Column::computed('vehicle_brand_label', 'Brand', fn (Vehicle $vehicle): string => $vehicle->vehicleBrand?->name ?? '-')
                ->orderable(false)
                ->searchable(false),
            Column::make('plat_new', 'Plat Nomor'),
            Column::make('machine_number', 'No. Mesin'),
            Column::make('vin_number', 'VIN'),
            Column::computed('fuel_type_label', 'Bahan Bakar', fn (Vehicle $vehicle): string => $vehicle->fuelType?->name ?? $vehicle->fuel_type)
                ->orderable(false)
                ->searchable(false),
            Column::make('mileage', 'Kilometer'),
            Column::make('entry_year', 'Tahun Masuk'),
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
        ];
    }

    public function id(): string
    {
        return 'company-'.$this->company->getKey().'-vehicles-table';
    }

    public function ajax(): string
    {
        return route('companies.vehicles.data', $this->company);
    }
}
