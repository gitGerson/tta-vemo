@extends('layouts.sneat')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cetak Laporan Bulanan</h5>
            </div>

            <form action="" id="FormSearchWithQuery" class="container-fluid mb-4">
                <div class="row">
                    <div class="col-md-3 mb-md-0">
                        <label for="company" class="form-label">PERUSAHAAN</label>
                        <select class="form-select select2" style="width: 100%;" id="company" name="company"
                            aria-label="Default select example">
                            <option selected disabled>- pilih perusahaan -</option>
                            @foreach ($company as $row)
                                <option
                                    {{ request()->query('company') == $row->id || $row->code == 'TTI' ? 'selected' : '' }}
                                    value="{{ $row->id }}">{{ $row->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr>

                <div class="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="date" class="form-label">BULAN/TAHUN</label>
                                <input type="month" value="{{ $date }}" name="date"
                                    class="form-control" id="date">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="unit" class="form-label">UNIT</label>
                                <select class="form-select select2" style="width: 100%;" name="unit"
                                    aria-label="Default select example" id="unit" required>
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
                        </div>

                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label text-white d-block">placeholder</label>
                                <a target="_blank"
                                    href="/report/monthly/pdf?date={{ $date }}&unit={{ request()->query('unit') }}&company={{ request()->query('company', $defaluteCompany->id) }}"
                                    class="btn btn-danger {{ !request()->query('unit') ? 'disabled' : '' }}">
                                    Cetak PDF
                                </a>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-3 ms-auto">
                                <label class="form-label text-white d-block">placeholder</label>
                                <input type="text" id="global-search" class="form-control"
                                    placeholder="Search...">
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap ms-6">
                <table id="dataTable" class="table table-bordered table-striped search-table">
                    <thead class="table-light">
                    <tr class="text-nowrap">
                        <th scope="col">No</th>
                        <th scope="col">TANGGAL</th>
                        <th scope="col">SUPIR 1</th>
                        <th scope="col">SUPIR 2</th>
                        <th scope="col">NO POLISI</th>
                        <th scope="col">TUJUAN</th>
                    </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($record as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Carbon\Carbon::parse($row->date)->translatedFormat('d F Y') }}</td>
                                <td>{{ isset($row->driver_1) ? strtoupper($row->driver_1) : 'NONE' }}</td>
                                <td>{{ isset($row->driver_2) ? strtoupper($row->driver_2) : 'NONE' }}</td>
                                <td>{{ $row->no_pol }}</td>
                                <td>{{ $row->destination }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            width: 100%;
            height: 40px;
            display: flex;
            align-items: center;
        }
    </style>
@endpush

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const datatable = $('#dataTable').DataTable({
            dom: 'lBfrtip',
            buttons: [
                { extend: 'copy', exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'csv', exportOptions: { columns: ':not(:last-child)' } },
                {
                    extend: 'excelHtml5',
                    text: "<i class='bx bx-export me-1'></i> Export Excel",
                    title: null,
                    filename: function() {
                        let date = "{{ request()->query('date', '') }}";
                        let unit = "{{ request()->query('unit', '') }}";
                        let company = "{{ $company->where('id', request()->query('company'))->first()->code ?? 'Perusahaan' }}";
                        return 'Laporan_Bulanan_' + company + '_' + unit + '_' + date;
                    },
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];

                        var company = "{{ $company->where('id', request()->query('company'))->first()->code ?? 'PERUSAHAAN' }}";
                        var unit = "{{ request()->query('unit', 'UNIT') }}";
                        var date = "{{ \Carbon\Carbon::parse(request()->query('date'))->translatedFormat('F Y') }}";

                        var heading = `
                            <row r="1">
                                <c t="inlineStr" r="A1"><is><t>LAPORAN BULANAN</t></is></c>
                            </row>
                            <row r="2">
                                <c t="inlineStr" r="A2"><is><t>Perusahaan: ${company}</t></is></c>
                            </row>
                            <row r="3">
                                <c t="inlineStr" r="A3"><is><t>Unit: ${unit}</t></is></c>
                            </row>
                            <row r="4">
                                <c t="inlineStr" r="A4"><is><t>Bulan/Tahun: ${date}</t></is></c>
                            </row>
                            <row r="5"></row>
                        `;

                        var rows = $(sheet).find('sheetData row');
                        rows.each(function(index, row) {
                            var r = parseInt($(row).attr('r'));
                            $(row).attr('r', r + 5);
                            $(row).find('c').each(function() {
                                var cellRef = $(this).attr('r');
                                var col = cellRef.replace(/[0-9]/g, '');
                                var rowNumber = parseInt(cellRef.replace(/[A-Z]/g, '')) + 5;
                                $(this).attr('r', col + rowNumber);
                            });
                        });

                        $(sheet).find('sheetData').prepend(heading);
                    },
                    exportOptions: {
                        columns: ':visible'
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

        $(document).ready(function() {
            $('.select2').select2();

            $('#company, #date, #unit').on('change', function() {
                $('#FormSearchWithQuery').submit();
            });

        });

        $('#global-search').on('keyup', function() {
            datatable.search(this.value).draw();
        });
    </script>
@endpush
