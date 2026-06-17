@extends('layouts.sneat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
    <!-- Content -->
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- KPI cards -->
        <div class="row">
            @foreach ($kpis as $kpi)
                <div class="col-xl-3 col-md-6 col-6 mb-6">
                    <a href="{{ $kpi['link'] }}" class="card h-100 text-body">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-{{ $kpi['color'] }}"><i
                                            class="icon-base bx {{ $kpi['icon'] }}"></i></span>
                                </div>
                            </div>
                            <p class="mb-1">{{ $kpi['label'] }}</p>
                            <h4 class="card-title mb-1">{{ $kpi['value'] }}</h4>
                            <small class="text-body-secondary">{{ $kpi['sub'] }}</small>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Financial & utilization summary -->
        <div class="row">
            @if ($financials['visible'])
                <!-- Spend Trend -->
                <div class="col-lg-8 mb-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <h5 class="mb-1 me-2">Spend Trend</h5>
                                <p class="card-subtitle">Maintenance vs Fuel · 6 bulan terakhir</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <script type="application/json" id="spendTrendData">@json($financials['trend'])</script>
                            <div id="spendTrendChart"></div>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Spend & utilization stat cards -->
            <div class="col-lg-4 mb-6">
                <div class="row">
                    @if ($financials['visible'])
                        @foreach (['maintenance' => ['Maintenance Spend', 'warning', 'bx-wrench'], 'fuel' => ['Fuel Spend', 'info', 'bx-gas-pump']] as $key => $meta)
                            @php $stat = $financials['summary'][$key]; @endphp
                            <div class="col-md-6 col-lg-12 mb-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="fw-medium d-block mb-1">{{ $meta[0] }}</span>
                                                <h4 class="mb-0">{{ $stat['formatted'] }}</h4>
                                            </div>
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-{{ $meta[1] }}"><i
                                                        class="icon-base bx {{ $meta[2] }}"></i></span>
                                            </div>
                                        </div>
                                        @if ($stat['delta'] === null)
                                            <small class="text-body-secondary">{{ $financials['summary']['month_label'] }}</small>
                                        @else
                                            <small class="{{ $stat['delta'] > 0 ? 'text-danger' : 'text-success' }}">
                                                <i class="icon-base bx {{ $stat['delta'] > 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                                {{ abs($stat['delta']) }}% vs bulan lalu
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <!-- KM Ditempuh -->
                    <div class="col-md-6 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="content-left">
                                        <span class="fw-medium d-block mb-1">KM Ditempuh
                                            <i class="icon-base bx bx-info-circle text-body-secondary"
                                                style="cursor: help; font-size: 0.85rem;" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Total jarak tempuh dari data perjalanan bulan ini"></i>
                                        </span>
                                        <h4 class="mb-0">{{ $utilization['km'] }}</h4>
                                    </div>
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-primary"><i
                                                class="icon-base bx bx-tachometer"></i></span>
                                    </div>
                                </div>
                                <small class="text-body-secondary">bulan ini</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condition, composition & maintenance trend -->
        <div class="row">
            <!-- Vehicle Condition -->
            <div class="col-md-6 col-lg-4 mb-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-1">Vehicle Condition</h5>
                        <p class="card-subtitle mb-0">{{ $conditionSummary['active'] }} aktif · {{ $conditionSummary['total'] }} total</p>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <script type="application/json" id="vehicleConditionData">@json($conditionSummary)</script>
                        <div id="vehicleConditionChart"></div>
                    </div>
                </div>
            </div>
            <!-- Fleet Composition -->
            <div class="col-md-6 col-lg-4 mb-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-1">Fleet Composition</h5>
                        <p class="card-subtitle mb-0">Per tipe kendaraan</p>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <script type="application/json" id="fleetCompositionData">@json($fleetComposition)</script>
                        <div id="fleetCompositionChart"></div>
                    </div>
                </div>
            </div>
            <!-- Maintenance Volume -->
            <div class="col-md-6 col-lg-4 mb-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-1">Maintenance Volume</h5>
                        <p class="card-subtitle mb-0">Rutin vs non-rutin · 6 bulan</p>
                    </div>
                    <div class="card-body">
                        <script type="application/json" id="maintenanceVolumeData">@json($maintenanceVolume)</script>
                        <div id="maintenanceVolumeChart"></div>
                    </div>
                </div>
            </div>
        </div>

        @php
            // Shared 3-column layout (Plat / Info / Status) for the reminder tables.
            $reminderColumns = [
                ['title' => 'Plat', 'field' => 'plate', 'formatter' => 'html', 'headerSort' => false],
                ['title' => 'Info', 'field' => 'detail', 'headerSort' => false],
                ['title' => 'Status', 'field' => 'status', 'formatter' => 'html', 'headerSort' => false, 'hozAlign' => 'right'],
            ];
            $plateLink = fn (array $i): string => '<a href="'.e($i['link']).'" class="fw-medium text-body">'.e($i['plate']).'</a>';
        @endphp

        <div class="row">
            <!-- Service Reminder -->
            @if ($serviceReminders['visible'])
                @php
                    $serviceRows = collect($serviceReminders['items'])->map(fn (array $i): array => [
                        'plate' => $plateLink($i),
                        'detail' => $i['current'].' / '.$i['threshold'].' km · last '.$i['last_service'],
                        'status' => '<span class="badge bg-label-danger">'.e($i['over']).' km over</span>',
                    ])->all();
                @endphp
                <div class="col-md-6 col-lg-6 order-1 mb-6">
                    <x-dashboard.list-table id="dashServiceReminder" title="Service Reminder"
                        subtitle="{{ $serviceReminders['count'] }} Due" :columns="$reminderColumns" :rows="$serviceRows"
                        placeholder="Tidak ada kendaraan jatuh tempo servis." />
                </div>
            @endif
            <!--/ Service Reminder -->

            <!-- Foto Kendaraan -->
            @if ($photoReminders['visible'])
                @php
                    $photoRows = collect($photoReminders['items'])->map(fn (array $i): array => [
                        'plate' => $plateLink($i),
                        'detail' => 'Foto '.$i['period_label'].' belum lengkap',
                        'status' => '<span class="badge bg-label-warning">'.e($i['completed']).'/'.e($i['total']).'</span>',
                    ])->all();
                @endphp
                <div class="col-md-6 col-lg-6 order-1 mb-6">
                    <x-dashboard.list-table id="dashPhotoReminder" title="Foto Kendaraan"
                        subtitle="{{ $photoReminders['count'] }} Belum lengkap" :columns="$reminderColumns"
                        :rows="$photoRows" placeholder="Semua kendaraan sudah lengkap foto bulan ini." />
                </div>
            @endif
            <!--/ Foto Kendaraan -->

            <!-- Insurance Reminder -->
            @if ($insuranceReminders['visible'])
                @php
                    $insuranceRows = collect($insuranceReminders['items'])->map(function (array $i): array {
                        $overdue = ($i['state'] ?? null) === 'overdue';
                        $status = $overdue
                            ? '<span class="badge bg-label-danger">Overdue '.e($i['days_overdue']).'h</span>'
                            : '<span class="badge bg-label-'.($i['threshold'] <= 7 ? 'danger' : 'warning').'">H-'.e($i['days_left']).'</span>';

                        return [
                            'plate' => '<a href="'.e($i['link']).'" class="fw-medium text-body">'.e($i['plate']).'</a>',
                            'detail' => $i['insurer'].' · '.($overdue ? 'kedaluwarsa' : 'berakhir').' '.$i['end_date'],
                            'status' => $status,
                        ];
                    })->all();
                @endphp
                <div class="col-md-6 col-lg-6 order-1 mb-6">
                    <x-dashboard.list-table id="dashInsuranceReminder" title="Insurance Reminder"
                        subtitle="{{ $insuranceReminders['count'] }} Perlu perhatian" :columns="$reminderColumns"
                        :rows="$insuranceRows" placeholder="Tidak ada asuransi yang perlu perhatian.">
                        <x-slot:action>
                            <a href="{{ route('vehicle-insurances.index') }}"
                                class="btn btn-sm btn-outline-primary">Lihat</a>
                        </x-slot:action>
                    </x-dashboard.list-table>
                </div>
            @endif
            <!--/ Insurance Reminder -->

            <!-- Recent Maintenance -->
            @php
                $recentRows = collect($recentMaintenance)->map(fn (array $i): array => [
                    'plate' => $plateLink($i),
                    'detail' => $i['meta'],
                    'status' => '<span class="badge bg-label-'.$i['color'].'">'.e($i['badge']).'</span>',
                ])->all();
            @endphp
            <div class="col-md-6 col-lg-6 order-2 mb-6">
                <x-dashboard.list-table id="dashRecentMaintenance" title="Recent Maintenance" :columns="$reminderColumns"
                    :rows="$recentRows" placeholder="No recent maintenance records.">
                    <x-slot:action>
                        <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </x-slot:action>
                </x-dashboard.list-table>
            </div>
            <!--/ Recent Maintenance -->
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('sneat/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    @vite('resources/js/modules/dashboard.js')
@endpush
