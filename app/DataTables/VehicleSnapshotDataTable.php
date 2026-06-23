<?php

namespace App\DataTables;

use App\Models\Vehicle;
use App\Models\VehicleSnapshot;
use Illuminate\Database\Eloquent\Builder;

class VehicleSnapshotDataTable extends DataTable
{
    public function __construct(private Vehicle $vehicle) {}

    public function query(): Builder
    {
        return VehicleSnapshot::query()
            ->with('changedBy')
            ->where('vehicle_id', $this->vehicle->getKey())
            ->latest();
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::computed('snapshot_at', 'Tanggal', fn (VehicleSnapshot $snapshot): string => $snapshot->created_at->format('d M Y H:i'))
                ->searchable(false),
            Column::make('plat_new', 'Plat Nomor'),
            Column::make('type', 'Tipe'),
            Column::make('company_name', 'Perusahaan'),
            Column::make('fuel_type', 'Bahan Bakar'),
            Column::computed('status_badges', 'Status', function (VehicleSnapshot $snapshot): string {
                $statusClass = $snapshot->status === 'ACTIVE'
                    ? 'bg-label-success text-success'
                    : 'bg-label-secondary text-secondary';

                $statusLabel = $snapshot->status === 'ACTIVE' ? 'Aktif' : 'Pasif';
                $badges = ['<span class="badge '.$statusClass.'">'.e($statusLabel).'</span>'];

                if ($snapshot->mtc === 'YES') {
                    $badges[] = '<span class="badge bg-label-warning text-warning">Dalam Pemeliharaan</span>';
                }

                $badges[] = $snapshot->isprivate
                    ? '<span class="badge bg-label-info text-info">Kendaraan Pribadi</span>'
                    : '<span class="badge bg-label-secondary text-secondary">Kendaraan Umum</span>';

                return '<div class="d-flex flex-wrap gap-1">'.implode('', $badges).'</div>';
            })
                ->orderable(false)
                ->searchable(false)
                ->raw(),
            Column::make('machine_number', 'No. Mesin'),
            Column::make('vin_number', 'VIN'),
            Column::computed('purchase_price_label', 'Harga Beli', fn (VehicleSnapshot $snapshot): string => $snapshot->purchase_price !== null ? 'Rp '.number_format((float) $snapshot->purchase_price, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('selling_price_label', 'Harga Jual', fn (VehicleSnapshot $snapshot): string => $snapshot->selling_price !== null ? 'Rp '.number_format((float) $snapshot->selling_price, 0, ',', '.') : '-')
                ->searchable(false),
            Column::computed('purchase_date_label', 'Tgl Beli', fn (VehicleSnapshot $snapshot): string => $snapshot->purchase_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('sale_date_label', 'Tgl Jual', fn (VehicleSnapshot $snapshot): string => $snapshot->sale_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::computed('changed_by_name', 'Oleh', fn (VehicleSnapshot $snapshot): string => $snapshot->changedBy?->name ?? '-')
                ->searchable(false),
        ];
    }

    public function id(): string
    {
        return 'vehicle-snapshot-'.$this->vehicle->getKey().'-table';
    }

    public function ajax(): string
    {
        return route('vehicles.snapshots.data', $this->vehicle);
    }
}
