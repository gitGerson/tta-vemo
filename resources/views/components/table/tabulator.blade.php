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
    ];

    // Local editable grids show the Add button without a remote create URL.
    $showAdd = $table->tabulatorCreateUrl() !== null || ($isLocal && $table->editable());
@endphp

@once
    @push('styles')
        <style>
            .table-tabulator-card {
                --tabulator-sneat-success: #71dd37;
                --tabulator-sneat-success-rgb: 113, 221, 55;
                --tabulator-compact-font-size: 0.76rem;
                --tabulator-content-font-size: 0.75rem;
            }

            .table-tabulator-card .card-header {
                padding: 0.7rem 0.9rem;
            }

            .table-tabulator-card .card-header h5 {
                font-size: 0.95rem;
                line-height: 1.25;
            }

            .table-tabulator-card .card-body {
                padding: 0.75rem;
            }

            .table-tabulator-card .btn-sm {
                --bs-btn-padding-y: 0.25rem;
                --bs-btn-padding-x: 0.5rem;
                --bs-btn-font-size: 0.75rem;
                --bs-btn-border-radius: 0.25rem;
            }

            .table-tabulator-card .btn-sm:disabled {
                box-shadow: none;
            }

            .table-tabulator-search {
                width: 10rem;
                min-height: 1.85rem;
                padding: 0.24rem 0.45rem;
                border: 1px solid rgba(var(--tabulator-sneat-success-rgb), 0.35);
                border-radius: 0.25rem;
                font-size: 0.72rem;
                line-height: 1.2;
            }

            .table-tabulator-search:focus {
                border-color: var(--tabulator-sneat-success);
                box-shadow: 0 0 0 0.12rem rgba(var(--tabulator-sneat-success-rgb), 0.18);
                outline: 0;
            }

            @media (max-width: 575.98px) {
                .table-tabulator-toolbar {
                    width: 100%;
                    justify-content: flex-start;
                }

                .table-tabulator-search {
                    display: none;
                }

                /* Collapse toolbar buttons to icon-only: zero the button font to hide
                   the text label + whitespace, then restore the boxicon glyph size.
                   !important overrides Bootstrap's me-1 (margin utilities are !important). */
                .table-tabulator-card .card-header .btn {
                    font-size: 0;
                }

                .table-tabulator-card .card-header .btn > .bx {
                    font-size: 1rem;
                    margin: 0 !important;
                }
            }

            .table-tabulator {
                --tabulator-column-border: rgba(var(--tabulator-sneat-success-rgb), 0.18);
                border-color: rgba(var(--tabulator-sneat-success-rgb), 0.22);
                border-radius: 0.375rem;
                font-size: var(--tabulator-compact-font-size);
            }

            .table-tabulator .tabulator-header {
                border-bottom-color: rgba(var(--tabulator-sneat-success-rgb), 0.36);
                background: rgba(var(--tabulator-sneat-success-rgb), 0.08);
            }

            .table-tabulator .tabulator-header .tabulator-col {
                min-height: 1.85rem;
                border-right: 1px solid var(--tabulator-column-border);
                background: rgba(var(--tabulator-sneat-success-rgb), 0.08);
                box-shadow: inset -1px 0 0 var(--tabulator-column-border);
            }

            .table-tabulator .tabulator-header .tabulator-col.tabulator-sortable:hover {
                background: rgba(var(--tabulator-sneat-success-rgb), 0.16);
            }

            .table-tabulator .tabulator-header .tabulator-col-title {
                color: #2f5f1d;
                font-size: 0.72rem;
                font-weight: 600;
                line-height: 1.15;
            }

            .table-tabulator .tabulator-header .tabulator-arrow {
                border-bottom-color: var(--tabulator-sneat-success);
            }

            .table-tabulator .tabulator-header .tabulator-col[aria-sort='desc'] .tabulator-arrow {
                border-top-color: var(--tabulator-sneat-success);
                border-bottom-color: transparent;
            }

            .table-tabulator .tabulator-row {
                min-height: 2rem;
                border-bottom-color: rgba(67, 89, 113, 0.08);
            }

            .table-tabulator .tabulator-row.tabulator-row-even {
                background-color: rgba(var(--tabulator-sneat-success-rgb), 0.025);
            }

            .table-tabulator .tabulator-row.tabulator-selectable:hover {
                background-color: rgba(var(--tabulator-sneat-success-rgb), 0.1);
            }

            .table-tabulator .tabulator-cell {
                min-height: 2rem;
                padding: 0.35rem 0.5rem;
                border-right: 1px solid var(--tabulator-column-border);
                box-shadow: inset -1px 0 0 var(--tabulator-column-border);
                font-size: var(--tabulator-content-font-size);
                line-height: 1.25;
            }

            .table-tabulator .tabulator-cell input[type='checkbox'],
            .table-tabulator .tabulator-col input[type='checkbox'] {
                width: 0.85rem;
                height: 0.85rem;
                margin: 0;
                accent-color: var(--tabulator-sneat-success);
                vertical-align: middle;
            }

            .table-tabulator .tabulator-row.tabulator-selected {
                background-color: rgba(var(--tabulator-sneat-success-rgb), 0.14);
            }

            .table-tabulator .tabulator-row.tabulator-selected:hover {
                background-color: rgba(var(--tabulator-sneat-success-rgb), 0.2);
            }

            .table-tabulator .tabulator-row.table-tabulator-row-dirty {
                box-shadow: inset 3px 0 0 var(--tabulator-sneat-success);
            }

            .table-tabulator .tabulator-header .tabulator-col:last-child,
            .table-tabulator .tabulator-row .tabulator-cell:last-child {
                border-right: 0;
                box-shadow: none;
            }

            .table-tabulator .badge {
                padding: 0.24rem 0.42rem;
                font-size: 0.68rem;
                line-height: 1;
            }

            .table-tabulator .btn-sm {
                --bs-btn-padding-y: 0.18rem;
                --bs-btn-padding-x: 0.35rem;
                --bs-btn-font-size: 0.7rem;
                --bs-btn-border-radius: 0.2rem;
            }

            .table-tabulator .tabulator-footer {
                padding: 0.15rem 0.5rem;
                border-top-color: rgba(var(--tabulator-sneat-success-rgb), 0.22);
                background: rgba(var(--tabulator-sneat-success-rgb), 0.04);
                font-size: 0.74rem;
            }

            .table-tabulator .tabulator-footer .tabulator-page,
            .table-tabulator .tabulator-footer .tabulator-page-size {
                color: #2f5f1d;
                height: 1.7rem;
                min-height: 1.7rem;
                line-height: 1;
                padding: 0 0.42rem;
                border-color: rgba(var(--tabulator-sneat-success-rgb), 0.35);
                background: #fff;
                border-radius: 0.25rem;
                font-size: 0.72rem;
                line-height: 1.2;
            }

            .table-tabulator .tabulator-footer .tabulator-page:not(.disabled):hover,
            .table-tabulator .tabulator-footer .tabulator-page-size:focus {
                border-color: var(--tabulator-sneat-success) !important;
                background: rgba(var(--tabulator-sneat-success-rgb), 0.12) !important;
                color: #2f5f1d !important;
            }

            .table-tabulator .tabulator-footer .tabulator-page.active,
            .table-tabulator .tabulator-footer .tabulator-page[aria-current='page'],
            .table-tabulator .tabulator-footer .tabulator-page[data-page='active'],
            .table-tabulator .tabulator-footer .tabulator-page.tabulator-page-current {
                border-color: var(--tabulator-sneat-success) !important;
                background: var(--tabulator-sneat-success) !important;
                color: #fff !important;
                box-shadow: 0 0.125rem 0.375rem rgba(var(--tabulator-sneat-success-rgb), 0.36);
            }

            .table-tabulator .tabulator-footer .tabulator-page.disabled {
                border-color: rgba(67, 89, 113, 0.12) !important;
                background: rgba(67, 89, 113, 0.04) !important;
                color: rgba(67, 89, 113, 0.42) !important;
            }

            .table-tabulator .tabulator-responsive-collapse {
                padding: 0.35rem 0.5rem;
                background: rgba(var(--tabulator-sneat-success-rgb), 0.035);
                border-top: 1px solid var(--tabulator-column-border);
                font-size: 0.74rem;
            }

            .table-tabulator.table-clickable .tabulator-row {
                cursor: pointer;
            }

            .table-tabulator-menu {
                min-width: 9rem;
                font-size: 0.72rem;
            }

            .table-tabulator-menu .dropdown-item {
                padding: 0.15rem 0.4rem;
                border-radius: 0.2rem;
                cursor: pointer;
            }

            .table-tabulator-menu .form-check-input {
                width: 0.8rem;
                height: 0.8rem;
            }
        </style>
    @endpush
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
                    placeholder="Search"
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
                    <i class="bx bx-plus me-1"></i> Add
                </button>
            @endif

            @if ($selectableRows && $table->tabulatorDeleteUrl() !== null)
                <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $tableId }}-delete" disabled>
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
            @endif

            @if ($table->tabulatorUpdateUrl() !== null)
                <button type="button" class="btn btn-sm btn-outline-success" id="{{ $tableId }}-save" disabled>
                    <i class="bx bx-save me-1"></i> Save
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
                        <i class="bx bx-download me-1"></i> Export
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
