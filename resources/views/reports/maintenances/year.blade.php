@extends('layouts.sneat')

<x-form.select2-assets />

@section('title')
    Laporan Pemeliharaan Tahunan
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $canPrint = $companyId !== null;
        $printTitle = $canPrint ? null : 'Pilih satu perusahaan untuk mencetak';
    @endphp

    <div class="card mb-4">
    <div class="card-body">
        <h6 class="text-primary fw-semibold mb-3">Cetak Laporan Pemeliharaan Tahunan</h6>

    <form class="row g-2 align-items-end mb-1" action="{{ route('report.maintenances.year') }}" method="GET" id="dateForm">
        <div class="col-sm-6 col-md-3">
            <label for="year" class="form-label small mb-1">Tahun</label>
            <select name="year" class="form-select form-select-sm" id="year" data-select2-enabled="1">
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
            <select name="company" class="form-select form-select-sm" id="company" data-select2-enabled="1">
                <option value="" {{ $companyId === null ? 'selected' : '' }}>Semua perusahaan</option>
                @foreach ($companies as $row)
                    <option value="{{ $row->id }}" {{ (int) $companyId === (int) $row->id ? 'selected' : '' }}>{{ $row->name ?: $row->code }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6 col-md-auto">
            <a target="_blank"
                href="{{ $canPrint ? route('report.maintenances.year.pdf', ['year' => request()->query('year'), 'company' => $companyId]) : '#' }}"
                class="btn btn-sm btn-primary {{ $canPrint ? '' : 'disabled' }}"
                title="{{ $printTitle }}"
                aria-disabled="{{ $canPrint ? 'false' : 'true' }}"><i class="bx bx-printer me-1"></i> Cetak PDF</a>
        </div>

        <div class="col-sm-6 col-md-auto">
            <a href="{{ $canPrint ? route('report.maintenances.year.excel', ['year' => request()->query('year', ''), 'company' => $companyId]) : '#' }}"
                class="btn btn-sm btn-outline-primary {{ $canPrint ? '' : 'disabled' }}"
                title="{{ $printTitle }}"
                aria-disabled="{{ $canPrint ? 'false' : 'true' }}"><i class="bx bx-export me-1"></i> Cetak Excel</a>
        </div>

    </form>
    </div>
    </div>

    @if (request()->query('year'))
        <x-table engine="tabulator" :table="$table" layout="fitDataFill" placeholder="Tidak ada data." title="Laporan Pemeliharaan Tahunan" />
    @endif
</div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#year, #company').on('change', function() {
                $('#dateForm').submit();
            });
        });
    </script>
@endpush
