<div class="d-inline-flex align-items-center gap-1">
    <a href="{{ route('drivers.show', $driver) }}" class="btn btn-sm btn-info" title="Lihat" aria-label="Lihat">
        <i class="bx bx-show"></i>
    </a>

    @if($canEdit)
        <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-sm btn-warning" title="Edit" aria-label="Edit">
            <i class="bx bx-edit-alt"></i>
        </a>
    @endif

    @if($canDelete)
        <form action="{{ route('drivers.destroy', $driver) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm(@js('Yakin ingin menghapus driver ini?'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
