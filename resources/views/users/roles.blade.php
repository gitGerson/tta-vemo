@extends('layouts.sneat')

@section('title')
    Kelola Role User
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

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kelola Role: {{ $user->name }}</h5>
                        <div>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-info me-2">
                                <i class="bx bx-show me-1"></i> Lihat User
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6>Informasi User</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">Nama</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td>{{ $user->username }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <form action="{{ route('users.roles.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <h6>Pilih Role untuk User</h6>
                                <div class="row">
                                    @foreach($availableRoles as $role)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="roles[]" value="{{ $role->id }}" 
                                                       id="role_{{ $role->id }}"
                                                       {{ $user->hasRole($role) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_{{ $role->id }}">
                                                    <strong>{{ $role->display_name }}</strong>
                                                    <small class="text-muted d-block">{{ $role->description ?? 'No description' }}</small>
                                                    <small class="text-info d-block">{{ $role->permissions->count() }} permissions</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Role
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Role Saat Ini ({{ $user->roles->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @if($user->roles->count() > 0)
                            @foreach($user->roles as $role)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-primary">{{ $role->display_name }}</span>
                                            <small class="text-muted d-block">{{ $role->permissions->count() }} permissions</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="removeRole({{ $role->id }}, '{{ $role->display_name }}')">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">User belum memiliki role</p>
                        @endif
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Tambah Role Cepat</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select class="form-select" id="quickAddRole">
                                <option value="">Pilih role untuk ditambahkan...</option>
                                @foreach($availableRoles as $role)
                                    @if(!$user->hasRole($role))
                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="quickAddRole()">
                            <i class="bx bx-plus me-1"></i> Tambah Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk konfirmasi -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmButton">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function removeRole(roleId, roleName) {
    $('#confirmMessage').text(`Yakin ingin menghapus role "${roleName}" dari user ini?`);
    $('#confirmButton').off('click').on('click', function() {
        $.ajax({
            url: '{{ route("users.roles.remove", $user) }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                role_id: roleId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat menghapus role');
            }
        });
        $('#confirmModal').modal('hide');
    });
    $('#confirmModal').modal('show');
}

function quickAddRole() {
    const roleId = $('#quickAddRole').val();
    if (!roleId) {
        alert('Pilih role terlebih dahulu');
        return;
    }

    $.ajax({
        url: '{{ route("users.roles.assign", $user) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            role_id: roleId
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat menambah role');
        }
    });
}
</script>
@endpush