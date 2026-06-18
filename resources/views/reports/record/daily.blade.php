@extends('layouts.sneat')

@section('title')
    Laporan Harian BBM
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
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
                    <select class="form-select form-select-sm" id="company" name="company">
                        <option value="">Semua perusahaan</option>
                        @foreach ($company as $row)
                            <option value="{{ $row->id }}" {{ request()->query('company') == $row->id || (!request()->query('company') && $row->code === 'TTI') ? 'selected' : '' }}>
                                {{ $row->code ?: $row->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-auto">
                    <a target="_blank"
                        href="{{ route('report.daily.pdf', ['category' => $category, 'date' => request()->query('date') ?? Carbon\Carbon::now()->format('Y-m-d'), 'company' => $QueryCompany ?? $defaluteCompany?->id]) }}"
                        class="btn btn-sm btn-primary"><i class="bx bx-printer me-1"></i> Cetak PDF</a>
                </div>
            </form>
        </div>
    </div>

    <x-table engine="tabulator" :table="$table" layout="fitColumns" placeholder="Tidak ada data." title="Laporan Harian BBM" />
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('#dates, #company, #category').forEach(function (el) {
        el.addEventListener('change', function () {
            document.getElementById('filtersForm').submit();
        });
    });
</script>
@endpush
