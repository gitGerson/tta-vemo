<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('management.roles.edit'))
        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning" title="Edit" aria-label="Edit">
            <i class="bx bx-edit-alt"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermission('management.roles.delete') && $role->users_count == 0)
        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm(@js('Yakin ingin menghapus role ini?'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @elseif($role->users_count > 0)
        <button type="button" class="btn btn-sm btn-outline-secondary" title="Tidak dapat dihapus (sedang digunakan)" aria-label="Tidak dapat dihapus (sedang digunakan)" disabled>
            <i class="bx bx-trash"></i>
        </button>
    @endif
</div>
