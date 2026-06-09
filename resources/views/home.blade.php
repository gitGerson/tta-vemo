@extends('layouts.sneat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
    <!-- Content -->
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xxl-8 mb-6 order-0">
                <div class="card">
                    <div class="d-flex align-items-start row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">Hello {{ Auth::user()->name }}! 🎉</h5>
                                <p class="mb-6">
                                    You have done 72% more sales today.<br />Check your new badge in
                                    your profile.
                                </p>

                                <a href="javascript:;" class="btn btn-sm btn-outline-primary">View
                                    Badges</a>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-6">
                                <img src="{{ asset('sneat/assets/img/illustrations/man-with-laptop.png') }}" height="175"
                                    alt="View Badge User" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-lg-12 col-md-4 order-1">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{ asset('sneat/assets/img/icons/unicons/chart-success.png') }}" alt="chart success"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                            <a class="dropdown-item" href="javascript:void(0);">View
                                                More</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Profit</p>
                                <h4 class="card-title mb-3">$12,628</h4>
                                <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i>
                                    +72.80%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{ asset('sneat/assets/img/icons/unicons/wallet-info.png') }}" alt="wallet info"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                            <a class="dropdown-item" href="javascript:void(0);">View
                                                More</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Sales</p>
                                <h4 class="card-title mb-3">$4,679</h4>
                                <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i>
                                    +28.42%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Revenue -->
            <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6 total-revenue">
                <div class="card">
                    <div class="row row-bordered g-0">
                        <div class="col-lg-8">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2">Total Revenue</h5>
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="totalRevenue" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="icon-base bx bx-dots-vertical-rounded icon-lg text-body-secondary"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="totalRevenue">
                                        <a class="dropdown-item" href="javascript:void(0);">Select
                                            All</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Share</a>
                                    </div>
                                </div>
                            </div>
                            <div id="totalRevenueChart" class="px-3"></div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
                                <div class="text-center mb-6">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary">
                                            <script>
                                                document.write(new Date().getFullYear() - 1);
                                            </script>
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="javascript:void(0);">2021</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">2020</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">2019</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div id="growthChart"></div>
                                <div class="text-center fw-medium my-6">62% Company Growth</div>

                                <div class="d-flex gap-11 justify-content-between">
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-primary"><i
                                                    class="icon-base bx bx-dollar icon-lg text-primary"></i></span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>
                                                <script>
                                                    document.write(new Date().getFullYear() - 1);
                                                </script>
                                            </small>
                                            <h6 class="mb-0">$32.5k</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-info"><i
                                                    class="icon-base bx bx-wallet icon-lg text-info"></i></span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>
                                                <script>
                                                    document.write(new Date().getFullYear() - 2);
                                                </script>
                                            </small>
                                            <h6 class="mb-0">$41.2k</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Total Revenue -->
            <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2 profile-report">
                <div class="row">
                    <div class="col-6 mb-6 payments">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{ asset('sneat/assets/img/icons/unicons/paypal.png') }}" alt="paypal"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                            <a class="dropdown-item" href="javascript:void(0);">View
                                                More</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Payments</p>
                                <h4 class="card-title mb-3">$2,456</h4>
                                <small class="text-danger fw-medium"><i class="icon-base bx bx-down-arrow-alt"></i>
                                    -14.82%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-6 transactions">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{ asset('sneat/assets/img/icons/unicons/cc-primary.png') }}" alt="Credit Card"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                            <a class="dropdown-item" href="javascript:void(0);">View
                                                More</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Transactions</p>
                                <h4 class="card-title mb-3">$14,857</h4>
                                <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i>
                                    +28.14%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-6 profile-report">
                        <div class="card h-100">
                            <div class="card-body">
                                <div
                                    class="d-flex justify-content-between align-items-center flex-sm-row flex-column gap-10 flex-wrap">
                                    <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                        <div class="card-title mb-6">
                                            <h5 class="text-nowrap mb-1">Profile Report</h5>
                                            <span class="badge bg-label-warning">YEAR 2022</span>
                                        </div>
                                        <div class="mt-sm-auto">
                                            <span class="text-success text-nowrap fw-medium"><i
                                                    class="icon-base bx bx-up-arrow-alt"></i>
                                                68.2%</span>
                                            <h4 class="mb-0">$84,686k</h4>
                                        </div>
                                    </div>
                                    <div id="profileReportChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script src="{{ asset('sneat/assets/js/dashboards-analytics.js') }}"></script>
    @vite('resources/js/modules/dashboard.js')
@endpush
