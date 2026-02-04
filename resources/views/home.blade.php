@extends('layouts.sneat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row">
            <!-- COUNT VEHINCLE -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    JUMLAH KENDARAAN</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $vehicle->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-car-front text-gray-300">
                                    <path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8" />
                                    <path d="M7 14h.01" />
                                    <path d="M17 14h.01" />
                                    <rect width="18" height="8" x="3" y="10" rx="2" />
                                    <path d="M5 18v2" />
                                    <path d="M19 18v2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COUNT DRIVER -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    JUMLAH SUPIR</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $active = $driver->where('status', 'ACTIVE')->count();
                                    @endphp
                                    {{ $active }} / {{ $driver->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-user text-gray-300">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <section class="mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <span class="badge bg-info-subtle text-info fw-semibold text-uppercase">ℹ️ Pengingat
                                Servis</span>
                            <h5 class="mt-2 mb-1">Kendaraan yang sudah lama tidak servis</h5>
                            <p class="text-muted mb-0 small">Pengingat untuk kendaraan yang sudah menempuh jarak cukup jauh
                                sejak servis terakhir.</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-info-subtle text-info fw-semibold">{{ $service->count() }} Unit</span>
                            <div class="text-muted small">Perlu dipertimbangkan untuk servis</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="service-table" class="table table-striped table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Unit Kendaraan</th>
                                    <th>Tahun</th>
                                    <th>Terakhir Diservis</th>
                                    <th>Jarak Tempuh</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($service as $row)
                                    @php
                                        $vehicleData = App\Models\Vehicle::getByNoPol($row?->no_pol);
                                        $maintenances = App\Models\Maintenances::getMtcByPlat($row->no_pol);
                                        $lastService = $maintenances?->date_out
                                            ? \Carbon\Carbon::parse($maintenances->date_out)->translatedFormat('d M Y')
                                            : 'Belum ada riwayat';
                                        $distance = $row->to_km - $row->from_km;
                                    @endphp
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>
                                            <div class="fw-semibold">{{ $vehicleData?->type ?? 'Kendaraan' }}</div>
                                            <div class="text-muted small">{{ $row->no_pol }}</div>
                                        </td>
                                        <td><span class="badge text-bg-light">{{ $vehicleData?->entry_year ?? '-' }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ $lastService }}</div>
                                            <div class="text-muted small">Riwayat terakhir</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-info-subtle text-info">{{ number_format($distance) }} km
                                                    sejak servis</span>
                                                <small class="text-muted">KM {{ number_format($row->from_km) }} →
                                                    {{ number_format($row->to_km) }}</small>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-info"
                                                href="/maintenances/create?vehicle={{ $row->no_pol }}">
                                                Buat Perbaikan
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        @if (auth()->user()?->hasPermission('maintenances.approve'))
            <section>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                            <div>
                                <span class="badge bg-warning-subtle text-warning fw-semibold text-uppercase">🔧 Sedang
                                    Dikerjakan</span>
                                <h5 class="mt-2 mb-1">Perbaikan kendaraan yang sedang dalam proses</h5>
                                <p class="text-muted mb-0 small">Pantau progres perbaikan dan selesaikan jika sudah selesai.</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning-subtle text-warning fw-semibold">{{ $mtc->count() }} Servis</span>
                                <div class="text-muted small">Dalam proses perbaikan</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="approval-table" class="table table-striped table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Unit Kendaraan</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Tanggal Keluar</th>
                                        <th>Oleh</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mtc as $list)
                                        @php
                                            $vehicleData = App\Models\Vehicle::getByNoPol($list?->no_pol);
                                            $dateIn = $list->date_in
                                                ? \Carbon\Carbon::parse($list->date_in)->translatedFormat('d M Y')
                                                : '-';
                                            $dateOut = $list->date_out
                                                ? \Carbon\Carbon::parse($list->date_out)->translatedFormat('d M Y')
                                                : '-';
                                        @endphp
                                        <tr>
                                            <th scope="row">{{ $loop->iteration }}</th>
                                            <td>
                                                <div class="fw-semibold">
                                                    @if ($vehicleData)
                                                        {{ $vehicleData?->type }} ({{ $list->no_pol }})
                                                    @else
                                                        Data kendaraan tidak ditemukan
                                                    @endif
                                                </div>
                                                <div class="text-muted small">Kode: {{ $list->code_mt }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $dateIn }}</div>
                                                <span class="badge bg-warning-subtle text-warning">Dalam Proses</span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $dateOut }}</div>
                                                <div class="text-muted small">Estimasi keluar</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $list->request->name }}</div>
                                                <div class="text-muted small">Pengaju</div>
                                            </td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="/maintenances/{{ $list->code_mt }}">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const baseOptions = {
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                order: [],
                autoWidth: false,
                paginate: true,
                searching: false,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_",
                    zeroRecords: "Tidak ada data",
                    emptyTable: "Tidak ada data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_",
                    // infoEmpty: "Tidak ada data",
                    infoFiltered: "(disaring dari _MAX_ total)",
                    paginate: { previous: "Sebelumnya", next: "Berikutnya" }
                },
                dom: '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 text-md-end"f>>t<"row align-items-center mt-3"<"col-md-12"i>><"row mt-2"<"col-md-12 d-flex justify-content-end"p>>'
            };

            $('#service-table').DataTable(Object.assign({}, baseOptions, {
                columnDefs: [
                    { orderable: false, searchable: false, targets: [0, 5] },
                    { orderable: false, targets: [4] }
                ]
            }));

            $('#approval-table').DataTable(Object.assign({}, baseOptions, {
                columnDefs: [
                    { orderable: false, searchable: false, targets: [0, 5] }
                ]
            }));
        });
    </script>
@endpush

@push('styles')
    <style>
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dt-paging {
            display: flex;
            justify-content: flex-end;
        }

        .dataTables_wrapper .dataTables_paginate nav,
        .dataTables_wrapper .dt-paging nav {
            display: flex;
        }

        .dataTables_wrapper .dataTables_paginate .pagination,
        .dataTables_wrapper .dt-paging .pagination {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 0.25rem;
            margin: 0;
            padding-left: 0;
            list-style: none;
            width: auto;
            max-width: 100%;
        }

        .dataTables_wrapper .dataTables_paginate .pagination .page-item,
        .dataTables_wrapper .dt-paging .pagination .page-item {
            display: inline-flex;
            flex: 0 0 auto;
        }

        .dataTables_wrapper .dataTables_paginate .pagination .page-link,
        .dataTables_wrapper .dt-paging .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
    </style>
@endpush