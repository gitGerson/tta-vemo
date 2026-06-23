<?php

namespace App\DataTables;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

class VehicleDataTable extends DataTable
{
    public function query(): Builder
    {
        return Vehicle::query()->with(['company', 'fuelType', 'vehicleType', 'vehicleBrand'])->select([
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
            'desc',
            'isprivate',
            'machine_number',
            'vin_number',
            'bpkb_image',
            'stnk_image',
            'purchase_price',
            'selling_price',
            'purchase_date',
            'sale_date',
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
            Column::computed('company_name', 'Perusahaan', fn (Vehicle $vehicle): string => $vehicle->company?->name ?? '-')
                ->orderable(false)
                ->searchable(false),
            Column::make('plat_new', 'Plat Nomor'),
            Column::make('machine_number', 'No. Mesin'),
            Column::make('vin_number', 'Nomor Rangka'),
            Column::computed('fuel_type_label', 'Bahan Bakar', fn (Vehicle $vehicle): string => $vehicle->fuelType?->name ?? $vehicle->fuel_type)
                ->orderable(false)
                ->searchable(false),
            Column::computed('purchase_price_label', 'Harga Beli', fn (Vehicle $vehicle): string => $vehicle->purchase_price !== null ? 'Rp '.number_format((float) $vehicle->purchase_price, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('selling_price_label', 'Harga Jual', fn (Vehicle $vehicle): string => $vehicle->selling_price !== null ? 'Rp '.number_format((float) $vehicle->selling_price, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('purchase_date_label', 'Tgl Beli', fn (Vehicle $vehicle): string => $vehicle->purchase_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('sale_date_label', 'Tgl Jual', fn (Vehicle $vehicle): string => $vehicle->sale_date?->format('d M Y') ?? '-')
                ->searchable(false),
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
                'type' => 'edit-link',
                'url' => route('vehicles.edit', $row),
                'permission' => 'master.vehicles.edit',
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
