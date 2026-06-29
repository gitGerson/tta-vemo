<?php

namespace App\DataTables;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

class CompanyTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return Company::onlyTrashed()->select([
            'id',
            'code',
            'name',
            'email',
            'phone',
            'deleted_at',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('code', 'Kode'),
            Column::make('name', 'Nama'),
            Column::make('email', 'Email'),
            Column::make('phone', 'Telepon'),
            Column::make('deleted_at', 'Dihapus Pada'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'companies-trash-table';
    }

    public function ajax(): string
    {
        return route('companies.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('companies.restore', $row),
                'permission' => 'master.companies.restore',
                'label' => 'Restore',
            ],
            [
                'type' => 'force-delete',
                'url' => route('companies.force-delete', $row),
                'permission' => 'master.companies.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen perusahaan ini?',
            ],
        ];
    }
}
