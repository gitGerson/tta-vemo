<?php

namespace App\DataTables;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;

class DestinationTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Destination::query()->whereRaw('1 = 0')->select([
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
        ];
    }

    public function id(): string
    {
        return 'destinations-trash-table';
    }

    public function ajax(): string
    {
        return route('destinations.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [];
    }
}
