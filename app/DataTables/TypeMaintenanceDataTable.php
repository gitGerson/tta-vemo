<?php

namespace App\DataTables;

use App\Models\TypeMaintenances;
use Illuminate\Database\Eloquent\Builder;

class TypeMaintenanceDataTable extends DataTable
{
    public function query(): Builder
    {
        return TypeMaintenances::query()->select([
            'id',
            'name',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'type-maintenances-table';
    }

    public function ajax(): string
    {
        return route('type-maintenances.data');
    }

    public function clickableRoute(): ?string
    {
        return 'type-maintenances.show';
    }

    public function detailModal(): ?string
    {
        return 'type-maintenance-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('type-maintenances.show', $row),
                'permission' => 'master.type_maintenances.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.type_maintenances.edit',
                'class' => 'btn-edit-type-maintenance',
                'data' => [
                    'update-url' => route('type-maintenances.update', $row),
                    'name' => $row->name,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('type-maintenances.destroy', $row),
                'permission' => 'master.type_maintenances.delete',
                'confirm' => 'Yakin ingin menghapus tipe maintenance ini?',
            ],
        ];
    }
}
