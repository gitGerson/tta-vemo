@extends('layouts.sneat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">
    <style>
        .datepicker {
            z-index: 1080 !important;
        }

        .datepicker table tr td.active.active,
        .datepicker table tr td span.active.active,
        .datepicker table tr td.today {
            background-color: var(--bs-primary, #696cff);
            background-image: none;
            border-color: var(--bs-primary, #696cff);
        }

        /* Compact month-picker popup. */
        .datepicker.datepicker-dropdown {
            width: 216px;
            padding: 0.35rem;
            font-size: 0.75rem;
            min-width: 0;
        }

        .datepicker-dropdown .datepicker-switch,
        .datepicker-dropdown .prev,
        .datepicker-dropdown .next {
            height: 26px;
            font-size: 0.8125rem;
        }

        .datepicker-dropdown .datepicker-months table {
            width: 100%;
            table-layout: fixed;
        }

        .datepicker-dropdown .datepicker-months table tr td span {
            width: 25%;
            height: 30px;
            line-height: 30px;
            margin: 0;
            font-size: 0.75rem;
        }

        /* Give the company select a comfortable width so long names don't clip. */
        .dashboard-filter .select2-container {
            min-width: 220px;
        }

        /* Match select2 (bootstrap-5 theme) to Bootstrap's form-select-sm sizing
           so it lines up with the month input beside it. */
        .dashboard-filter .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 1.75rem 0.25rem 0.5rem;
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
        }

        .dashboard-filter .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: 1.5;
        }

        .dashboard-filter #dashboard-month-picker {
            background-color: var(--bs-paper-bg);
        }

        /* Compact dropdown panel — scoped to this select's results list only, so
           other select2 dropdowns in the app keep their default sizing. */
        #select2-dashboard-company-results .select2-results__option {
            padding: 0.3rem 0.6rem;
            font-size: 0.8125rem;
            line-height: 1.3;
        }

        .select2-dropdown:has(#select2-dashboard-company-results) .select2-search__field {
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
        }

        html[data-bs-theme='dark'] .dashboard-filter .select2-selection--single,
        html[data-bs-theme='dark'] #dashboard-month-picker {
            color: var(--bs-body-color);
            background-color: var(--bs-paper-bg) !important;
            border-color: var(--bs-border-color);
        }

        html[data-bs-theme='dark'] .dashboard-filter .select2-selection__rendered {
            color: var(--bs-body-color);
        }

        html[data-bs-theme='dark'] .select2-dropdown:has(#select2-dashboard-company-results),
        html[data-bs-theme='dark'] .select2-dropdown:has(#select2-dashboard-company-results) .select2-search__field,
        html[data-bs-theme='dark'] .datepicker.datepicker-dropdown {
            color: var(--bs-body-color);
            background-color: var(--bs-paper-bg);
            border-color: var(--bs-border-color);
        }

        html[data-bs-theme='dark'] #select2-dashboard-company-results .select2-results__option--highlighted {
            color: var(--bs-primary-contrast);
            background-color: var(--bs-primary);
        }

        html[data-bs-theme='dark'] .datepicker-dropdown .datepicker-switch:hover,
        html[data-bs-theme='dark'] .datepicker-dropdown .prev:hover,
        html[data-bs-theme='dark'] .datepicker-dropdown .next:hover,
        html[data-bs-theme='dark'] .datepicker-dropdown table tr td span:hover {
            background-color: var(--bs-secondary-bg);
        }

        /* Slim top progress bar shown while the dashboard refreshes via AJAX. */
        #dashboard-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0;
            z-index: 2000;
            opacity: 0;
            transition: width 0.2s ease, opacity 0.3s ease;
            pointer-events: none;
        }

        #dashboard-content {
            transition: opacity 0.15s ease;
        }

        #dashboard-content.is-loading {
            opacity: 0.55;
        }
    </style>
@endpush

@section('content')
    <!-- Content -->

    <div id="dashboard-progress" class="bg-primary"></div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-6">
            <div>
                <h4 class="mb-1">Dashboard</h4>
                <p class="mb-0 text-body-secondary">Ringkasan operasional armada</p>
            </div>
            <form action="{{ route('home') }}" method="GET" id="dashboard-filter" class="dashboard-filter row g-2 align-items-end">
                <div class="col-sm-auto">
                    <label for="dashboard-company" class="form-label small mb-1">Perusahaan</label>
                    <select name="company" id="dashboard-company" class="form-select form-select-sm"
                        data-select2-enabled="1">
                        @if ($dashboardContext['company_options']->count() > 1)
                            <option value="">Semua Perusahaan</option>
                        @endif
                        @foreach ($dashboardContext['company_options'] as $company)
                            <option value="{{ $company->id }}" @selected($dashboardContext['selected_company_id'] === $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-auto">
                    <label for="dashboard-month-picker" class="form-label small mb-1">Bulan Aktif</label>
                    <input type="hidden" name="month" id="dashboard-month"
                        value="{{ $dashboardContext['selected_month'] }}">
                    <input type="text" id="dashboard-month-picker" class="form-control form-control-sm"
                        autocomplete="off" readonly value="{{ $dashboardContext['month'] }}">
                </div>
            </form>
        </div>

        <div id="dashboard-content">
            @include('home.partials.content')
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('sneat/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/locales/bootstrap-datepicker.id.min.js"></script>
    @vite('resources/js/modules/select2.js')
    @vite('resources/js/modules/dashboard.js')
@endpush
