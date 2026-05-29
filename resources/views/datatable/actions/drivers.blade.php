<div class="d-inline-flex align-items-center gap-1">
    <a href="{{ route('drivers.show', $driver) }}" class="btn btn-sm btn-info" title="Lihat" aria-label="Lihat">
        <i class="bx bx-show"></i>
    </a>

    @if($canEdit)
        <button
            type="button"
            class="btn btn-sm btn-warning btn-edit-driver"
            title="Edit"
            aria-label="Edit"
            data-update-url="{{ route('drivers.update', $driver) }}"
            data-name="{{ $driver->name }}"
            data-nik="{{ $driver->nik }}"
            data-no-identity="{{ $driver->no_identity }}"
            data-residence="{{ $driver->residence }}"
            data-date-birth="{{ $driver->date_birth?->format('Y-m-d') }}"
            data-no-phone-1="{{ $driver->no_phone_1 }}"
            data-no-phone-2="{{ $driver->no_phone_2 }}"
            data-address="{{ $driver->address }}"
            data-entry-date="{{ $driver->entry_date?->format('Y-m-d') }}"
            data-out-date="{{ $driver->out_date?->format('Y-m-d') }}"
            data-status="{{ $driver->status }}"
            data-image-url="{{ $driver->imageUrl() }}"
        >
            <i class="bx bx-edit-alt"></i>
        </button>
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
