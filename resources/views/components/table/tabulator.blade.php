@props([
    'table',
    'title' => null,
    'tableClass' => 'table table-striped',
    'responsiveClass' => 'table-responsive text-nowrap',
    'paginationSize' => 10,
    'paginationSizeSelector' => [10, 25, 50, 100],
    'layout' => 'fitColumns',
    'responsiveLayout' => false,
    'selectableRows' => false,
    'showSearch' => true,
    'showColvis' => true,
    'showExport' => true,
    'placeholder' => null,
])

@php
    $tableId = $table->id() . '-tabulator';
    $clickable = $table->isClickable();
    $isLocal = $table->mode() === 'local';
    $config = [
        'ajaxUrl' => $table->ajax(),
        'columns' => $table->tabulatorColumns(),
        'initialSort' => $table->tabulatorInitialSort(),
        'clickable' => $clickable,
        'detailModal' => $table->detailModal(),
        'rowUrlField' => $table->rowUrlField(),
        'paginationSize' => $paginationSize,
        'paginationSizeSelector' => $paginationSizeSelector,
        'layout' => $layout,
        'responsiveLayout' => $responsiveLayout,
        'selectableRows' => $selectableRows,
        'createUrl' => $table->tabulatorCreateUrl(),
        'updateUrl' => $table->tabulatorUpdateUrl(),
        'deleteUrl' => $table->tabulatorDeleteUrl(),
        // Local (client-side editable) mode — defaults are remote-safe no-ops.
        'mode' => $table->mode(),
        'data' => $isLocal ? $table->clientData() : [],
        'editable' => $table->editable(),
        'formField' => $table->formField(),
        'formTarget' => $table->formTarget(),
        'formFieldMap' => $table->formFieldMap(),
        'addRowDefaults' => $table->addRowDefaults(),
        'addRowFocusField' => $table->addRowFocusField(),
        'placeholder' => $placeholder,
    ];

    // Local editable grids show the Add button without a remote create URL.
    $showAdd = $table->tabulatorCreateUrl() !== null || ($isLocal && $table->editable());
@endphp

@once
    {{-- .table-tabulator styling (header/rows/footer/borders + dark mode) lives in
         resources/sass/_tabulator.scss so it loads globally and also covers the
         read-only <x-dashboard.list-table> grids, not just this component. --}}
    @push('scripts')
        @vite(['resources/js/modules/tabulator.js'])
    @endpush
@endonce

<div class="card table-tabulator-card">
    <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <h5 class="mb-0">{{ $title }}</h5>

        <div class="d-flex flex-wrap align-items-center gap-2 table-tabulator-toolbar">
            @if ($showSearch)
                <input
                    type="search"
                    class="form-control form-control-sm table-tabulator-search"
                    id="{{ $tableId }}-search"
                    placeholder="Cari"
                    autocomplete="off"
                >
            @endif

            @isset($actions)
                <div class="d-flex align-items-center gap-2">{{ $actions }}</div>
            @endisset

            @isset($tools)
                <div class="d-flex align-items-center gap-2">{{ $tools }}</div>
            @endisset

            @if ($showAdd)
                <button type="button" class="btn btn-sm btn-success" id="{{ $tableId }}-add">
                    <i class="bx bx-plus me-1"></i> Tambah
                </button>
            @endif

            @if ($selectableRows && $table->tabulatorDeleteUrl() !== null)
                <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $tableId }}-delete" disabled>
                    <i class="bx bx-trash me-1"></i> Hapus
                </button>
            @endif

            @if ($table->tabulatorUpdateUrl() !== null)
                <button type="button" class="btn btn-sm btn-outline-success" id="{{ $tableId }}-save" disabled>
                    <i class="bx bx-save me-1"></i> Simpan
                </button>
            @endif

            @if ($showColvis)
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $tableId }}-colvis" aria-expanded="false">
                        <i class="bx bx-columns me-1"></i> Kolom
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-1 table-tabulator-menu" id="{{ $tableId }}-colvis-menu">
                        {{-- checkbox items injected by tabulator.js --}}
                    </div>
                </div>
            @endif

            @if ($showExport)
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $tableId }}-export" aria-expanded="false">
                        <i class="bx bx-download me-1"></i> Ekspor
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-1 table-tabulator-menu" id="{{ $tableId }}-export-menu">
                        <a class="dropdown-item" href="#" data-export="csv">CSV</a>
                        <a class="dropdown-item" href="#" data-export="json">JSON</a>
                        <a class="dropdown-item" href="#" data-export="xlsx">Excel (XLSX)</a>
                        <a class="dropdown-item" href="#" data-export="pdf">PDF</a>
                        <a class="dropdown-item" href="#" data-export="html">HTML</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div id="{{ $tableId }}" class="table-tabulator"></div>
        <script type="application/json" data-tabulator-config="{{ $tableId }}">{!! json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </div>
</div>
