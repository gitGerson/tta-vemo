<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('management.users.logs'))
        <a href="{{ route('users.user-logs', $user) }}" class="btn btn-sm btn-secondary" title="Activity Log" aria-label="Activity Log">
            <i class="bx bx-history"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermission('management.users.edit'))
        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit" aria-label="Edit">
            <i class="bx bx-edit-alt"></i>
        </a>
        <a href="{{ route('users.roles', $user) }}" class="btn btn-sm btn-primary d-none" title="Kelola Role" aria-label="Kelola Role">
            <i class="bx bx-shield"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermission('management.users.delete'))
        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm(@js('Yakin ingin menghapus user ini?'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
