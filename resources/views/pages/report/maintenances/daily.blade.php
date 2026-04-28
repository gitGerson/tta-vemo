@extends('layouts.sneat')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cetak Laporan Pemeliharaan Unit</h5>
            </div>

            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <form action="" method="GET" class="row g-3 align-items-end" id="dateForm">
                            <div class="col-md-2">
                                <label for="month" class="form-label">BULAN/TAHUN</label>
                                <input type="month" value="{{ \Carbon\Carbon::parse($start_date)->format('Y-m') }}"
                                    class="form-control" id="month">
                            </div>

                            <div class="col-md-2">
                                <label for="start_date" class="form-label">TGL MULAI</label>
                                <input type="date" value="{{ $start_date }}" name="start_date"
                                    class="form-control date" id="start_date">
                            </div>

                            <div class="col-md-2">
                                <label for="end_date" class="form-label">TGL SELESAI</label>
                                <input type="date" value="{{ $end_date }}" name="end_date"
                                    class="form-control date" id="end_date">
                            </div>

                            <div class="col-md-4">
                                <label for="unit" class="form-label">UNIT</label>
                                <select class="form-select select2" style="width: 100%;" name="unit" required>
                                    <option selected disabled>- pilih kendaraan -</option>

                                    <optgroup label="Plat Nomer Terbaru">
                                        @foreach ($vehicles as $row)
                                            @if (!empty($row->plat_new))
                                                <option
                                                    {{ request()->query('unit') == $row->plat_new ? 'selected' : '' }}
                                                    value="{{ $row->plat_new }}">
                                                    {{ $row->plat_new }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="Plat Nomer Lama">
                                        @foreach ($vehicles as $row)
                                            @if (!empty($row->plat_old))
                                                <option
                                                    {{ request()->query('unit') == $row->plat_old ? 'selected' : '' }}
                                                    value="{{ $row->plat_old }}">
                                                    {{ $row->plat_old }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <a target="_blank"
                                    href="/report/maintenances/daily/pdf?start_date={{ $start_date }}&end_date={{ $end_date }}&unit={{ request()->query('unit') }}"
                                    class="btn btn-danger flex-grow-1 {{ !request()->query('unit') ? 'disabled' : '' }}">Cetak PDF</a>
                                <!-- <a href="/report/maintenances/daily"
                                    class="btn btn-outline-secondary"
                                    title="Reset Filter">
                                    <i class='bx bx-reset'></i>
                                </a> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if ($start_date && $end_date && request()->query('unit'))
                <h6 class="font-weight-bold ms-6">
                    <span class="text-primary">UNIT &nbsp;:</span>
                    &nbsp;{{ $unit?->plat_new ?? $unit?->plat_old ?? request()->query('unit') }}
                    {{ $unit?->type ? '/ ' . $unit->type : '' }}
                    {{ $unit?->entry_year ? '/ Tahun Masuk ' . $unit->entry_year : '' }}
                </h6>

                <div class="report-table-toolbar ms-6 mb-3">
                    <div id="dataTableButtons" class="report-table-buttons"></div>
                    <div class="report-table-search">
                        <input type="text" id="global-search" class="form-control" placeholder="Search...">
                    </div>
                </div>

                <div class="table-responsive ms-6">
                    <table id="dataTable" class="table table-bordered table-striped compact-report-table">
                        <thead class="table-light">
                            <tr class="text-nowrap text-center">
                                <th scope="col" rowspan="2">No</th>
                                <th scope="col" rowspan="2">TANGGAL</th>
                                <th scope="col" rowspan="2">KODE MT</th>
                                <th scope="col" rowspan="2">JENIS</th>
                                <th scope="col" colspan="3">BIAYA</th>
                            </tr>
                            <tr class="text-nowrap">
                                <th scope="col">QTY</th>
                                <th scope="col">HARGA</th>
                                <th scope="col">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($detailMtc as $row)
                                @php
                                    $rowDate = $row->date_out ?? $row->date_in;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $rowDate ? \Carbon\Carbon::parse($rowDate)->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $row->code_mt ?? '-' }}</td>
                                    <td>{{ $row->maintenance_name }}</td>
                                    <td class="text-end">{{ $row->qty ?: 0 }}</td>
                                    <td class="text-end">{{ number_format((float) ($row->price ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) ($row->subtotal ?? 0), 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="fw-semibold">JUMLAH</td>
                                <td class="text-end fw-semibold">{{ number_format($detailMtc->sum(fn ($item) => (float) ($item->subtotal ?? 0)), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection


@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            display: flex;
            align-items: center;
        }

        .compact-report-table {
            font-size: 0.8125rem;
        }

        .compact-report-table th,
        .compact-report-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .report-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .report-table-buttons .dt-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .report-table-search {
            width: min(100%, 240px);
        }
    </style>
@endpush

@push('addon-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2().on('change', function() {
        $('#dateForm').submit();
    });

    $('.date').on('change', function() {
        $('#dateForm').submit();
    });

    $('#month').on('change', function() {
        const value = $(this).val();
        if (!value) {
            return;
        }
        const [year, month] = value.split('-').map(Number);
        const start = new Date(year, month - 1, 1);
        const end = new Date(year, month, 0);
        const fmt = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        $('#start_date').val(fmt(start));
        $('#end_date').val(fmt(end));
        $('#dateForm').submit();
    });

    if (!$.fn.DataTable.isDataTable('#dataTable')) {
        return;
    }

    var table = $('#dataTable').DataTable({
        dom: 't',
        paging: false,
        info: false,
        buttons: [
            { extend: 'copy', exportOptions: { columns: ':visible' } },
            { extend: 'csv', exportOptions: { columns: ':visible' } },
            {
                extend: 'excelHtml5',
                text: "<i class='bx bx-export me-1'></i> Export Excel",
                title: ' ',
                filename: function() {
                    let startDate = $('#start_date').val() || 'start';
                    let endDate = $('#end_date').val() || 'end';
                    let unit = $('select[name="unit"]').val() || 'unit';
                    return 'laporan_pemeliharaan_' + unit + '_' + startDate + '_to_' + endDate;
                },
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                    let startDate = $('#start_date').val() || '';
                    let endDate = $('#end_date').val() || '';
                    let unit = $('select[name="unit"]').val() || '';
                    let unitType = @json($unit?->type ?? '');
                    let unitEntryYear = @json($unit?->entry_year ?? '');

                    let formattedStartDate = startDate ? new Date(startDate).toLocaleDateString('id-ID') : '';
                    let formattedEndDate = endDate ? new Date(endDate).toLocaleDateString('id-ID') : '';

                    function createCell(r, c, value) {
                        return '<c r="' + c + r + '" t="inlineStr"><is><t>' + value + '</t></is></c>';
                    }

                    $('row', sheet).each(function() {
                        const r = parseInt($(this).attr('r'));
                        $(this).attr('r', r + 4);
                        $(this).find('c').each(function() {
                            const cellRef = $(this).attr('r');
                            const col = cellRef.replace(/[0-9]/g, '');
                            const rowNum = parseInt(cellRef.replace(/[A-Z]/g, ''));
                            $(this).attr('r', col + (rowNum + 4));
                        });
                    });

                    const heading =
                        '<row r="1">' + createCell(1, 'A', 'LAPORAN PEMELIHARAAN PERUNIT') + '</row>' +
                        '<row r="2">' + createCell(2, 'A', 'Periode: ' + formattedStartDate + ' - ' + formattedEndDate) + '</row>' +
                        '<row r="3">' + createCell(3, 'A', 'Unit: ' + unit + (unitType ? ' / ' + unitType : '') + (unitEntryYear ? ' / Tahun Masuk ' + unitEntryYear : '')) + '</row>' +
                        '<row r="4"></row>';

                    const sheetData = sheet.getElementsByTagName('sheetData')[0];
                    sheetData.innerHTML = heading + sheetData.innerHTML;
                }
            },
            { extend: 'pdf', exportOptions: { columns: ':visible' } },
            { extend: 'print', exportOptions: { columns: ':visible' } },
            'colvis'
        ],
        language: {
            emptyTable: 'Tidak ada data.',
            paginate: {
                previous: '‹',
                next: '›'
            }
        },
    });

    table.buttons().container().appendTo('#dataTableButtons');

    $('#global-search').on('keyup', function() {
        table.search(this.value).draw();
    });
});
</script>
@endpush
