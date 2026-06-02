@props(['actions' => []])

{{--
    Generic, permission-gated action column.
    Each descriptor: ['type' => view|edit-link|edit-modal|delete|force-delete|restore, ...].
    Common keys: permission, url, label, icon, confirm. edit-modal also takes
    'class' and a 'data' map (rendered as data-* attributes for modal population).
--}}
<div class="d-inline-flex align-items-center gap-1">
    @foreach ($actions as $action)
        @continue (! empty($action['permission']) && ! auth()->user()?->hasPermission($action['permission']))

        @php $type = $action['type'] ?? 'view'; @endphp

        @switch($type)
            @case('view')
                <a href="{{ $action['url'] }}" class="btn btn-sm btn-info" title="{{ $action['label'] ?? 'Lihat' }}" aria-label="{{ $action['label'] ?? 'Lihat' }}">
                    <i class="bx {{ $action['icon'] ?? 'bx-show' }}"></i>
                </a>
                @break

            @case('edit-link')
                <a href="{{ $action['url'] }}" class="btn btn-sm btn-warning" title="{{ $action['label'] ?? 'Edit' }}" aria-label="{{ $action['label'] ?? 'Edit' }}">
                    <i class="bx {{ $action['icon'] ?? 'bx-edit-alt' }}"></i>
                </a>
                @break

            @case('edit-modal')
                <button
                    type="button"
                    class="btn btn-sm btn-warning {{ $action['class'] ?? '' }}"
                    title="{{ $action['label'] ?? 'Edit' }}"
                    aria-label="{{ $action['label'] ?? 'Edit' }}"
                    @foreach (($action['data'] ?? []) as $dataKey => $dataValue) data-{{ $dataKey }}="{{ $dataValue }}" @endforeach
                >
                    <i class="bx {{ $action['icon'] ?? 'bx-edit-alt' }}"></i>
                </button>
                @break

            @case('delete')
            @case('force-delete')
                <form action="{{ $action['url'] }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="{{ $action['label'] ?? 'Hapus' }}" aria-label="{{ $action['label'] ?? 'Hapus' }}" onclick="return confirm(@js($action['confirm'] ?? 'Yakin ingin menghapus data ini?'))">
                        <i class="bx {{ $action['icon'] ?? 'bx-trash' }}"></i>
                    </button>
                </form>
                @break

            @case('restore')
                <form action="{{ $action['url'] }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" title="{{ $action['label'] ?? 'Pulihkan' }}" aria-label="{{ $action['label'] ?? 'Pulihkan' }}">
                        <i class="bx {{ $action['icon'] ?? 'bx-undo' }}"></i>
                    </button>
                </form>
                @break
        @endswitch
    @endforeach
</div>
