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
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="bx bx-plus me-1"></i> Tambah User
                        </button>
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
                                <th>Companies</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasPermission('management.users.create'))
        <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username LDAP</label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                       id="username" name="username" value="{{ old('username') }}" required
                                       placeholder="Masukkan username untuk query LDAP">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Username akan digunakan untuk mengambil data dari LDAP</div>
                            </div>
                            <div class="mb-3">
                                <label for="company_ids" class="form-label">Companies Access</label>
                                <select class="form-select select2 @error('company_ids') is-invalid @enderror @error('company_ids.*') is-invalid @enderror" id="company_ids" name="company_ids[]" multiple required>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" {{ collect(old('company_ids', []))->contains($company->id) || collect(old('company_ids', []))->contains((string) $company->id) ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('company_ids.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Pilih semua company yang bisa diakses user.</div>
                            </div>
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role</label>
                                <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                    <option value="">- pilih role -</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name ?: $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection


@push('scripts')
    <script>
        $(function() {
            $('#company_ids').select2({
                dropdownParent: $('#createUserModal'),
                width: '100%',
                placeholder: '- pilih company akses -'
            });
        });

        const shouldOpenCreateModal = @json(
            request()->boolean('create') ||
            $errors->any() ||
            session()->has('error')
        );

        if (shouldOpenCreateModal) {
            const createModal = document.getElementById('createUserModal');
            if (createModal) {
                const modal = new bootstrap.Modal(createModal);
                modal.show();
            }
        }

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
                        data: 'companies',
                        name: 'companies',
                        orderable: false,
                        searchable: false
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
