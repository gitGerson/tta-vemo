@extends('layouts.sneat')

<x-form.select2-assets />

@section('title')
    Laporan Analisa Bulanan Pemeliharaan
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            @php
                $selectedCompanyId = $companyId;
                $canPrint = $selectedCompanyId !== null;
                $printTitle = $canPrint ? null : 'Pilih perusahaan untuk mencetak';
                $exportParams = ['date' => $date, 'company' => $selectedCompanyId, 'unit' => request()->query('unit')];
            @endphp

            <h6 class="text-primary fw-semibold mb-3">Analisa Bulanan Pemeliharaan</h6>

            <form action="{{ route('report.analysis.maintenances.monthly') }}" id="FormSearchWithQuery" class="row g-2 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label for="company" class="form-label small mb-1">Perusahaan</label>
                    <select class="form-select form-select-sm" id="company" name="company" data-select2-enabled="1">
                        <option value="" {{ $selectedCompanyId === null ? 'selected' : '' }}>Pilih perusahaan</option>
                        @foreach ($companies as $row)
                            <option
                                {{ (int) $selectedCompanyId === (int) $row->id ? 'selected' : '' }}
                                value="{{ $row->id }}">{{ $row->name ?: $row->code }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-3">
                    <label for="date" class="form-label small mb-1">Bulan/Tahun</label>
                    <input type="month" value="{{ $date }}" name="date" class="form-control form-control-sm" id="date">
                </div>

                <div class="col-sm-6 col-md-3">
                    <label for="unit" class="form-label small mb-1">Unit <span class="text-muted">(opsional)</span></label>
                    <select class="form-select form-select-sm" name="unit" id="unit" data-select2-enabled="1">
                        <option value="" {{ request()->query('unit') ? '' : 'selected' }}>Semua unit</option>
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

                <div class="col-sm-6 col-md-auto d-flex gap-2">
                    <a target="_blank"
                        href="{{ $canPrint ? route('report.analysis.maintenances.monthly.pdf', $exportParams) : '#' }}"
                        class="btn btn-sm btn-primary {{ $canPrint ? '' : 'disabled' }}"
                        title="{{ $printTitle }}"
                        aria-disabled="{{ $canPrint ? 'false' : 'true' }}"><i class="bx bx-printer me-1"></i> Cetak PDF</a>
                    <a target="_blank"
                        href="{{ $canPrint ? route('report.analysis.maintenances.monthly.excel', $exportParams) : '#' }}"
                        class="btn btn-sm btn-success {{ $canPrint ? '' : 'disabled' }}"
                        title="{{ $printTitle }}"
                        aria-disabled="{{ $canPrint ? 'false' : 'true' }}"><i class="bx bx-spreadsheet me-1"></i> Excel</a>
                </div>
            </form>
        </div>
    </div>

    <x-table engine="tabulator" :table="$table" layout="fitColumns" placeholder="Pilih perusahaan dan bulan untuk menampilkan analisa." title="Laporan Analisa Bulanan Pemeliharaan" />
</div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function filterUnitOptions() {
                const companyId = $('#company').val();
                const currentUnit = $('#unit').val();

                $('#unit option').each(function() {
                    const optionCompanyId = String($(this).data('company-id') || '');
                    const shouldShow = !$(this).val() || !companyId || optionCompanyId === String(companyId);
                    $(this).prop('disabled', !shouldShow);
                });

                if (currentUnit && $('#unit option:selected').prop('disabled')) {
                    $('#unit').val('');
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
    </script>
@endpush
