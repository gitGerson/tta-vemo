<?php

namespace App\DataTables;

use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Database\Eloquent\Builder;

class VehicleInsuranceDataTable extends DataTable
{
    public function __construct(private ?Vehicle $vehicle = null) {}

    public function query(): Builder
    {
        $query = VehicleInsurance::query()->with('vehicle');

        if ($this->vehicle !== null) {
            $query->where('vehicle_id', $this->vehicle->getKey());
        }

        return $query->latest('end_date');
    }

    public function columns(): array
    {
        $columns = [
            Column::index(),
            Column::make('code', 'Kode'),
            Column::make('insurer_name', 'Perusahaan Asuransi'),
            Column::make('policy_number', 'Nomor Polis'),
            Column::make('insurance_type', 'Jenis Asuransi'),
        ];

        if ($this->vehicle === null) {
            $columns[] = Column::computed('vehicle_plat', 'Kendaraan', fn (VehicleInsurance $insurance): string => $insurance->vehicle?->plat_new ?? '-')
                ->orderable(false)
                ->searchable(false);
        }

        return array_merge($columns, [
            Column::computed('start_date_label', 'Tgl Mulai', fn (VehicleInsurance $insurance): string => $insurance->start_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('end_date_label', 'Tgl Berakhir', fn (VehicleInsurance $insurance): string => $insurance->end_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('coverage_value_label', 'Nilai Pertanggungan', fn (VehicleInsurance $insurance): string => $insurance->coverage_value !== null ? 'Rp '.number_format((float) $insurance->coverage_value, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('premium_label', 'Premi', fn (VehicleInsurance $insurance): string => $insurance->premium !== null ? 'Rp '.number_format((float) $insurance->premium, 0, ',', '.') : '-')
                ->searchable(false),
            Column::action(),
        ]);
    }

    public function id(): string
    {
        return $this->vehicle !== null
            ? 'vehicle-'.$this->vehicle->getKey().'-insurances-table'
            : 'vehicle-insurances-table';
    }

    public function ajax(): string
    {
        return $this->vehicle !== null
            ? route('vehicles.insurances.data', $this->vehicle)
            : route('vehicle-insurances.data');
    }

    public function clickableRoute(): ?string
    {
        return 'vehicle-insurances.show';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
                'url' => route('vehicle-insurances.show', $row),
                'permission' => 'master.vehicle_insurances.view',
            ],
            [
                'type' => 'edit-link',
                'url' => route('vehicle-insurances.edit', $row),
                'permission' => 'master.vehicle_insurances.edit',
            ],
            [
                'type' => 'delete',
                'url' => route('vehicle-insurances.destroy', $row),
                'permission' => 'master.vehicle_insurances.delete',
                'confirm' => 'Yakin ingin menghapus data asuransi ini?',
            ],
        ];
    }
}
