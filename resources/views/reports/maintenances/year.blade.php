@extends('layouts.sneat')

@section('title')
    Laporan Pemeliharaan Tahunan
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
    <div class="card-body">
        <h6 class="text-primary fw-semibold mb-3">Cetak Laporan Pemeliharaan Tahunan</h6>

    <form class="row g-2 align-items-end mb-1" action="{{ route('report.maintenances.year') }}" method="GET" id="dateForm">
        <div class="col-sm-6 col-md-3">
            <label for="year" class="form-label small mb-1">Tahun</label>
            <select name="year" class="form-select form-select-sm select2" id="year">
                <option value="">Pilih tahun</option>
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

        <div class="col-sm-6 col-md-3">
            <label for="company" class="form-label small mb-1">Perusahaan</label>
            <select name="company" class="form-select form-select-sm select2" id="company">
                <option value="">Semua perusahaan</option>
                @foreach ($companies as $row)
                    <option value="{{ $row->id }}" {{ (int) $companyId === (int) $row->id ? 'selected' : '' }}>{{ $row->name ?: $row->code }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6 col-md-auto">
            <a target="_blank"
                href="{{ route('report.maintenances.year.pdf', ['year' => request()->query('year'), 'company' => $companyId]) }}"
                class="btn btn-sm btn-primary"><i class="bx bx-printer me-1"></i> Cetak PDF</a>
        </div>

        <div class="col-sm-6 col-md-auto">
            <a href="{{ route('report.maintenances.year.excel', ['year' => request()->query('year', ''), 'company' => $companyId]) }}"
                class="btn btn-sm btn-outline-primary"><i class="bx bx-export me-1"></i> Cetak Excel</a>
        </div>

    </form>
    </div>
    </div>

    @if (request()->query('year'))
        <x-table engine="tabulator" :table="$table" layout="fitDataFill" placeholder="Tidak ada data." title="Laporan Pemeliharaan Tahunan" />
    @endif
</div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Match select2 to Bootstrap's form-select-sm sizing. */
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.15rem 0.5rem;
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2().on('change', function() {
                $('#dateForm').submit();
            });
        });
    </script>
@endpush
