<?php

namespace App\DataTables;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Database\Eloquent\Builder;

class VehiclePhotoDataTable extends DataTable
{
    public function __construct(private ?Vehicle $vehicle = null) {}

    public function query(): Builder
    {
        $query = VehiclePhoto::query()->with('vehicle');

        if ($this->vehicle !== null) {
            $query->where('vehicle_id', $this->vehicle->getKey());
        }

        return $query->latest('period');
    }

    public function columns(): array
    {
        $columns = [
            Column::index(),
            Column::computed('period_label', 'Bulan', fn (VehiclePhoto $photo): string => $photo->period?->translatedFormat('F Y') ?? '-')
                ->searchable(false),
            Column::computed('completeness', 'Kelengkapan', fn (VehiclePhoto $photo): string => sprintf(
                '<span class="badge %s">%d/%d %s</span>',
                $photo->isComplete() ? 'bg-label-success' : 'bg-label-warning',
                $photo->completedCount(),
                count(VehiclePhoto::SIDES),
                $photo->isComplete() ? 'Lengkap' : 'Belum lengkap',
            ))->searchable(false)->raw(),
        ];

        if ($this->vehicle === null) {
            $columns[] = Column::computed('vehicle_plat', 'Kendaraan', fn (VehiclePhoto $photo): string => $photo->vehicle?->plat_new ?? '-')
                ->orderable(false)
                ->searchable(false);
        }

        return array_merge($columns, [
            Column::make('notes', 'Keterangan'),
            Column::computed('created_at_label', 'Diunggah', fn (VehiclePhoto $photo): string => $photo->created_at?->format('d M Y H:i') ?? '-')
                ->searchable(false),
            Column::action(),
        ]);
    }

    public function id(): string
    {
        return $this->vehicle !== null
            ? 'vehicle-'.$this->vehicle->getKey().'-photos-table'
            : 'vehicle-photos-table';
    }

    public function ajax(): string
    {
        return route('vehicles.photos.data', $this->vehicle);
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'edit-link',
                'url' => route('vehicle-photos.edit', $row),
                'permission' => 'operational.vehicle_photos.edit',
            ],
            [
                'type' => 'delete',
                'url' => route('vehicle-photos.destroy', $row),
                'permission' => 'operational.vehicle_photos.delete',
                'confirm' => 'Yakin ingin menghapus foto kendaraan ini?',
            ],
        ];
    }
}
