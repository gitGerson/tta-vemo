@extends('layouts.sneat')

@section('title')
    Laporan Harian BBM
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $canPrint = $QueryCompany !== null;
        $printTitle = $canPrint ? null : 'Pilih satu perusahaan untuk mencetak';
    @endphp

    <div class="card mb-4">
        <div class="card-body py-3">
            <h6 class="text-primary fw-semibold mb-3">Cetak Laporan Harian</h6>

            <form method="GET" action="{{ route('report.daily') }}" class="row g-2 align-items-end" id="filtersForm">
                <div class="col-sm-6 col-md-3">
                    <label for="category" class="form-label small mb-1">Kategori</label>
                    <select class="form-select form-select-sm" id="category" name="category" required>
                        <option value="KESELURUHAN" {{ $category === 'KESELURUHAN' ? 'selected' : '' }}>Keseluruhan</option>
                        <option value="UMUM" {{ $category === 'UMUM' ? 'selected' : '' }}>Umum</option>
                        <option value="PRIBADI" {{ $category === 'PRIBADI' ? 'selected' : '' }}>Pribadi</option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="dates" class="form-label small mb-1">Tanggal</label>
                    <input type="date" value="{{ request()->query('date') ?? Carbon\Carbon::now()->format('Y-m-d') }}" name="date"
                        class="form-control form-control-sm" id="dates">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="company" class="form-label small mb-1">Perusahaan</label>
                    <select class="form-select form-select-sm select2" id="company" name="company">
                        <option value="" {{ $QueryCompany === null ? 'selected' : '' }}>Semua perusahaan</option>
                        @foreach ($company as $row)
                            <option value="{{ $row->id }}" {{ $QueryCompany === $row->id ? 'selected' : '' }}>
                                {{ $row->name ?: $row->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-auto">
                    <a target="_blank"
                        href="{{ $canPrint ? route('report.daily.pdf', ['category' => $category, 'date' => request()->query('date') ?? Carbon\Carbon::now()->format('Y-m-d'), 'company' => $QueryCompany]) : '#' }}"
                        class="btn btn-sm btn-primary {{ $canPrint ? '' : 'disabled' }}"
                        title="{{ $printTitle }}"
                        aria-disabled="{{ $canPrint ? 'false' : 'true' }}"><i class="bx bx-printer me-1"></i> Cetak PDF</a>
                </div>
            </form>
        </div>
    </div>

    <x-table engine="tabulator" :table="$table" layout="fitColumns" placeholder="Tidak ada data." title="Laporan Harian BBM" />
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
    $(document).ready(function () {
        $('.select2').select2();

        $('#dates, #company, #category').on('change', function () {
            document.getElementById('filtersForm').submit();
        });
    });
</script>
@endpush
