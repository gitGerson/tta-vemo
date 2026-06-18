@props([
    'table',
    'title' => null,
    'tableClass' => 'table table-striped',
    'responsiveClass' => 'table-responsive text-nowrap',
    'paginationSize' => 10,
    'paginationSizeSelector' => [10, 25, 50, 100],
    'selectableRows' => false,
])

@php
    $tableId = $table->id() . '-datatables';
    $clickable = $table->isClickable();
    $exportColumns = $table->exportColumnIndexes();
    $buttons = [
        ['extend' => 'copy', 'exportOptions' => ['columns' => $exportColumns]],
        ['extend' => 'csv', 'exportOptions' => ['columns' => $exportColumns]],
        ['extend' => 'excel', 'exportOptions' => ['columns' => $exportColumns]],
        ['extend' => 'pdf', 'exportOptions' => ['columns' => $exportColumns]],
        ['extend' => 'print', 'exportOptions' => ['columns' => $exportColumns]],
        'colvis',
    ];
@endphp

@if ($clickable)
    @once
        @push('styles')
            <style>
                .datatable-clickable-row {
                    cursor: pointer;
                    transition: background-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
                }

                .datatable-clickable-row:hover {
                    background-color: rgba(var(--bs-primary-rgb), 0.08) !important;
                    box-shadow: inset 4px 0 0 var(--bs-primary);
                    transform: translateX(2px);
                }
            </style>
        @endpush
    @endonce
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>

        @isset($actions)
            <div>{{ $actions }}</div>
        @endisset
    </div>
    <div class="card-body">
        <div class="{{ $responsiveClass }}">
            <table id="{{ $tableId }}" class="{{ $tableClass }}">
                <thead>
                    <tr>
                        @foreach ($table->headers() as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            const tableSelector = '#{{ $tableId }}';

            $(tableSelector).DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: @json($table->ajax()),
                columns: @json($table->jsColumns()),
                pageLength: @json($paginationSize),
                lengthMenu: @json($paginationSizeSelector),
                @if ($table->order() !== null)
                    order: @json($table->order()),
                @endif
                dom: 'lBfrtip',
                buttons: @json($buttons),
                @if ($clickable)
                    createdRow: function (row, data) {
                        if (!data[@json($table->rowUrlField())]) {
                            return;
                        }

                        row.classList.add('datatable-clickable-row');
                        row.dataset.href = data[@json($table->rowUrlField())];
                        row.setAttribute('tabindex', '0');
                    },
                @endif
            });

            @if ($clickable)
                @if ($table->hasDetailModal())
                    const detailModalEl = document.getElementById(@json($table->detailModal()));

                    const openDetailModal = (url) => {
                        if (!detailModalEl || !url) {
                            return;
                        }

                        const body = detailModalEl.querySelector('.modal-body');
                        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
                        bootstrap.Modal.getOrCreateInstance(detailModalEl).show();

                        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                            .then((response) => response.ok ? response.text() : Promise.reject(response.status))
                            .then((html) => { body.innerHTML = html; })
                            .catch(() => { body.innerHTML = '<div class="alert alert-danger m-3 mb-0">Gagal memuat detail.</div>'; });
                    };

                    $(tableSelector).on('click', 'tbody tr.datatable-clickable-row', function (event) {
                        if ($(event.target).closest('a, button, form, input, select, textarea, label').length) {
                            return;
                        }

                        openDetailModal(this.dataset.href);
                    });

                    $(tableSelector).on('keydown', 'tbody tr.datatable-clickable-row', function (event) {
                        if (event.key === 'Enter') {
                            openDetailModal(this.dataset.href);
                        }
                    });

                    $(tableSelector).on('click', '.btn-detail-modal', function () {
                        openDetailModal(this.dataset.url);
                    });
                @else
                    $(tableSelector).on('click', 'tbody tr.datatable-clickable-row', function (event) {
                        if ($(event.target).closest('a, button, form, input, select, textarea, label').length) {
                            return;
                        }

                        window.location.href = this.dataset.href;
                    });

                    $(tableSelector).on('keydown', 'tbody tr.datatable-clickable-row', function (event) {
                        if (event.key !== 'Enter') {
                            return;
                        }

                        window.location.href = this.dataset.href;
                    });
                @endif
            @endif
        });
    </script>
@endpush
