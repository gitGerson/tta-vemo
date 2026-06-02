<?php

namespace App\DataTables;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Builder;

class DriverDataTable extends DataTable
{
    public function query(): Builder
    {
        return Driver::query()->select([
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

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
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
