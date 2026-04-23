<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('images.restore'))
        <form action="{{ route('images.restore', $image->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-success" title="Restore" aria-label="Restore" onclick="return confirm(@js('Are you sure you want to restore this image?'))">
                <i class="bx bx-refresh"></i>
            </button>
        </form>
    @endif

    @if(auth()->user()->hasPermission('images.force_delete'))
        <form action="{{ route('images.force-delete', $image->id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Delete Permanently" aria-label="Delete Permanently" onclick="return confirm(@js('Are you sure you want to permanently delete this image? This cannot be undone!'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
