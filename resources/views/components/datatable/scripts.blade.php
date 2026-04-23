@php
    $buttons = $exportExcludeLastColumn
        ? [
            ['extend' => 'copy', 'exportOptions' => ['columns' => ':not(:last-child)']],
            ['extend' => 'csv', 'exportOptions' => ['columns' => ':not(:last-child)']],
            ['extend' => 'excel', 'exportOptions' => ['columns' => ':not(:last-child)']],
            ['extend' => 'pdf', 'exportOptions' => ['columns' => ':not(:last-child)']],
            ['extend' => 'print', 'exportOptions' => ['columns' => ':not(:last-child)']],
            'colvis',
        ]
        : ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'];
@endphp

@if($clickableRows)
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

@push('scripts')
    <script>
        $(function() {
            const tableSelector = '#{{ $tableId }}';

            @if($variable)
                window.{{ $variable }} =
            @endif
            $(tableSelector).DataTable({
                processing: true,
                serverSide: true,
                ajax: @json($ajax),
                columns: [
                    {!! trim($slot) !!}
                ],
                @if($order !== null)
                    order: @json($order),
                @endif
                dom: 'lBfrtip',
                buttons: @json($buttons),
                @if($clickableRows)
                    createdRow: function(row, data) {
                        if (!data[@json($rowUrlField)]) {
                            return;
                        }

                        row.classList.add('datatable-clickable-row');
                        row.dataset.href = data[@json($rowUrlField)];
                        row.setAttribute('tabindex', '0');
                    },
                @endif
            });

            @if($clickableRows)
                $(tableSelector).on('click', 'tbody tr.datatable-clickable-row', function(event) {
                    if ($(event.target).closest('a, button, form, input, select, textarea, label').length) {
                        return;
                    }

                    window.location.href = this.dataset.href;
                });

                $(tableSelector).on('keydown', 'tbody tr.datatable-clickable-row', function(event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    window.location.href = this.dataset.href;
                });
            @endif
        });
    </script>
@endpush
