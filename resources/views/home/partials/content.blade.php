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
        <!-- Tren Biaya -->
        <div class="col-lg-8 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        <h5 class="mb-1 me-2">Tren Biaya</h5>
                        <p class="card-subtitle">Pemeliharaan vs BBM · 6 bulan terakhir</p>
                    </div>
                </div>
                <div class="card-body">
                    <script type="application/json" id="spendTrendData">@json($financials['trend'])</script>
                    <div id="spendTrendChart"></div>
                </div>
            </div>
        </div>
    @endif
    <!-- Biaya & pemakaian -->
    <div class="col-lg-4 mb-6">
        <div class="row">
            @if ($financials['visible'])
                @foreach (['maintenance' => ['Biaya Pemeliharaan', 'warning', 'bx-wrench'], 'fuel' => ['Biaya BBM', 'info', 'bx-gas-pump']] as $key => $meta)
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

<!-- Kondisi, komposisi & tren pemeliharaan -->
<div class="row">
    <!-- Kondisi Kendaraan -->
    <div class="col-md-6 col-lg-4 mb-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">Kondisi Kendaraan</h5>
                <p class="card-subtitle mb-0">{{ $conditionSummary['active'] }} aktif · {{ $conditionSummary['total'] }} total</p>
            </div>
            <div class="card-body d-flex justify-content-center">
                <script type="application/json" id="vehicleConditionData">@json($conditionSummary)</script>
                <div id="vehicleConditionChart"></div>
            </div>
        </div>
    </div>
    <!-- Komposisi Armada -->
    <div class="col-md-6 col-lg-4 mb-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">Komposisi Armada</h5>
                <p class="card-subtitle mb-0">Per tipe kendaraan</p>
            </div>
            <div class="card-body d-flex justify-content-center">
                <script type="application/json" id="fleetCompositionData">@json($fleetComposition)</script>
                <div id="fleetCompositionChart"></div>
            </div>
        </div>
    </div>
    <!-- Volume Pemeliharaan -->
    <div class="col-md-6 col-lg-4 mb-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">Volume Pemeliharaan</h5>
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
    <!-- Pengingat Servis -->
    @if ($serviceReminders['visible'])
        @php
            $serviceRows = collect($serviceReminders['items'])->map(fn (array $i): array => [
                'plate' => $plateLink($i),
                'detail' => $i['current'].' / '.$i['threshold'].' km · terakhir '.$i['last_service'],
                'status' => '<span class="badge bg-label-danger">Lewat '.e($i['over']).' km</span>',
            ])->all();
        @endphp
        <div class="col-md-6 col-lg-6 order-1 mb-6">
            <x-dashboard.list-table id="dashServiceReminder" title="Pengingat Servis"
                subtitle="{{ $serviceReminders['count'] }} Tenggat waktu" :columns="$reminderColumns" :rows="$serviceRows"
                placeholder="Tidak ada kendaraan jatuh tempo servis.">
                <x-slot:action>
                    <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </x-slot:action>
            </x-dashboard.list-table>
        </div>
    @endif
    <!--/ Pengingat Servis -->

    <!-- STNK & KIR segera kedaluwarsa -->
    @foreach ([
        ['data' => $stnkReminders, 'id' => 'dashStnkReminder', 'title' => 'STNK Segera Kedaluwarsa', 'empty' => 'Tidak ada STNK yang segera kedaluwarsa.'],
        ['data' => $kirReminders, 'id' => 'dashKirReminder', 'title' => 'KIR Segera Kedaluwarsa', 'empty' => 'Tidak ada KIR yang segera kedaluwarsa.'],
    ] as $documentTable)
        @if ($documentTable['data']['visible'])
            @php
                $documentRows = collect($documentTable['data']['items'])->map(function (array $i) use ($plateLink): array {
                    $daysLeft = (int) $i['days_left'];
                    $status = $daysLeft === 0 ? 'Hari ini' : 'H-'.$daysLeft;
                    $color = $daysLeft <= 3 ? 'danger' : 'warning';

                    return [
                        'plate' => $plateLink($i),
                        'detail' => 'Berlaku sampai '.$i['due_date'],
                        'status' => '<span class="badge bg-label-'.$color.'">'.e($status).'</span>',
                    ];
                })->all();
            @endphp
            <div class="col-md-6 col-lg-6 order-1 mb-6">
                <x-dashboard.list-table :id="$documentTable['id']" :title="$documentTable['title']"
                    subtitle="{{ $documentTable['data']['count'] }} Akan kedaluwarsa"
                    :columns="$reminderColumns" :rows="$documentRows" :placeholder="$documentTable['empty']">
                    <x-slot:action>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </x-slot:action>
                </x-dashboard.list-table>
            </div>
        @endif
    @endforeach
    <!--/ STNK & KIR segera kedaluwarsa -->

    <!-- Pengingat Asuransi -->
    @if ($insuranceReminders['visible'])
        @php
            $insuranceRows = collect($insuranceReminders['items'])->map(function (array $i): array {
                $overdue = ($i['state'] ?? null) === 'overdue';
                $status = $overdue
                    ? '<span class="badge bg-label-danger">Lewat '.e($i['days_overdue']).'h</span>'
                    : '<span class="badge bg-label-'.($i['threshold'] <= 7 ? 'danger' : 'warning').'">H-'.e($i['days_left']).'</span>';

                return [
                    'plate' => '<a href="'.e($i['link']).'" class="fw-medium text-body">'.e($i['plate']).'</a>',
                    'detail' => $i['insurer'].' · '.($overdue ? 'kedaluwarsa' : 'berakhir').' '.$i['end_date'],
                    'status' => $status,
                ];
            })->all();
        @endphp
        <div class="col-md-6 col-lg-6 order-1 mb-6">
            <x-dashboard.list-table id="dashInsuranceReminder" title="Pengingat Asuransi"
                subtitle="{{ $insuranceReminders['count'] }} Perlu perhatian" :columns="$reminderColumns"
                :rows="$insuranceRows" placeholder="Tidak ada asuransi yang perlu perhatian.">
                <x-slot:action>
                    <a href="{{ route('vehicle-insurances.index') }}"
                        class="btn btn-sm btn-outline-primary">Lihat</a>
                </x-slot:action>
            </x-dashboard.list-table>
        </div>
    @endif
    <!--/ Pengingat Asuransi -->

    <!-- Pemeliharaan Terbaru -->
    @php
        $recentRows = collect($recentMaintenance)->map(fn (array $i): array => [
            'plate' => $plateLink($i),
            'detail' => $i['meta'],
            'status' => '<span class="badge bg-label-'.$i['color'].'">'.e($i['badge']).'</span>',
        ])->all();
    @endphp
    <div class="col-md-6 col-lg-6 order-2 mb-6">
        <x-dashboard.list-table id="dashRecentMaintenance" title="Pemeliharaan Terbaru" :columns="$reminderColumns"
            :rows="$recentRows" placeholder="Belum ada data pemeliharaan terbaru.">
            <x-slot:action>
                <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </x-slot:action>
        </x-dashboard.list-table>
    </div>
    <!--/ Pemeliharaan Terbaru -->
</div>
