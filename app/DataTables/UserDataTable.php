<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserDataTable extends DataTable
{
    public function query(): Builder
    {
        return User::query()
            ->with(['companies', 'roles'])
            ->select(['id', 'name', 'email', 'username']);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::make('username', 'Username'),
            Column::make('email', 'Email'),
            Column::computed('companies', 'Perusahaan', fn (User $user): string => $user->companies->pluck('name')->join(', ') ?: '-')
                ->orderable(false)
                ->searchable(false),
            Column::computed('roles', 'Role', fn (User $user): string => $user->roles->pluck('display_name')->join(', ') ?: '-')
                ->orderable(false)
                ->searchable(false),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'users-table';
    }

    public function ajax(): string
    {
        return route('users.data');
    }

    public function clickableRoute(): ?string
    {
        return 'users.show';
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'view',
                'url' => route('users.user-logs', $row),
                'permission' => 'management.users.logs',
                'label' => 'Activity Log',
                'icon' => 'bx-history',
            ],
            [
                'type' => 'edit-link',
                'url' => route('users.edit', $row),
                'permission' => 'management.users.edit',
            ],
            [
                'type' => 'delete',
                'url' => route('users.destroy', $row),
                'permission' => 'management.users.delete',
                'confirm' => 'Yakin ingin menghapus user ini?',
            ],
        ];
    }
}
