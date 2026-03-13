@extends('layouts.sneat')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cetak Laporan Harian</h5>
            </div>

            <form method="GET" action="{{ route('report.daily') }}" class="row g-3 align-items-end my-3 ms-6" id="filtersForm">
                <div class="col-md-3">
                    <label for="category" class="form-label">FILTER KATEGORI</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="KESELURUHAN" {{ $category === 'KESELURUHAN' ? 'selected' : '' }}>- KESELURUHAN -</option>
                        <option value="UMUM" {{ $category === 'UMUM' ? 'selected' : '' }}>- UMUM -</option>
                        <option value="PRIBADI" {{ $category === 'PRIBADI' ? 'selected' : '' }}>- PRIBADI -</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dates" class="form-label">TANGGAL</label>
                    <input type="date" value="{{ request()->query('date') ?? Carbon\Carbon::now()->format('Y-m-d') }}" name="date"
                        class="form-control" id="dates">
                </div>
                <div class="col-md-3">
                    <label for="company" class="form-label">PERUSAHAAN</label>
                    <select class="form-select select2" id="company" name="company">
                        <option value="">- pilih perusahaan -</option>
                        @foreach ($company as $row)
                            <option value="{{ $row->id }}" {{ request()->query('company') == $row->id || (!request()->query('company') && $row->code === 'TTI') ? 'selected' : '' }}>
                                {{ $row->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <label class="form-label text-white d-block">placeholder</label>
                    <a target="_blank"
                        href="/report/daily/pdf?category={{ $category }}&date={{ request()->query('date') ?? Carbon\Carbon::now()->format('Y-m-d') }}&company={{ $QueryCompany ?? $defaluteCompany->id }}"
                        class="btn btn-danger px-3">Cetak PDF</a>
                </div>
            </form>

            @php
                $allowedPlates = collect();
                $vehiclesByPlate = $vehicle->flatMap(function ($item) {
                    return collect(array_filter([$item->plat_new, $item->plat_old]))
                        ->mapWithKeys(fn ($plate) => [$plate => $item]);
                });

                if ($category === 'UMUM' || $category === 'PRIBADI') {
                    $allowedPlates = $vehicle
                        ->filter(function ($v) use ($category) {
                            return $category === 'PRIBADI' ? (int) $v->isprivate === 1 : (int) $v->isprivate === 0;
                        })
                        ->flatMap(function ($v) {
                            return array_filter([$v->plat_new, $v->plat_old]);
                        })
                        ->values();
                }

                $filteredRecords = $record->filter(function ($item) use ($category, $allowedPlates) {
                    return $category === 'KESELURUHAN' || $allowedPlates->contains($item->no_pol);
                })->values();
            @endphp

            <div class="report-table-toolbar ms-6 mb-3">
                <div id="dataTableButtons" class="report-table-buttons"></div>
                <div class="report-table-search">
                    <input type="text" id="global-search" class="form-control" placeholder="Search...">
                </div>
            </div>

            <div class="table-responsive ms-6">
                <table id="dataTable" class="table table-bordered table-striped compact-report-table">
                    <thead class="table-light">
                        <tr class="text-nowrap">
                            <th scope="col" rowspan="2">No</th>
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
                        @php $rowNumber = 1; @endphp
                        @foreach ($filteredRecords as $row)
                            @php
                                $selectedVehicle = $vehiclesByPlate->get($row->no_pol);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $rowNumber++ }}</td>
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
                            <td colspan="6" class="fw-semibold">JUMLAH</td>
                            <td class="text-end fw-semibold">{{ $filteredRecords->sum(fn ($item) => (float) ($item->totals ?? 0)) }}</td>
                            <td class="text-end fw-semibold">{{ $filteredRecords->sum(fn ($item) => (float) ($item->bbm ?? 0)) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($filteredRecords->sum(fn ($item) => (float) ($item->idr ?? 0)), 0, ',', '.') }}</td>
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
</style>
@endpush

@push('addon-script')
<script>
$(document).ready(function() {
    const table = $('#dataTable').DataTable({
        dom: 't',
        paging: false,
        info: false,
        buttons: [
            { extend: 'copy', exportOptions: { columns: ':visible' } },
            { extend: 'csv', exportOptions: { columns: ':visible' } },
            {
                extend: 'excelHtml5',
                text: "<i class='bx bx-export me-1'></i> Export Excel",
                title: '',
                filename: function() {
                    const category = $('#category').val() || 'KESELURUHAN';
                    const date = $('#dates').val() || new Date().toISOString().split('T')[0];
                    const companyText = $('#company option:selected').text();
                    const company = companyText && companyText !== '- pilih perusahaan -' ? companyText : 'ALL';
                    return 'Laporan_Harian_' + company + '_' + category + '_' + date;
                },
                exportOptions: { columns: ':visible' },
                customize: function (xlsx) {
                    const sheet = xlsx.xl.worksheets['sheet1.xml'];
                    const filterKategori = $('#category').val() || 'KESELURUHAN';
                    const filterTanggal = $('#dates').val() || new Date().toISOString().split('T')[0];
                    let filterPerusahaan = $('#company option:selected').text() || 'Semua Perusahaan';
                    if (filterPerusahaan === '- pilih perusahaan -') {
                        filterPerusahaan = 'Semua Perusahaan';
                    }

                    function createCell(r, c, value) {
                        return '<c r="' + c + r + '" t="inlineStr"><is><t>' + value + '</t></is></c>';
                    }

                    $('row', sheet).each(function() {
                        const r = parseInt($(this).attr('r'));
                        $(this).attr('r', r + 5);
                        $(this).find('c').each(function() {
                            const cellRef = $(this).attr('r');
                            const col = cellRef.replace(/[0-9]/g, '');
                            const rowNum = parseInt(cellRef.replace(/[A-Z]/g, ''));
                            const newRowNum = rowNum + 5;
                            $(this).attr('r', col + newRowNum);
                        });
                    });

                    const row1 = '<row r="1">' + createCell(1, 'A', 'Laporan Harian') + '</row>';
                    const row2 = '<row r="2">' + createCell(2, 'A', 'Filter Kategori: ' + filterKategori) + '</row>';
                    const row3 = '<row r="3">' + createCell(3, 'A', 'Tanggal: ' + filterTanggal) + '</row>';
                    const row4 = '<row r="4">' + createCell(4, 'A', 'Perusahaan: ' + filterPerusahaan) + '</row>';
                    const row5 = '<row r="5"></row>';

                    const sheetData = sheet.getElementsByTagName('sheetData')[0];
                    sheetData.innerHTML = row1 + row2 + row3 + row4 + row5 + sheetData.innerHTML;
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

    $('#dates, #company, #category').on('change', function() {
        $('#filtersForm').submit();
    });
});
</script>
@endpush
