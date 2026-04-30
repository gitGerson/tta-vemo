<div class="d-inline-flex align-items-center gap-1">
    @if(auth()->user()->hasPermission('images.edit'))
        <a href="{{ route('images.edit', $image) }}" class="btn btn-sm btn-warning" title="Edit" aria-label="Edit">
            <i class="bx bx-edit-alt"></i>
        </a>
    @endif

    <a href="{{ $image->url }}" class="btn btn-sm btn-secondary" title="Download" aria-label="Download" download target="_blank">
        <i class="bx bx-download"></i>
    </a>

    @if(auth()->user()->hasPermission('images.delete'))
        <form action="{{ route('images.destroy', $image) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Delete" aria-label="Delete" onclick="return confirm(@js('Are you sure you want to delete this image?'))">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    @endif
</div>
