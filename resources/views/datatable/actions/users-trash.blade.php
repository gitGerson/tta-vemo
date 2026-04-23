<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('management.users.restore'))
        <form action="{{ route('users.restore', $user->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-success" title="Pulihkan" aria-label="Pulihkan" onclick="return confirm(@js('Yakin ingin memulihkan user ini?'))">
                <i class="bx bx-refresh"></i>
            </button>
        </form>
    @endif

    @if(auth()->user()->hasPermission('management.users.force_delete'))
        <form action="{{ route('users.force-delete', $user->id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen" aria-label="Hapus Permanen" onclick="return confirm(@js('Yakin ingin menghapus permanen user ini? Data tidak dapat dipulihkan!'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
