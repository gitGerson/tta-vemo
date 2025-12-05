@extends('layouts.sneat')

@section('title')
    Tempat Sampah User
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
                <h5 class="mb-0">Tempat Sampah User</h5>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar User
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">

                    <table id="trash-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Dihapus</th>
                                <th>Dihapus Oleh</th>
                                <th>Aksi</th>
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
            $('#trash-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.trash.data') }}',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at',
                        render: function(data, type, row) {
                            return moment(data).format('DD MMM YYYY HH:mm');
                        }
                    },
                    {
                        data: 'deleted_by_name',
                        name: 'deleted_by_name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'csv',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'excel',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'print',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    'colvis'
                ],
            });
        });
    </script>
@endpush