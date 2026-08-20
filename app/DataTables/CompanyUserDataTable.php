<?php

namespace App\DataTables;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CompanyUserDataTable extends DataTable
{
    public function __construct(private readonly Company $company) {}

    public function query(): Builder
    {
        return User::query()
            ->with('roles')
            ->whereHas('companies', fn (Builder $query): Builder => $query->whereKey($this->company->getKey()))
            ->select([
                'id',
                'name',
                'email',
                'username',
                'created_at',
            ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::make('username', 'Username'),
            Column::make('email', 'Email'),
            Column::computed('roles_label', 'Role', fn (User $user): string => $user->roles->pluck('display_name')->join(', ') ?: '-')
                ->orderable(false)
                ->searchable(false),
            Column::computed('created_at_label', 'Dibuat Pada', fn (User $user): string => $user->created_at?->format('d M Y H:i') ?? '-')
                ->searchable(false),
        ];
    }

    public function id(): string
    {
        return 'company-'.$this->company->getKey().'-users-table';
    }

    public function ajax(): string
    {
        return route('companies.users.data', $this->company);
    }
}
