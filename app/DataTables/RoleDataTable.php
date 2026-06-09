<?php

namespace App\DataTables;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleDataTable extends DataTable
{
    public function query(): Builder
    {
        return Role::query()
            ->select(['id', 'name', 'display_name', 'description', 'created_at'])
            ->withCount(['users', 'permissions']);
    }

    public function columns(): array
    {
        return [
            Column::make('id', 'ID'),
            Column::make('name', 'Nama Role'),
            Column::make('display_name', 'Display Name'),
            Column::computed('description', 'Deskripsi', fn (Role $role): string => $role->description ?: '-')
                ->searchable(false),
            Column::computed('users_count', 'Jumlah User', fn (Role $role): string => (string) $role->users_count)
                ->searchable(false)
                ->orderable(false),
            Column::computed('permissions_count', 'Jumlah Permission', fn (Role $role): string => (string) $role->permissions_count)
                ->searchable(false)
                ->orderable(false),
            Column::computed('created_at_label', 'Dibuat', fn (Role $role): string => $role->created_at?->format('d M Y H:i') ?? '-')
                ->searchable(false),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'roles-table';
    }

    public function ajax(): string
    {
        return route('roles.data');
    }

    public function clickableRoute(): ?string
    {
        return 'roles.show';
    }

    public function actions(mixed $row): array
    {
        $actions = [
            [
                'type' => 'edit-link',
                'url' => route('roles.edit', $row),
                'permission' => 'management.roles.edit',
            ],
        ];

        if ((int) $row->users_count === 0) {
            $actions[] = [
                'type' => 'delete',
                'url' => route('roles.destroy', $row),
                'permission' => 'management.roles.delete',
                'confirm' => 'Yakin ingin menghapus role ini?',
            ];
        } else {
            $actions[] = [
                'type' => 'disabled',
                'label' => 'Tidak dapat dihapus (sedang digunakan)',
                'icon' => 'bx-trash',
            ];
        }

        return $actions;
    }
}
