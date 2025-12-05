@extends('layouts.sneat')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
    <div class="card-body">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Cetak Laporan Pemeliharaan Tahunan</h5>
        </div>

    <form class="row g-2 align-items-end ms-6 mb-3" method="GET" id="dateForm">
        <div class="col-auto">
            <label for="year" class="form-label">TAHUN</label>
            <select name="year" class="form-select select2" id="year" style="width: 200px">
                <option value="">- PILIH TAHUN</option>
                @php
                    $currentYear = date('Y');
                    $startYear = 2021;
                    for ($year = $currentYear; $year >= $startYear; $year--) {
                        $selected = $year == request()->query('year') ? 'selected' : '';
                        echo "<option value=\"$year\" $selected>$year</option>";
                    }
                @endphp
            </select>
        </div>

        <div class="col-auto">
            <a target="_blank"
                href="/report/maintenances/year/pdf?year={{ request()->query('year') }}"
                class="btn btn-danger">Cetak PDF</a>
        </div>

        <div class="col-auto">
            <a href="{{ url('/report/maintenances/year/excel') . '?year=' . request()->query('year', '') }}"
            class="btn btn-secondary">
            <i class='bx bx-export me-1'></i> Cetak Excel
            </a>
        </div>

    </form>

        @if (request()->query('year'))
            <div class="table-responsive text-nowrap ms-6">
            <table id="dataTable" class="table table-bordered table-striped">
                <thead class="table-light">
                <tr class="text-nowrap">
                    <th class="text-center" scope="col">No</th>
                    <th class="text-center" scope="col">KENDARAAN</th>
                    @foreach (['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'] as $month)
                        <th class="text-center" scope="col">{{ $month }}</th>
                    @endforeach
                    <th class="text-center" scope="col">TOTAL</th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @php
                        $monthlyTotals = array_fill(1, 12, 0);
                        $overallTotal = 0;
                    @endphp

                    @foreach ($mtc->groupBy('no_pol') as $noPol => $rows)
                        @php
                            $vehicle = $vehicles->where('plat_new', $noPol)->first();
                            $totalAmount = 0;
                            $monthlySums = array_fill(1, 12, 0);

                            foreach ($rows as $row) {
                                $monthlySums[$row->month] += $row->total_amount;
                                $monthlyTotals[$row->month] += $row->total_amount;
                                $totalAmount += $row->total_amount;
                            }

                            $overallTotal += $totalAmount;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                {{ $vehicle ? $vehicle->plat_new . ' / ' . $vehicle->type : 'Unknown Vehicle' }}
                            </td>
                            @for ($month = 1; $month <= 12; $month++)
                                <td class="text-right">{{ number_format($monthlySums[$month]) }}</td>
                            @endfor
                            <td class="text-right">{{ number_format($totalAmount) }}</td>
                        </tr>
                    @endforeach

                    <tr class="fw-bold">
                        <td colspan="2" class="text-center font-weight-bold">TOTAL</td>
                        @foreach ($monthlyTotals as $total)
                            <td class="text-right font-weight-bold">{{ number_format($total) }}</td>
                        @endforeach
                        <td class="text-right font-weight-bold">{{ number_format($overallTotal) }}</td>
                    </tr>
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

        .table th,
        .table td {
            text-align: center;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
@endpush

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            $('.select2').select2().on('change', function() {
                $('#dateForm').submit();
            })
        });
    </script>
@endpush
