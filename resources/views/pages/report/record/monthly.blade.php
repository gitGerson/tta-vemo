@extends('layouts.sneat')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            @php
                $selectedCompanyId = request()->query('company') ?? $defaluteCompany?->id;
            @endphp

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
                                    {{ (string) $selectedCompanyId === (string) $row->id ? 'selected' : '' }}
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
                                                    data-company-id="{{ $row->company_id }}"
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
                                                    data-company-id="{{ $row->company_id }}"
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
                                    href="/report/monthly/pdf?date={{ $date }}&unit={{ request()->query('unit') }}&company={{ $selectedCompanyId }}"
                                    class="btn btn-danger {{ !request()->query('unit') ? 'disabled' : '' }}">
                                    Cetak PDF
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <div class="report-table-toolbar ms-6 mb-3">
                <div id="dataTableButtons" class="report-table-buttons"></div>
                <div class="report-table-search">
                    <input type="text" id="global-search" class="form-control" placeholder="Search...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped compact-report-table">
                    <thead class="table-light">
                        <tr class="text-nowrap">
                            <th scope="col" rowspan="2">No</th>
                            <th scope="col" rowspan="2">TANGGAL</th>
                            <th scope="col" rowspan="2">JENIS</th>
                            <th scope="col" rowspan="2">NOPOL</th>
                            <th scope="col" rowspan="2">TUJUAN</th>
                            <th scope="col" colspan="3">KILOMETER</th>
                            <th scope="col" rowspan="2">LITER</th>
                            <th scope="col" rowspan="2">RUPIAH</th>
                            <th scope="col" rowspan="2">SUPIR</th>
                            <th scope="col" rowspan="2">SKALA</th>
                        </tr>
                        <tr class="text-nowrap">
                            <th scope="col">AWAL</th>
                            <th scope="col">AKHIR</th>
                            <th scope="col">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $vehiclesByPlate = $vehicles->flatMap(function ($item) {
                                return collect(array_filter([$item->plat_new, $item->plat_old]))
                                    ->mapWithKeys(fn ($plate) => [$plate => $item]);
                            });
                        @endphp
                        @foreach ($record as $row)
                            @php
                                $selectedVehicle = $vehiclesByPlate->get($row->no_pol);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                                <td>{{ $selectedVehicle?->type ?? '-' }}</td>
                                <td>{{ $row->no_pol }}</td>
                                <td>{{ $row->destination }}</td>
                                <td class="text-end">{{ $row->start_km ?? 0 }}</td>
                                <td class="text-end">{{ $row->finish_km ?? 0 }}</td>
                                <td class="text-end">{{ $row->totals ?? 0 }}</td>
                                <td class="text-end">{{ $row->bbm ?? 0 }}</td>
                                <td class="text-end">{{ number_format((float) ($row->idr ?? 0), 0, ',', '.') }}</td>
                                <td>{{ strtoupper($row->driver_1 ?: 'NONE') }} - {{ strtoupper($row->driver_2 ?: 'NONE') }}</td>
                                <td class="text-center">{{ $row->scale ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="fw-semibold">JUMLAH</td>
                            <td class="text-end fw-semibold">{{ $record->sum(fn ($item) => (float) ($item->totals ?? 0)) }}</td>
                            <td class="text-end fw-semibold">{{ $record->sum(fn ($item) => (float) ($item->bbm ?? 0)) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($record->sum(fn ($item) => (float) ($item->idr ?? 0)), 0, ',', '.') }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
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
            dom: 't',
            paging: false,
            info: false,
            buttons: [
                { extend: 'copy', exportOptions: { columns: ':visible' } },
                { extend: 'csv', exportOptions: { columns: ':visible' } },
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

        $(document).ready(function() {
            $('.select2').select2();
            datatable.buttons().container().appendTo('#dataTableButtons');

            function filterUnitOptions() {
                const companyId = $('#company').val();
                const currentUnit = $('#unit').val();

                $('#unit option').each(function() {
                    const optionCompanyId = String($(this).data('company-id') || '');
                    const shouldShow = !$(this).val() || (companyId && optionCompanyId === String(companyId));
                    $(this).prop('disabled', !shouldShow);
                });

                if (currentUnit && $('#unit option:selected').prop('disabled')) {
                    $('#unit').val(null);
                }

                $('#unit').trigger('change.select2');
            }

            filterUnitOptions();

            $('#company, #date, #unit').on('change', function() {
                if ($(this).attr('id') === 'company') {
                    filterUnitOptions();
                }
                $('#FormSearchWithQuery').submit();
            });

        });

        $('#global-search').on('keyup', function() {
            datatable.search(this.value).draw();
        });
    </script>
@endpush
