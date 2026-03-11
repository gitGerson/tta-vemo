@extends('layouts.sneat')

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="card mb-4">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Catatan Perjalanan</h5>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->hasPermission('record.create'))
                            <a href="{{ route('record.create') }}" class="btn btn-primary btn-sm d-flex align-items-center">
                                <i class='bx bx-plus-circle me-1'></i> Add New
                            </a>
                        @endif
                        @if(auth()->user()->hasPermission('record.view'))
                            <a href="{{ route('record.export', [
        'start_date' => request('start_date'),
        'end_date' => request('end_date'),
        'company' => request('company')
    ]) }}" class="btn btn-secondary btn-sm d-flex align-items-center">
                                <i class='bx bx-export me-1'></i> Export Excel
                            </a>
                        @endif
                    </div>
                </div>


                <form action="">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>TGL MULAI</label>
                                <input type="date" value="{{ $start_date }}" name="start_date" class="form-control mr-2"
                                    id="start_date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>TGL SELESAI</label>
                                <input type="date" value="{{ $end_date }}" name="end_date" class="form-control mr-2"
                                    id="end_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="company">PERUSAHAAN</label>
                                <select class="form-control select2" name="company" aria-label="Default select example">
                                    <option selected value="">SEMUANYA</option>
                                    @foreach ($company as $row)
                                        <option {{ request()->query('company') == $row->id ? 'selected' : '' }}
                                            value="{{ $row->id }}">{{ $row->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                if (request()->query('company')) {
                                    $company = request()->query('company');
                                } else {
                                    $company = 'TTI';
                                }
                            @endphp
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="" class="text-white">placeholder</label>
                                <br>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                        <div class="col">
                            <label for="" class="text-white">placeholder</label>
                            <div class="form-group justify-content-end ml-auto">
                                <input type="text" id="global-search" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                </form>
                <br>

                <form method="get" class="d-flex align-items-center mb-2" style="gap: 10px;">
                    <!-- <label for="per_page">Show</label>
                    <select name="per_page" id="per_page" class="form-control" style="width: auto; min-width: 30px;"
                        onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100, 200] as $limit)
                            <option value="{{ $limit }}" {{ (session('per_page', 100) == $limit) ? 'selected' : '' }}>
                                {{ $limit }}
                            </option>
                        @endforeach
                    </select>
                    <span>entries</span> -->

                    @if(request('start_date'))
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    @endif
                    @if(request('end_date'))
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    @endif
                    @if(request('company'))
                        <input type="hidden" name="company" value="{{ request('company') }}">
                    @endif
                </form>

                <div class="table-responsive text-nowrap">
                    <table id="dataTable" class="table table-sm table-bordered table-striped search-table compact-record-table">
                        <thead class="table-light">
                            <tr class="text-nowrap">
                                <th scope="col">No</th>
                                <th scope="col">TANGGAL</th>
                                <th scope="col">SUPIR 1</th>
                                <th scope="col">SUPIR 2</th>
                                <th scope="col">NO POLISI</th>
                                <th scope="col">TUJUAN</th>
                                <th scope="col">KM AWAL</th>
                                <th scope="col">KM AKHIR</th>
                                <th scope="col">TOTAL</th>
                                <th scope="col">BBM</th>
                                <th scope="col">IDR</th>
                                <th scope="col">SKALA</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($record as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}. </td>
                                    <td>{{ Carbon\Carbon::parse($row->date)->translatedFormat('d F Y') }}</td>
                                    <td>{{ isset($row->driver_1) ? strtoupper($row->driver_1) : 'NONE' }}</td>
                                    <td>{{ isset($row->driver_2) ? strtoupper($row->driver_2) : 'NONE' }}</td>
                                    <td>{{ $row->no_pol }}</td>
                                    <td>{{ $row->destination }}</td>
                                    <td>{{ $row->start_km }}</td>
                                    <td>{{ $row->finish_km }}</td>
                                    <td>{{ $row->totals }}</td>
                                    <td>{{ $row->bbm }}</td>
                                    <td>{{ $row->idr }}</td>
                                    <td>{{ $row->scale }}</td>
                                    <td>
                                        @if(auth()->user()->hasPermission('record.view'))
                                            <a href="{{ route('record.show', $row->id) }}" class="btn btn-info btn-sm">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('record.edit'))
                                            <a href="{{ route('record.edit', $row->id) }}" class="btn btn-warning btn-sm">
                                                <i class='bx bx-edit-alt'></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('record.delete'))
                                            <form action="{{ route('record.destroy', $row->id) }}" method="POST"
                                                style="display: inline;"
                                                onsubmit="return confirm('Hapus catatan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {!! $record->withQueryString()->links('pagination::bootstrap-5') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('addon-style')
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            min-width: 1200px;
        }

        .compact-record-table {
            font-size: 0.8125rem;
        }

        .compact-record-table > :not(caption) > * > * {
            padding: 0.4rem 0.5rem;
        }
    </style>
@endpush
@push('addon-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('global-search');
            const tables = document.querySelectorAll('.search-table');

            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    const searchTerm = searchInput.value.toLowerCase();

                    tables.forEach(function (table) {
                        const rows = table.querySelectorAll('tbody tr');

                        rows.forEach(function (row) {
                            const cells = row.querySelectorAll('td');
                            let rowText = '';

                            cells.forEach(function (cell) {
                                rowText += cell.textContent.toLowerCase() + ' ';
                            });

                            row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                        });
                    });
                });
            }
        });

    </script>
@endpush
