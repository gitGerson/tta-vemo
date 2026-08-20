@extends('layouts.sneat')

@section('title')
    Detail Role
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Role</h5>
                        <div>
                            @if(auth()->user()->hasPermission('management.roles.edit'))
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
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 border-bottom pb-4 mb-4">
                            <div>
                                <span class="badge bg-label-primary mb-2">Role #{{ $role->id }}</span>
                                <h4 class="mb-1">{{ $role->display_name }}</h4>
                                <code>{{ $role->name }}</code>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-content-start">
                                <span class="badge bg-info">{{ $role->users()->count() }} users</span>
                                <span class="badge bg-label-secondary">{{ $role->permissions->count() }} permissions</span>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Display Name</small>
                                    <div class="fw-semibold">{{ $role->display_name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Nama Role (Kode)</small>
                                    <code>{{ $role->name }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Guard Name</small>
                                    <code>{{ $role->guard_name }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Jumlah User</small>
                                    <span class="badge bg-info">{{ $role->users()->count() }} users</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Dibuat</small>
                                    <div>{{ $role->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Diperbarui</small>
                                    <div>{{ $role->updated_at?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Deskripsi</small>
                                    <div>{{ $role->description ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Permissions ({{ $role->permissions->count() }})</h6>
                        <span class="badge bg-label-primary">{{ $role->permissions->groupBy('module')->count() }} module</span>
                    </div>
                    <div class="card-body">
                        @if($role->permissions->count() > 0)
                            @php
                                $groupedPermissions = $role->permissions->groupBy('module');
                            @endphp
                            <div class="row g-4">
                                @foreach($groupedPermissions as $module => $permissions)
                                    @php
                                        $collapseId = 'permission-module-'.$loop->index;
                                    @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded overflow-hidden permission-module-card">
                                            <button class="btn w-100 text-start p-3 d-flex justify-content-between align-items-center"
                                                    type="button"
                                                    data-permission-toggle
                                                    data-target="{{ $collapseId }}"
                                                    aria-expanded="true"
                                                    aria-controls="{{ $collapseId }}">
                                                <span class="text-primary fw-semibold">{{ ucfirst($module) }}</span>
                                                <span class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-label-secondary">{{ $permissions->count() }}</span>
                                                    <i class="bx bx-chevron-up permission-toggle-icon"></i>
                                                </span>
                                            </button>
                                            <div id="{{ $collapseId }}" class="permission-module-body">
                                                <div class="px-3 pb-3 d-flex flex-wrap gap-2">
                                                    @foreach($permissions as $permission)
                                                        <span class="badge bg-success">{{ $permission->display_name }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-shield-x display-6 text-muted"></i>
                                <p class="text-muted mb-0 mt-2">Tidak ada permission assigned</p>
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
        .permission-module-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.25s ease;
        }

        .permission-module-card {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-permission-toggle]').forEach(function (button) {
                var target = document.getElementById(button.dataset.target);

                if (target) {
                    target.style.maxHeight = target.scrollHeight + 'px';
                }

                button.addEventListener('click', function () {
                    var icon = button.querySelector('.permission-toggle-icon');

                    if (!target) {
                        return;
                    }

                    var isExpanded = button.getAttribute('aria-expanded') === 'true';
                    button.setAttribute('aria-expanded', String(!isExpanded));
                    target.style.maxHeight = isExpanded ? '0px' : target.scrollHeight + 'px';

                    if (icon) {
                        icon.classList.toggle('bx-chevron-up', !isExpanded);
                        icon.classList.toggle('bx-chevron-down', isExpanded);
                    }
                });
            });

            window.addEventListener('resize', function () {
                document.querySelectorAll('[data-permission-toggle][aria-expanded="true"]').forEach(function (button) {
                    var target = document.getElementById(button.dataset.target);

                    if (target) {
                        target.style.maxHeight = target.scrollHeight + 'px';
                    }
                });
            });
        });
    </script>
@endpush
