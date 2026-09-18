@extends('layouts.sneat')

<x-form.select2-assets />

@section('title')
    Report Generator BBM
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            @php
                $isDay = $filters['type'] === \App\Http\Controllers\ReportGeneratorController::TYPE_DAY;
                $inputType = $isDay ? 'date' : 'month';
                $exportQuery = [
                    'type' => $filters['type'],
                    'start' => $filters['start'],
                    'end' => $filters['end'],
                    'companies' => $filters['companies'],
                    'unit' => $filters['unit'],
                ];
            @endphp

            <h6 class="text-primary fw-semibold mb-3">Report Generator BBM</h6>

            <form action="{{ route('report.generator') }}" id="FormReportGenerator" class="row g-2 align-items-start">
                <div class="col-12 col-xl filter-company">
                    <label for="companies" class="form-label small mb-1">Perusahaan</label>
                    <select class="form-select form-select-sm" id="companies" name="companies[]" multiple
                        data-select2-enabled="1" data-select2-placeholder="Semua perusahaan" data-select2-allow-clear="1">
                        @foreach ($company as $row)
                            <option
                                {{ in_array($row->id, $filters['companies'], true) ? 'selected' : '' }}
                                value="{{ $row->id }}">{{ $row->name ?: $row->code }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-xl-auto filter-type">
                    <label for="type" class="form-label small mb-1">Tipe Periode</label>
                    <select class="form-select form-select-sm" id="type" name="type">
                        <option value="day" {{ $isDay ? 'selected' : '' }}>Harian</option>
                        <option value="month" {{ $isDay ? '' : 'selected' }}>Bulanan</option>
                    </select>
                </div>

                <div class="col-sm-6 col-xl-auto filter-date">
                    <label for="start" class="form-label small mb-1">Dari</label>
                    <input type="{{ $inputType }}" value="{{ $filters['start'] }}" name="start" class="form-control form-control-sm" id="start" required>
                </div>

                <div class="col-sm-6 col-xl-auto filter-date">
                    <label for="end" class="form-label small mb-1">Sampai</label>
                    <input type="{{ $inputType }}" value="{{ $filters['end'] }}" name="end" class="form-control form-control-sm" id="end" required>
                </div>

                <div class="col-sm-6 col-xl-auto filter-unit">
                    <label for="unit" class="form-label small mb-1">Unit</label>
                    <select class="form-select form-select-sm" name="unit" id="unit" data-select2-enabled="1">
                        <option value="" {{ $filters['unit'] === null ? 'selected' : '' }}>Semua kendaraan</option>
                        @foreach ($vehicles as $row)
                            @if (!empty($row->plat_new))
                                <option
                                    {{ $filters['unit'] === $row->plat_new ? 'selected' : '' }}
                                    data-company-id="{{ $row->company_id }}"
                                    value="{{ $row->plat_new }}">
                                    {{ $row->plat_new }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-xl-auto">
                    {{-- Empty label keeps the buttons level with the inputs when the row is top-aligned. --}}
                    <label class="form-label small mb-1 d-none d-xl-block">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bx bx-search me-1"></i> Tampilkan</button>
                        @if (auth()->user()?->hasPermission('reports.generator.export'))
                            <a href="{{ route('report.generator.excel', $exportQuery) }}"
                                class="btn btn-sm btn-primary"><i class="bx bx-spreadsheet me-1"></i> Export Excel</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <x-table engine="tabulator" :table="$table" layout="fitColumns" placeholder="Tidak ada data." title="Report Generator BBM" />
</div>

@endsection

@push('styles')
    <style>
        /* One-row layout on wide screens: fixed-width filters, company takes the rest. */
        @media (min-width: 1200px) {
            #FormReportGenerator .filter-company { min-width: 0; }
            #FormReportGenerator .filter-type { width: 120px; }
            #FormReportGenerator .filter-date { width: 165px; }
            #FormReportGenerator .filter-unit { width: 180px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            function lastDayOfMonth(month) {
                const [year, mon] = month.split('-').map(Number);
                const day = new Date(year, mon, 0).getDate();

                return month + '-' + String(day).padStart(2, '0');
            }

            function filterUnitOptions() {
                const companyIds = ($('#companies').val() || []).map(String);
                const currentUnit = $('#unit').val();

                $('#unit option').each(function() {
                    const optionCompanyId = String($(this).data('company-id') || '');
                    const shouldShow = !$(this).val() || companyIds.length === 0 || companyIds.includes(optionCompanyId);
                    $(this).prop('disabled', !shouldShow);
                });

                if (currentUnit && $('#unit option:selected').prop('disabled')) {
                    $('#unit').val('');
                }

                $('#unit').trigger('change.select2');
            }

            filterUnitOptions();

            $('#companies').on('change', filterUnitOptions);

            $('#type').on('change', function() {
                const $start = $('#start');
                const $end = $('#end');
                const start = $start.val();
                const end = $end.val();

                if ($(this).val() === 'month') {
                    $start.attr('type', 'month').val(start ? start.substring(0, 7) : '');
                    $end.attr('type', 'month').val(end ? end.substring(0, 7) : '');
                } else {
                    $start.attr('type', 'date').val(start ? start + '-01' : '');
                    $end.attr('type', 'date').val(end ? lastDayOfMonth(end) : '');
                }
            });
        });
    </script>
@endpush
