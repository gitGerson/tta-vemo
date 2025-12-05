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
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">TGL MULAI</label>
                        <input type="date" value="{{ $start_date }}" name="start_date"
                            class="form-control me-2 date" id="start_date">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">TGL SELESAI</label>
                        <input type="date" value="{{ $end_date }}" name="end_date"
                            class="form-control me-2 date" id="end_date">
                    </div>

                    <div class="col-md-3">
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

                    <div class="col-md-3 d-flex align-items-end">
                        <a target="_blank"
                            href="/report/maintenances/daily/pdf?start_date={{ $start_date }}&end_date={{ $end_date }}&unit={{ request()->query('unit') }}"
                            class="btn btn-danger w-50">Cetak PDF</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

        @if ($start_date && $end_date && request()->query('unit'))
         <h6 class="font-weight-bold"><span class="text-primary ms-6">UNIT &nbsp;: </span>
            &nbsp;{{ $unit->plat_new }} / {{ $unit->type }}</h6>
            <div class="table-responsive text-nowrap ms-6">
            <table id="dataTable" class="table table-bordered table-striped">
                <thead class="table-light">
                <tr class="text-nowrap">
                    <th scope="col">No</th>
                    <th scope="col">JENIS</th>
                    <th scope="col">TOTAL QTY</th>
                    <th scope="col">TOTAL HARGA</th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($detailMtc as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->maintenance_name }}</td>
                            <td>{{ $row->total_qty }}</td>
                            <td>{{ formatRupiah($row->total_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
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

    var table = $('#dataTable').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { extend: 'copy', exportOptions: { columns: ':not(:last-child)' } },
            { extend: 'csv', exportOptions: { columns: ':not(:last-child)' } },
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
                    let unitType = '{{ isset($unit) ? $unit->type : "" }}';
                    
                    let formattedStartDate = startDate ? new Date(startDate).toLocaleDateString('id-ID') : '';
                    let formattedEndDate = endDate ? new Date(endDate).toLocaleDateString('id-ID') : '';
                    
                    var sheetString = new XMLSerializer().serializeToString(sheet);
                    
                    var headerRows = '';
                    
                    headerRows += '<row r="1">';
                    headerRows += '<c r="A1" t="inlineStr"><is><t>LAPORAN PEMELIHARAAN PERUNIT</t></is></c>';
                    headerRows += '</row>';
                    
                    headerRows += '<row r="2">';
                    headerRows += '<c r="A2" t="inlineStr"><is><t>Periode: ' + formattedStartDate + ' - ' + formattedEndDate + '</t></is></c>';
                    headerRows += '</row>';
                    
                    headerRows += '<row r="3">';
                    headerRows += '<c r="A3" t="inlineStr"><is><t>Unit: ' + unit + (unitType ? ' / ' + unitType : '') + '</t></is></c>';
                    headerRows += '</row>';
                    
                    headerRows += '<row r="4"></row>';
                    
                    var sheetDataStart = sheetString.indexOf('<sheetData>') + '<sheetData>'.length;
                    var beforeSheetData = sheetString.substring(0, sheetDataStart);
                    var afterSheetData = sheetString.substring(sheetDataStart);
                    
                    afterSheetData = afterSheetData.replace(/r="(\d+)"/g, function(match, rowNum) {
                        return 'r="' + (parseInt(rowNum) + 3) + '"';
                    });
                    

                    afterSheetData = afterSheetData.replace(/r="([A-Z]+)(\d+)"/g, function(match, col, rowNum) {
                        return 'r="' + col + (parseInt(rowNum) + 3) + '"';
                    });
                    
                    var newSheetString = beforeSheetData + headerRows + afterSheetData;
                    
                    var parser = new DOMParser();
                    var newSheet = parser.parseFromString(newSheetString, 'text/xml');
                    
                    xlsx.xl.worksheets['sheet1.xml'] = newSheet.documentElement;
                }
            },
            { extend: 'pdf', exportOptions: { columns: ':not(:last-child)' } },
            { extend: 'print', exportOptions: { columns: ':not(:last-child)' } },
            'colvis'
        ],
        language: {
            paginate: {
                previous: '‹',
                next: '›'
            }
        },
    });
});
</script>
@endpush
