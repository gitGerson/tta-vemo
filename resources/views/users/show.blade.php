@extends('layouts.sneat')

@section('title')
    Detail User
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail User</h5>
                        <div>
                            @if(auth()->user()->hasPermission('management.users.edit'))
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission('management.users.view'))
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
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 border-bottom pb-4 mb-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-xl me-3">
                                    <span class="avatar-initial rounded bg-primary">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-label-primary mb-2">User #{{ $user->id }}</span>
                                    <h4 class="mb-1">{{ $user->name }}</h4>
                                    <div class="text-muted">{{ $user->username }}</div>
                                    <div class="text-muted">{{ $user->email }}</div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-content-start">
                                <span class="badge bg-label-secondary">{{ $user->roles->count() }} roles</span>
                                @if($user->email_verified_at)
                                    <span class="badge bg-label-success">Verified</span>
                                @else
                                    <span class="badge bg-label-warning">Unverified</span>
                                @endif
                                @if($user->deleted_at)
                                    <span class="badge bg-label-danger">Deleted</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Nama</small>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Username</small>
                                    <code>{{ $user->username }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Email</small>
                                    <div>{{ $user->email }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Perusahaan</small>
                                    <div>{{ $user->companies->pluck('name')->join(', ') ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Domain</small>
                                    <div>{{ $user->domain ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">GUID</small>
                                    <div class="text-break">{{ $user->guid ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Email Verified At</small>
                                    <div>{{ $user->email_verified_at?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Dibuat</small>
                                    <div>{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Diperbarui</small>
                                    <div>{{ $user->updated_at?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Dibuat Oleh</small>
                                    <div>{{ $user->creator?->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Diperbarui Oleh</small>
                                    <div>{{ $user->updater?->name ?? '-' }}</div>
                                </div>
                            </div>
                            @if($user->deleted_at)
                                <div class="col-md-6 col-xl-4">
                                    <div class="border rounded p-3 h-100">
                                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Dihapus</small>
                                        <div>{{ $user->deleted_at?->format('d M Y H:i') ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="border rounded p-3 h-100">
                                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Dihapus Oleh</small>
                                        <div>{{ $user->deleter?->name ?? '-' }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Roles ({{ $user->roles->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @if($user->roles->count() > 0)
                            <div class="row g-4">
                                @foreach($user->roles as $role)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded p-3 h-100 user-role-card">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <h6 class="text-primary mb-1">{{ $role->display_name }}</h6>
                                                    <code>{{ $role->name }}</code>
                                                </div>
                                                <span class="badge bg-label-secondary">{{ $role->guard_name ?? 'web' }}</span>
                                            </div>
                                            <p class="text-muted mb-0 mt-3">{{ $role->description ?? 'Tidak ada deskripsi' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-shield-x display-6 text-muted"></i>
                                <p class="text-muted mb-0 mt-2">User belum memiliki role</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .user-role-card {
            background-color: #f8f9fa;
        }
    </style>
@endpush
