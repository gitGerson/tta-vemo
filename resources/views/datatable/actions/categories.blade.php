<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('master.categories.edit'))
        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-warning" title="Edit" aria-label="Edit">
            <i class="bx bx-edit-alt"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermission('master.categories.delete'))
        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm(@js('Yakin ingin menghapus kategori ini?'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
