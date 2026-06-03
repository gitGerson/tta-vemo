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
            'code',
            'province_code',
            'name',
            'meta',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('code', 'Kode'),
            Column::make('name', 'Kota/Kabupaten'),
            Column::make('province_code', 'Kode Provinsi'),
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
        ];
    }
}
