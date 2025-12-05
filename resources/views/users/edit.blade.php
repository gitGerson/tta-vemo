@extends('layouts.sneat')

@section('title')
    Edit User - {{ $user->name }}
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- User Basic Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">User Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="avatar-wrapper">
                                <div class="avatar avatar-xl me-3">
                                    <span class="avatar-initial rounded bg-primary">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ $user->name }}</h5>
                                <p class="mb-1 text-muted">{{ $user->username }}</p>
                                <p class="mb-0 text-muted">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="mt-3 d-none">
                            <form action="{{ route('users.update', $user) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="pin" class="form-label">PIN</label>
                                    <input type="password" class="form-control @error('pin') is-invalid @enderror"
                                           id="pin" name="pin" maxlength="6" placeholder="Enter new PIN">
                                    @error('pin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty to keep current PIN</small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bx bx-save me-1"></i> Update PIN
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Role Templates -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Apply Role Template</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('users.apply-template', $user) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <select name="role_template" class="form-select" required>
                                    <option value="">Select Template</option>
                                    @foreach($roleTemplates as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-key me-1"></i> Apply Template
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Permission Management -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Permission Management</h5>
                        <div>
                            <button type="button" class="btn btn-outline-success btn-sm me-2" id="selectAll">
                                <i class="bx bx-check-square me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm me-2" id="deselectAll">
                                <i class="bx bx-square me-1"></i> Deselect All
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('users.update-permissions', $user) }}" method="POST" id="permissionForm">
                            @csrf
                            @method('PUT')

                            <!-- Permission Groups -->
                            @php
                                $parentPermissions = $permissions->whereNull('parent')->sortBy('sort_order');
                            @endphp

                            @foreach($parentPermissions as $parent)
                                <div class="permission-group mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input parent-checkbox" type="checkbox"
                                                   id="parent_{{ $parent->id }}" data-parent="{{ $parent->name }}">
                                            <label class="form-check-label fw-bold text-primary" for="parent_{{ $parent->id }}">
                                                <i class="bx bx-folder me-2"></i>{{ $parent->display_name }}
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Child Permissions -->
                                    <div class="row ms-4">
                                        @php
                                            $childPermissions = $permissions->where('parent', $parent->name)->sortBy('sort_order');
                                        @endphp

                                        @foreach($childPermissions as $child)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input child-checkbox"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $child->id }}"
                                                           id="perm_{{ $child->id }}"
                                                           data-parent="{{ $parent->name }}"
                                                           {{ $user->hasPermission($child->name) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $child->id }}">
                                                        {{ $child->display_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <hr>
                            @endforeach

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Permissions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select/Deselect All functionality
    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.child-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateParentCheckboxes();
    });

    document.getElementById('deselectAll').addEventListener('click', function() {
        document.querySelectorAll('.child-checkbox, .parent-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Parent checkbox functionality
    document.querySelectorAll('.parent-checkbox').forEach(parentCheckbox => {
        parentCheckbox.addEventListener('change', function() {
            const parentName = this.dataset.parent;
            const childCheckboxes = document.querySelectorAll(`.child-checkbox[data-parent="${parentName}"]`);

            childCheckboxes.forEach(child => {
                child.checked = this.checked;
            });
        });
    });

    // Child checkbox functionality
    document.querySelectorAll('.child-checkbox').forEach(childCheckbox => {
        childCheckbox.addEventListener('change', function() {
            updateParentCheckboxes();
        });
    });

    function updateParentCheckboxes() {
        document.querySelectorAll('.parent-checkbox').forEach(parentCheckbox => {
            const parentName = parentCheckbox.dataset.parent;
            const childCheckboxes = document.querySelectorAll(`.child-checkbox[data-parent="${parentName}"]`);
            const checkedChildren = document.querySelectorAll(`.child-checkbox[data-parent="${parentName}"]:checked`);

            if (checkedChildren.length === childCheckboxes.length && childCheckboxes.length > 0) {
                parentCheckbox.checked = true;
                parentCheckbox.indeterminate = false;
            } else if (checkedChildren.length > 0) {
                parentCheckbox.checked = false;
                parentCheckbox.indeterminate = true;
            } else {
                parentCheckbox.checked = false;
                parentCheckbox.indeterminate = false;
            }
        });
    }

    // Initialize parent checkboxes
    updateParentCheckboxes();

    // Role template dropdown change - auto update checkboxes
    const roleTemplateSelect = document.querySelector('select[name="role_template"]');
    if (roleTemplateSelect) {
        roleTemplateSelect.addEventListener('change', function() {
            const roleId = this.value;

            if (!roleId) {
                return;
            }

            // Fetch permissions for the selected template
            fetch(`/roles/${roleId}/permissions`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // First, uncheck all checkboxes
                        document.querySelectorAll('.child-checkbox').forEach(checkbox => {
                            checkbox.checked = false;
                        });

                        // Then, check the permissions from the template
                        data.permission_ids.forEach(permissionId => {
                            const checkbox = document.querySelector(`input[name="permissions[]"][value="${permissionId}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });

                        // Update parent checkboxes
                        updateParentCheckboxes();
                    }
                })
                .catch(error => {
                    console.error('Error fetching template permissions:', error);
                });
        });
    }
});
</script>
@endpush
