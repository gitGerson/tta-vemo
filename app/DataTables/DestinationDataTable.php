<?php

namespace App\DataTables;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;

class DestinationDataTable extends DataTable
{
    public function query(): Builder
    {
        return Destination::query()->select([
            'id',
            'destination',
            'desc',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('destination', 'Tujuan'),
            Column::make('desc', 'Keterangan'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'destinations-table';
    }

    public function ajax(): string
    {
        return route('destinations.data');
    }

    public function clickableRoute(): ?string
    {
        return 'destinations.show';
    }

    public function detailModal(): ?string
    {
        return 'destination-detail-modal';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'detail-modal',
                'url' => route('destinations.show', $row),
                'permission' => 'master.destinations.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.destinations.edit',
                'class' => 'btn-edit-destination',
                'data' => [
                    'update-url' => route('destinations.update', $row),
                    'destination' => $row->destination,
                    'desc' => $row->desc,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('destinations.destroy', $row),
                'permission' => 'master.destinations.delete',
                'confirm' => 'Yakin ingin menghapus tujuan ini?',
            ],
        ];
    }
}
