<?php

namespace App\DataTables;

use App\Models\VehicleInsurance;
use Illuminate\Database\Eloquent\Builder;

class VehicleInsuranceTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return VehicleInsurance::onlyTrashed()->with('vehicle')->select([
            'id',
            'code',
            'vehicle_id',
            'insurer_name',
            'policy_number',
            'insurance_type',
            'end_date',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('code', 'Kode'),
            Column::computed('vehicle_plat', 'Kendaraan', fn (VehicleInsurance $insurance): string => $insurance->vehicle?->plat_new ?? '-')
                ->orderable(false)
                ->searchable(false),
            Column::make('insurer_name', 'Perusahaan Asuransi'),
            Column::make('policy_number', 'Nomor Polis'),
            Column::make('insurance_type', 'Jenis Asuransi'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'vehicle-insurances-trash-table';
    }

    public function ajax(): string
    {
        return route('vehicle-insurances.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('vehicle-insurances.restore', $row),
                'permission' => 'master.vehicle_insurances.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('vehicle-insurances.force-delete', $row),
                'permission' => 'master.vehicle_insurances.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen data asuransi ini?',
            ],
        ];
    }
}
