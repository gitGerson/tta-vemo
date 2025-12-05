@extends('layouts.sneat')

@section('title')
    Detail Role
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Role</h5>
                        <div>
                            @if(auth()->user()->hasPermission('roles.edit'))
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                            @endif
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="200">ID</th>
                                        <td>{{ $role->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama Role (Kode)</th>
                                        <td><code>{{ $role->name }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Display Name</th>
                                        <td>{{ $role->display_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <td>{{ $role->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Guard Name</th>
                                        <td><code>{{ $role->guard_name }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Jumlah User</th>
                                        <td>
                                            <span class="badge bg-info">{{ $role->users()->count() }} users</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat</th>
                                        <td>{{ $role->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Diperbarui</th>
                                        <td>{{ $role->updated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Permissions ({{ $role->permissions->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @if($role->permissions->count() > 0)
                            @php
                                $groupedPermissions = $role->permissions->groupBy('module');
                            @endphp
                            @foreach($groupedPermissions as $module => $permissions)
                                <div class="mb-3">
                                    <h6 class="text-primary">{{ ucfirst($module) }}</h6>
                                    @foreach($permissions as $permission)
                                        <div class="mb-1">
                                            <span class="badge bg-success me-1">{{ $permission->display_name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">Tidak ada permission assigned</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection