@extends('layouts.sneat')

@section('title')
    Laporan Pemeliharaan Bulanan
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body py-3">
            <h6 class="text-primary fw-semibold mb-3">Cetak Laporan Pemeliharaan Bulanan</h6>

            <form action="{{ route('report.maintenances.monthly') }}" method="GET" class="row g-2 align-items-end" id="dateForm">
                <div class="col-sm-6 col-md-3">
                    <label for="date" class="form-label small mb-1">Bulan/Tahun</label>
                    <input type="month" value="{{ $date }}" name="date" class="form-control form-control-sm" id="date">
                </div>

                <div class="col-sm-6 col-md-3">
                    <label for="company" class="form-label small mb-1">Perusahaan</label>
                    <select class="form-select form-select-sm select2" name="company" id="company">
                        <option value="">Semua perusahaan</option>
                        @foreach ($companies as $row)
                            <option value="{{ $row->id }}" {{ (int) $companyId === (int) $row->id ? 'selected' : '' }}>
                                {{ $row->name ?: $row->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-3">
                    <label for="unit" class="form-label small mb-1">Unit</label>
                    <select class="form-select form-select-sm select2" name="unit" id="unit" required>
                        <option selected disabled>Pilih kendaraan</option>
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
                    </select>
                </div>

                <div class="col-sm-6 col-md-3">
                    <a target="_blank"
                        href="{{ route('report.maintenances.monthly.pdf', ['date' => $date, 'unit' => request()->query('unit')]) }}"
                        class="btn btn-sm btn-primary w-100 {{ !request()->query('unit') ? 'disabled' : '' }}"><i class="bx bx-printer me-1"></i> Cetak PDF</a>
                </div>
            </form>
        </div>
    </div>

    @if ($table)
        <div class="mb-3 ms-2">
            <h6 class="font-weight-bold mb-0">
                <span class="text-primary">UNIT &nbsp;:</span>
                &nbsp;{{ $unit?->plat_new ?? request()->query('unit') }}
                {{ $unit?->type ? '/ ' . $unit->type : '' }}
                {{ $unit?->entry_year ? '/ Tahun Masuk ' . $unit->entry_year : '' }}
            </h6>
        </div>

        <x-table engine="tabulator" :table="$table" layout="fitColumns" placeholder="Tidak ada data." title="Laporan Pemeliharaan Bulanan" />
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
    $('.select2').select2();

    function filterUnitOptions() {
        const companyId = $('#company').val();
        const currentUnit = $('#unit').val();

        $('#unit option').each(function() {
            const optionCompanyId = String($(this).data('company-id') || '');
            const shouldShow = !$(this).val() || (companyId && optionCompanyId === String(companyId)) || !companyId;
            $(this).prop('disabled', !shouldShow);
        });

        if (currentUnit && $('#unit option:selected').prop('disabled')) {
            $('#unit').val(null);
        }

        $('#unit').trigger('change.select2');
    }

    filterUnitOptions();

    // Company is a tenant-scoped UI filter that narrows the unit list only.
    $('#company').on('change', filterUnitOptions);

    // Month and unit drive the report, so they submit the filter form.
    $('#unit, #date').on('change', function() {
        $('#dateForm').submit();
    });
});
</script>
@endpush
