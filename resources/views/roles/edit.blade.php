@extends('layouts.sneat')

@section('title')
    Edit Role
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Role: {{ $role->display_name }}</h5>
                        <div>
                            <a href="{{ route('roles.show', $role) }}" class="btn btn-info me-2">
                                <i class="bx bx-show me-1"></i> Lihat
                            </a>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role) }}" method="POST">
                            @csrf
                            @method('PUT')
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
                                    <div>
                                        <label for="name" class="form-label">Nama Role (Kode)</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                        <div class="form-text">Contoh: user_manager, admin, viewer</div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div>
                                        <label for="display_name" class="form-label">Display Name</label>
                                        <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                               id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" required>
                                        <div class="form-text">Contoh: User Manager, Administrator, Viewer</div>
                                        @error('display_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div>
                                        <label for="guard_name" class="form-label">Guard Name</label>
                                        <input type="text" class="form-control" id="guard_name" value="{{ $role->guard_name }}" disabled>
                                        <div class="form-text">Guard saat ini hanya ditampilkan, tidak diubah dari form ini.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div>
                                        <label for="description" class="form-label">Deskripsi</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded mt-4 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                                    <h6 class="mb-0">Permissions</h6>
                                    <span class="badge bg-label-primary">{{ $permissions->count() }} module</span>
                                </div>
                                <div class="p-4">
                                    <div class="row g-4">
                                        @foreach($permissions as $module => $modulePermissions)
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
                                                            <span class="badge bg-label-secondary">{{ $modulePermissions->count() }}</span>
                                                            <i class="bx bx-chevron-up permission-toggle-icon"></i>
                                                        </span>
                                                    </button>
                                                    <div id="{{ $collapseId }}" class="permission-module-body">
                                                        <div class="px-3 pb-3">
                                                            @foreach($modulePermissions as $permission)
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="permissions[]" value="{{ $permission->id }}"
                                                                           id="permission_{{ $permission->id }}"
                                                                           {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                        {{ $permission->display_name }}
                                                                        <small class="text-muted d-block">{{ $permission->description }}</small>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </div>
                        </form>
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
