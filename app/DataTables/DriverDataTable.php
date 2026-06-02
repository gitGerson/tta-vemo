<?php

namespace App\DataTables;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Builder;

class DriverDataTable extends DataTable
{
    public function query(): Builder
    {
        return Driver::query()->with('companies')->select([
            'id',
            'name',
            'address',
            'no_identity',
            'residence',
            'date_birth',
            'no_phone_1',
            'no_phone_2',
            'entry_date',
            'out_date',
            'status',
            'image',
            'nik',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::computed('companies_label', 'Perusahaan', fn (Driver $driver): string => $driver->companies->pluck('name')->join(', ') ?: '-')
                ->orderable(false)
                ->searchable(false),
            Column::make('no_identity', 'No Identitas'),
            Column::make('no_phone_1', 'No Telepon'),
            Column::make('status', 'Status'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'drivers-table';
    }

    public function ajax(): string
    {
        return route('drivers.data');
    }

    public function clickableRoute(): ?string
    {
        return 'drivers.show';
    }

    public function detailModal(): ?string
    {
        return 'driver-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('drivers.show', $row),
                'permission' => 'master.drivers.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.drivers.edit',
                'class' => 'btn-edit-driver',
                'data' => [
                    'update-url' => route('drivers.update', $row),
                    'name' => $row->name,
                    'nik' => $row->nik,
                    'no-identity' => $row->no_identity,
                    'residence' => $row->residence,
                    'date-birth' => $row->date_birth?->format('Y-m-d'),
                    'no-phone-1' => $row->no_phone_1,
                    'no-phone-2' => $row->no_phone_2,
                    'address' => $row->address,
                    'entry-date' => $row->entry_date?->format('Y-m-d'),
                    'out-date' => $row->out_date?->format('Y-m-d'),
                    'status' => $row->status,
                    'image-url' => $row->imageUrl(),
                    'company-ids' => $row->companies->pluck('id')->join(','),
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('drivers.destroy', $row),
                'permission' => 'master.drivers.delete',
                'confirm' => 'Yakin ingin menghapus driver ini?',
            ],
        ];
    }
}
