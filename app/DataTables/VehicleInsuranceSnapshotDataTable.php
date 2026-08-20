<?php

namespace App\DataTables;

use App\Models\VehicleInsurance;
use App\Models\VehicleInsuranceSnapshot;
use Illuminate\Database\Eloquent\Builder;

class VehicleInsuranceSnapshotDataTable extends DataTable
{
    public function __construct(private VehicleInsurance $insurance) {}

    public function query(): Builder
    {
        return VehicleInsuranceSnapshot::query()
            ->with('changedBy')
            ->where('vehicle_insurance_id', $this->insurance->getKey())
            ->latest();
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('snapshot_at', 'Tanggal', fn (VehicleInsuranceSnapshot $snapshot): string => $snapshot->created_at->format('d M Y H:i'))
                ->searchable(false),
            Column::make('insurer_name', 'Perusahaan Asuransi'),
            Column::make('policy_number', 'Nomor Polis'),
            Column::make('insurance_type', 'Jenis Asuransi'),
            Column::computed('start_date_label', 'Tgl Mulai', fn (VehicleInsuranceSnapshot $snapshot): string => $snapshot->start_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('end_date_label', 'Tgl Berakhir', fn (VehicleInsuranceSnapshot $snapshot): string => $snapshot->end_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('coverage_value_label', 'Nilai Pertanggungan', fn (VehicleInsuranceSnapshot $snapshot): string => $snapshot->coverage_value !== null ? 'Rp '.number_format((float) $snapshot->coverage_value, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('premium_label', 'Premi', fn (VehicleInsuranceSnapshot $snapshot): string => $snapshot->premium !== null ? 'Rp '.number_format((float) $snapshot->premium, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('changed_by_name', 'Oleh', fn (VehicleInsuranceSnapshot $snapshot): string => $snapshot->changedBy?->name ?? '-')
                ->searchable(false),
        ];
    }

    public function id(): string
    {
        return 'vehicle-insurance-snapshot-'.$this->insurance->getKey().'-table';
    }

    public function ajax(): string
    {
        return route('vehicle-insurances.snapshots.data', $this->insurance);
    }
}
