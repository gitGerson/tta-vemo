@extends('layouts.sneat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Perbaikan Kendaraan</h5>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->hasPermission('maintenances.create'))
                            <a href="{{ route('maintenances.create') }}"
                                class="btn btn-primary btn-sm d-flex align-items-center">
                                <i class='bx bx-plus-circle me-1'></i> Add New
                            </a>
                        @endif
                    </div>
                </div>
                <form action="">
                    <div class="row ms-3 mb-3">
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
                                <label for="status">STATUS</label>
                                <select class="form-control select2" name="status" style="width: 100%;"
                                    aria-label="Default select example">
                                    <option value="" {{ $status == null ? 'selected' : '' }}>SEMUA STATUS</option>
                                    @can('view pemeliharaan')
                                        <option {{ $status == 'DRAFT' ? 'selected' : '' }} value="DRAFT">
                                            DRAFT</option>
                                    @endcan
                                    <option {{ $status == 'IN_PROGRESS' ? 'selected' : '' }} value="IN_PROGRESS">
                                        SEDANG DIKERJAKAN</option>
                                    <option {{ $status == 'COMPLETED' ? 'selected' : '' }} value="COMPLETED">
                                        SELESAI</option>
                                    <option {{ $status == 'CANCELLED' ? 'selected' : '' }} value="CANCELLED">
                                        DIBATALKAN</option>

                                </select>
                            </div>
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
                <div class="table-responsive text-nowrap ms-6">
                    <table id="dataTable" class="table table-bordered table-striped search-table">
                        <thead class="table-light">
                            <tr class="text-nowrap">
                                <th scope="col">No</th>
                                <th scope="col">NO POLISI</th>
                                <th scope="col">TANGGAL MASUK</th>
                                <th scope="col">TANGGAL KELUAR</th>
                                <th scope="col">KM</th>
                                <th scope="col">STATUS</th>
                                <th scope="col">KETERANGAN</th>
                                @if(auth()->user()->hasPermission('maintenances.view'))
                                    <th scope="col">ACTION</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            display: flex;
            align-items: center;
        }
    </style>
@endpush
@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 (if needed)
            $('.select2').select2();

            // Initialize DataTable
            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('maintenances.index') }}",
                    data: function (d) {
                        // Pass additional data to the server
                        d.start_date = $('input[name=start_date]').val();
                        d.end_date = $('input[name=end_date]').val();
                        d.status = $('select[name=status]').val();
                        d.search_value = $('#global-search').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'no_pol', name: 'no_pol' },
                    { data: 'date_in', name: 'date_in' },
                    { data: 'date_out', name: 'date_out' },
                    { data: 'mileage', name: 'mileage' },
                    { data: 'status', name: 'status' },
                    { data: 'description', name: 'description' },
                    @if(auth()->user()->hasPermission('maintenances.view'))
                                {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    @endif
                    ],
                searching: false,
                dom: 'Bfrtip',
                buttons: [],
                language: {
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
            });

            $('#global-search').on('keyup', function () {
                table.ajax.reload();
            });
        });
    </script>
@endpush