@extends('layouts.sneat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
    <!-- Content -->
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- Welcome / Quick Actions -->
            <div class="col-lg-8 mb-6 order-0">
                <div class="card h-100"
                    style="background-image: url('{{ asset('images/sign_bg.png') }}'); background-size: cover; background-position: center;">
                    <div class="d-flex align-items-end row h-100">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-2">Hello {{ Auth::user()->name }}! 👋</h5>
                                <p class="mb-4">
                                    Here's the current state of your fleet.<br />Jump straight into the most
                                    common tasks below.
                                </p>
                                @if (count($quickActions))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($quickActions as $action)
                                            <a href="{{ $action['url'] }}" class="btn btn-sm btn-primary">
                                                <i class="icon-base bx {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-6">
                                <img src="{{ asset('sneat/assets/img/illustrations/man-with-laptop.png') }}" height="175"
                                    alt="Fleet dashboard" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- KPI cards -->
            <div class="col-lg-4 col-md-12 order-1">
                <div class="row">
                    @foreach ($kpis as $kpi)
                        <div class="col-lg-6 col-md-3 col-6 mb-6">
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
            </div>
        </div>
        <div class="row">
            <!-- Vehicle Condition -->
            <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Vehicle Condition</h5>
                            <p class="card-subtitle">{{ $conditionSummary['active'] }} Active</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <h3 class="mb-1">{{ $conditionSummary['total'] }}</h3>
                                <small>Total Vehicles</small>
                            </div>
                            <div id="vehicleConditionChart"></div>
                        </div>
                        <script type="application/json" id="vehicleConditionData">@json($conditionSummary)</script>
                        <ul class="p-0 m-0">
                            @foreach ($conditionSummary['items'] as $item)
                                <li class="d-flex align-items-center {{ $loop->last ? '' : 'mb-5' }}">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-{{ $item['color'] }}"><i
                                                class="icon-base bx {{ $item['icon'] }}"></i></span>
                                    </div>
                                    <a href="{{ $item['link'] }}"
                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2 text-body">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ $item['label'] }}</h6>
                                            <small>{{ $item['sublabel'] }}</small>
                                        </div>
                                        <div class="user-progress">
                                            <h6 class="mb-0">{{ $item['count'] }}</h6>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <!--/ Vehicle Condition -->

            <!-- Service Reminder -->
            @if ($serviceReminders['visible'])
            <div class="col-md-6 col-lg-4 order-1 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Service Reminder</h5>
                            <p class="card-subtitle">{{ $serviceReminders['count'] }} Due</p>
                        </div>
                        <a href="{{ route('maintenances.create') }}" class="btn btn-sm btn-outline-primary">Log
                            Service</a>
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse ($serviceReminders['items'] as $item)
                                <li class="d-flex align-items-center {{ $loop->last ? '' : 'mb-6' }}">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-danger"><i
                                                class="icon-base bx bx-wrench"></i></span>
                                    </div>
                                    <a href="{{ $item['link'] }}"
                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2 text-body">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ $item['plate'] }}</h6>
                                            <small>{{ $item['current'] }} / {{ $item['threshold'] }} km · last {{ $item['last_service'] }}</small>
                                        </div>
                                        <div class="user-progress">
                                            <span class="badge bg-label-danger">{{ $item['over'] }} km over</span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-body-secondary py-4">
                                    <i class="icon-base bx bx-check-circle text-success mb-2 d-block"
                                        style="font-size: 2rem;"></i>
                                    No vehicles due for service.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            <!--/ Service Reminder -->

            <!-- Foto Kendaraan -->
            @if ($photoReminders['visible'])
            <div class="col-md-6 col-lg-4 order-1 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Foto Kendaraan</h5>
                            <p class="card-subtitle">{{ $photoReminders['count'] }} Belum lengkap</p>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse ($photoReminders['items'] as $item)
                                <li class="d-flex align-items-center {{ $loop->last ? '' : 'mb-6' }}">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-warning"><i
                                                class="icon-base bx bx-camera"></i></span>
                                    </div>
                                    <a href="{{ $item['link'] }}"
                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2 text-body">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ $item['plate'] }}</h6>
                                            <small>Foto {{ $item['period_label'] }} belum lengkap</small>
                                        </div>
                                        <div class="user-progress">
                                            <span class="badge bg-label-warning">{{ $item['completed'] }}/{{ $item['total'] }}</span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-body-secondary py-4">
                                    <i class="icon-base bx bx-check-circle text-success mb-2 d-block"
                                        style="font-size: 2rem;"></i>
                                    Semua kendaraan sudah lengkap foto bulan ini.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            <!--/ Foto Kendaraan -->

            <!-- Insurance Reminder -->
            @if ($insuranceReminders['visible'])
            <div class="col-md-6 col-lg-4 order-1 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Insurance Reminder</h5>
                            <p class="card-subtitle">{{ $insuranceReminders['count'] }} Perlu perhatian</p>
                        </div>
                        <a href="{{ route('vehicle-insurances.index') }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse ($insuranceReminders['items'] as $item)
                                @php $overdue = ($item['state'] ?? null) === 'overdue'; @endphp
                                <li class="d-flex align-items-center {{ $loop->last ? '' : 'mb-6' }}">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-{{ $overdue || $item['threshold'] <= 7 ? 'danger' : 'warning' }}"><i
                                                class="icon-base bx {{ $overdue ? 'bx-shield-x' : 'bx-shield-quarter' }}"></i></span>
                                    </div>
                                    <a href="{{ $item['link'] }}"
                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2 text-body">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ $item['plate'] }}</h6>
                                            <small>{{ $item['insurer'] }} · {{ $overdue ? 'kedaluwarsa' : 'berakhir' }} {{ $item['end_date'] }}</small>
                                        </div>
                                        <div class="user-progress">
                                            @if ($overdue)
                                                <span class="badge bg-label-danger">Overdue {{ $item['days_overdue'] }}h</span>
                                            @else
                                                <span class="badge bg-label-{{ $item['threshold'] <= 7 ? 'danger' : 'warning' }}">H-{{ $item['days_left'] }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-body-secondary py-4">
                                    <i class="icon-base bx bx-check-circle text-success mb-2 d-block"
                                        style="font-size: 2rem;"></i>
                                    Tidak ada asuransi yang perlu perhatian.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            <!--/ Insurance Reminder -->

            <!-- Recent Maintenance -->
            <div class="col-md-6 col-lg-4 order-2 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Recent Maintenance</h5>
                        <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse ($recentMaintenance as $item)
                                <li class="d-flex align-items-center {{ $loop->last ? '' : 'mb-6' }}">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-{{ $item['color'] }}"><i
                                                class="icon-base bx {{ $item['icon'] }}"></i></span>
                                    </div>
                                    <a href="{{ $item['link'] }}"
                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2 text-body">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ $item['plate'] }}</h6>
                                            <small>{{ $item['meta'] }}</small>
                                        </div>
                                        <div class="user-progress">
                                            <span class="badge bg-label-{{ $item['color'] }}">{{ $item['badge'] }}</span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="text-center text-body-secondary py-4">No recent maintenance records.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <!--/ Recent Maintenance -->
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('sneat/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    @vite('resources/js/modules/dashboard.js')
@endpush
