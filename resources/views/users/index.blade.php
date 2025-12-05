@extends('layouts.sneat')

@section('title')
    Daftar User
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
                <h5 class="mb-0">Daftar User</h5>
                <div>
                    @if(auth()->user()->hasPermission('management.users.logs'))
                        <a href="{{ route('users.logs') }}" class="btn btn-info me-2">
                            <i class="bx bx-history me-1"></i> Activity Logs
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('management.users.delete'))
                        <a href="{{ route('users.trash') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-trash me-1"></i> Tempat Sampah
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('management.roles.view'))
                        <a href="{{ route('roles.index') }}" class="btn btn-warning me-2 d-none">
                            <i class="bx bx-shield me-1"></i> Kelola Role
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('management.users.create'))
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah User
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">

                    <table id="users-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
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
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
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
                        data: 'roles',
                        name: 'roles',
                        orderable: false,
                        searchable: false
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
