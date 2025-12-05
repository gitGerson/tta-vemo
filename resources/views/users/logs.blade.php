@extends('layouts.sneat')

@section('title')
    User Activity Logs
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User Activity Logs</h5>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar User
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">

                    <table id="logs-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Target User</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(function() {
            $('#logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.logs.data') }}',
                columns: [{
                        data: 'formatted_date',
                        name: 'created_at'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'action_badge',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'target_user_name',
                        name: 'target_user_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address'
                    }
                ],
                order: [[0, 'desc']],
                dom: 'lBfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
            });
        });
    </script>
@endpush