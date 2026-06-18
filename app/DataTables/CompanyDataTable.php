<?php

namespace App\DataTables;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

class CompanyDataTable extends DataTable
{
    public function query(): Builder
    {
        return Company::query()->select([
            'id',
            'code',
            'name',
            'email',
            'phone',
            'image',
            'notes',
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
            Column::make('notes', 'Keterangan'),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'companies-table';
    }

    public function ajax(): string
    {
        return route('companies.data');
    }

    public function clickableRoute(): ?string
    {
        return 'companies.show';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
                'url' => route('companies.show', $row),
                'permission' => 'master.companies.view',
            ],
            [
                'type' => 'edit-modal',
                'permission' => 'master.companies.edit',
                'class' => 'btn-edit-company',
                'data' => [
                    'update-url' => route('companies.update', $row),
                    'code' => $row->code,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'image' => $row->image,
                    'notes' => $row->notes,
                ],
            ],
            [
                'type' => 'delete',
                'url' => route('companies.destroy', $row),
                'permission' => 'master.companies.delete',
                'confirm' => 'Yakin ingin menghapus perusahaan ini?',
            ],
        ];
    }
}
