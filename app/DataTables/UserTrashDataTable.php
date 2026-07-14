<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserTrashDataTable extends DataTable
{
    public function query(): Builder
    {
        return User::onlyTrashed()
            ->with('deleter')
            ->select(['id', 'name', 'username', 'email', 'deleted_at', 'deleted_by']);
    }

    public function columns(): array
    {
        return [
            Column::make('id', 'ID'),
            Column::make('name', 'Nama'),
            Column::make('username', 'Username'),
            Column::make('email', 'Email'),
            Column::computed('deleted_at_label', 'Dihapus', fn (User $user): string => $user->deleted_at?->format('d M Y H:i') ?? '-')
                ->searchable(false),
            Column::computed('deleted_by_name', 'Dihapus Oleh', fn (User $user): string => $user->deleter?->name ?? '-')
                ->searchable(false)
                ->orderable(false),
            Column::action(),
        ];
    }

    public function id(): string
    {
        return 'users-trash-table';
    }

    public function ajax(): string
    {
        return route('users.trash.data');
    }

    public function actions(mixed $row): array
    {
        return [
            [
                'type' => 'restore',
                'url' => route('users.restore', $row),
                'permission' => 'management.users.restore',
                'label' => 'Pulihkan',
                'icon' => 'bx-refresh',
            ],
            [
                'type' => 'force-delete',
                'url' => route('users.force-delete', $row),
                'permission' => 'management.users.force_delete',
                'label' => 'Hapus Permanen',
                'confirm' => 'Yakin ingin menghapus permanen user ini? Data tidak dapat dipulihkan!',
            ],
        ];
    }
}
