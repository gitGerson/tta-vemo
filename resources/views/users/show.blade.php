@extends('layouts.sneat')

@section('title')
    Detail User
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail User</h5>
                        <div>
                            @if(auth()->user()->hasPermission('users.edit'))
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                <a href="{{ route('users.roles', $user) }}" class="btn btn-success me-2">
                                    <i class="bx bx-shield me-1"></i> Kelola Role
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission('users.logs'))
                                <a href="{{ route('users.user-logs', $user) }}" class="btn btn-info me-2">
                                    <i class="bx bx-history me-1"></i> Activity Log
                                </a>
                            @endif
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
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
                                        <td>{{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama</th>
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
                                    <tr>
                                        <th>Domain</th>
                                        <td>{{ $user->domain ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td>
                                            @if($user->roles->count() > 0)
                                                @foreach($user->roles as $role)
                                                    <span class="badge bg-primary me-1">{{ $role->display_name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Tidak ada role</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>GUID</th>
                                        <td>{{ $user->guid ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email Verified At</th>
                                        <td>{{ $user->email_verified_at ? $user->email_verified_at->format('d M Y H:i') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat</th>
                                        <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Diperbarui</th>
                                        <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat Oleh</th>
                                        <td>{{ $user->creator ? $user->creator->name : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Diperbarui Oleh</th>
                                        <td>{{ $user->updater ? $user->updater->name : '-' }}</td>
                                    </tr>
                                    @if($user->deleted_at)
                                    <tr>
                                        <th>Dihapus</th>
                                        <td>{{ $user->deleted_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dihapus Oleh</th>
                                        <td>{{ $user->deleter ? $user->deleter->name : '-' }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection