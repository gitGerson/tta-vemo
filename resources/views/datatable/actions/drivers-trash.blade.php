<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('master.drivers.restore'))
        <form action="{{ route('drivers.restore', $driver) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-success" title="Restore" aria-label="Restore">
                <i class="bx bx-undo"></i>
            </button>
        </form>
    @endif

    @if(auth()->user()->hasPermission('master.drivers.force_delete'))
        <form action="{{ route('drivers.force-delete', $driver) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen" aria-label="Hapus Permanen" onclick="return confirm(@js('Yakin ingin menghapus permanen driver ini?'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
