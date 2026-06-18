@props([
    'id',
    'title',
    'subtitle' => null,
    'columns' => [],
    'rows' => [],
    'placeholder' => 'Tidak ada data.',
])

{{--
    Lightweight, read-only Tabulator table for dashboard summary cards. Renders a
    container + inline JSON config in `local` mode, which tabulator.js picks up
    automatically (no AJAX, no toolbar, no pagination). `columns` are raw Tabulator
    column definitions; `rows` are plain field=>value arrays (HTML cells use the
    built-in `html` formatter).
--}}

@once
    @push('scripts')
        @vite('resources/js/modules/tabulator.js')
    @endpush
    @push('styles')
        <style>
            /* Compact sizing for read-only dashboard tables (the heavier Sneat
               table CSS is only loaded by the full table component). */
            .table-tabulator-mini {
                font-size: 0.75rem;
            }

            .table-tabulator-mini .tabulator-header .tabulator-col {
                min-height: 1.7rem;
            }

            .table-tabulator-mini .tabulator-header .tabulator-col,
            .table-tabulator-mini .tabulator-header .tabulator-col-title {
                font-size: 0.7rem;
                font-weight: 600;
            }

            .table-tabulator-mini .tabulator-row {
                min-height: 1.6rem;
            }

            .table-tabulator-mini .tabulator-row .tabulator-cell {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
                line-height: 1.3;
            }

            .table-tabulator-mini .badge {
                font-size: 0.68rem;
                padding: 0.22rem 0.4rem;
            }
        </style>
    @endpush
@endonce

<div class="card h-100">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
            <h5 class="mb-1 me-2">{{ $title }}</h5>
            @if ($subtitle)
                <p class="card-subtitle mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($action)
            <div>{{ $action }}</div>
        @endisset
    </div>
    <div class="card-body">
        <div id="{{ $id }}" class="table-tabulator table-tabulator-mini"></div>
        <script type="application/json" data-tabulator-config="{{ $id }}">{!! json_encode([
            'mode' => 'local',
            'layout' => 'fitColumns',
            'columns' => $columns,
            'data' => $rows,
            'placeholder' => $placeholder,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </div>
</div>
