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

        <x-datatable.card title="Daftar User">
            <x-slot:actions>
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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bx bx-plus me-1"></i> Tambah User
                    </button>
                @endif
            </x-slot:actions>

            <x-datatable.table
                id="users-table"
                :columns="['No', 'Nama', 'Username', 'Email', 'Perusahaan', 'Role', 'Aksi']"
            />
        </x-datatable.card>
    </div>

    @if(auth()->user()->hasPermission('management.users.create'))
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('users.store') }}" method="POST" id="addUserForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel">Tambah User (LDAP)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert d-none" id="ldapVerifyMessage" role="alert"></div>

                            <div class="mb-3">
                                <label for="addUserUsername" class="form-label">Username LDAP</label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                       id="addUserUsername" name="username" value="{{ old('username') }}" required
                                       placeholder="Masukkan username untuk query LDAP">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Username akan digunakan untuk mengambil data dari LDAP</div>
                            </div>

                            <div class="d-none mb-3" id="ldapUserPreview">
                                <div class="ldap-user-preview border rounded p-3">
                                    <div class="fw-semibold" id="ldapUserName"></div>
                                    <div class="text-muted small" id="ldapUserEmail"></div>
                                </div>
                            </div>

                            <div class="mb-3" id="roleSelectionWrapper">
                                <label for="roleId" class="form-label">Role</label>
                                <select name="role_id" id="roleId" class="form-select @error('role_id') is-invalid @enderror" required>
                                    <option value="">Pilih role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <x-form.select
                                name="company_ids"
                                id="companyIds"
                                label="Perusahaan"
                                mode="select2"
                                multiple
                                placeholder="Pilih perusahaan..."
                                :inputClass="$errors->has('company_ids.*') ? 'is-invalid' : null"
                            >
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" @selected(in_array($company->id, old('company_ids', [])))>
                                        {{ $company->name }} ({{ $company->code }})
                                    </option>
                                @endforeach
                            </x-form.select>
                            <x-form.error name="company_ids.*" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="saveUserButton" disabled>
                                <i class="bx bx-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        #addUserModal .ldap-user-preview {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color) !important;
            color: var(--bs-heading-color);
        }

        #addUserModal .ldap-user-preview .text-muted {
            color: var(--bs-secondary-color) !important;
        }

        [data-bs-theme="dark"] #addUserModal .modal-content {
            background-color: var(--bs-paper-bg);
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] #addUserModal .modal-header,
        [data-bs-theme="dark"] #addUserModal .modal-footer {
            border-color: var(--bs-border-color);
        }

        [data-bs-theme="dark"] #addUserModal .ldap-user-preview {
            background-color: #263544;
            border-color: #3a4b5c !important;
        }

        [data-bs-theme="dark"] #addUserModal #ldapUserName {
            color: #f5f7fa;
        }

        [data-bs-theme="dark"] #addUserModal .ldap-user-preview .text-muted {
            color: #aab6c2 !important;
        }

        [data-bs-theme="dark"] #addUserModal .alert-success {
            --bs-alert-color: #8ff06f;
            --bs-alert-bg: #12330d;
            --bs-alert-border-color: #28631f;
        }
    </style>
@endpush


<x-datatable.scripts table-id="users-table" ajax="{{ route('users.data') }}" clickable-rows>
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'username', name: 'username' },
    { data: 'email', name: 'email' },
    { data: 'companies', name: 'companies', orderable: false, searchable: false },
    { data: 'roles', name: 'roles', orderable: false, searchable: false },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>

@push('scripts')
    <script>
        $(function() {
            const addUserForm = document.getElementById('addUserForm');
            const addUserModal = document.getElementById('addUserModal');
            const usernameInput = document.getElementById('addUserUsername');
            const saveButton = document.getElementById('saveUserButton');
            const roleSelect = document.getElementById('roleId');
            const messageBox = document.getElementById('ldapVerifyMessage');
            const userPreview = document.getElementById('ldapUserPreview');
            const userName = document.getElementById('ldapUserName');
            const userEmail = document.getElementById('ldapUserEmail');
            let verifiedUsername = null;
            let verifyTimer = null;
            let verifyRequest = null;

            function showMessage(type, message) {
                messageBox.className = `alert alert-${type}`;
                messageBox.textContent = message;
            }

            function clearMessage() {
                messageBox.className = 'alert d-none';
                messageBox.textContent = '';
            }

            function syncSaveButton() {
                const username = usernameInput.value.trim();
                saveButton.disabled = !(verifiedUsername === username && roleSelect.value);
            }

            function resetVerifiedState(options = {}) {
                verifiedUsername = null;
                userPreview.classList.add('d-none');
                userName.textContent = '';
                userEmail.textContent = '';
                syncSaveButton();

                if (!options.keepMessage) {
                    clearMessage();
                }
            }

            function markVerified(user) {
                verifiedUsername = user.username;
                userPreview.classList.remove('d-none');
                userName.textContent = user.name;
                userEmail.textContent = `${user.username} | ${user.email}`;
                syncSaveButton();
            }

            function verifyLdapUsername(username) {
                if (verifyRequest) {
                    verifyRequest.abort();
                }

                verifyRequest = new AbortController();
                showMessage('info', 'Memverifikasi user ke LDAP...');

                fetch('{{ route('users.verify-ldap') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ username }),
                    signal: verifyRequest.signal,
                })
                    .then(async response => {
                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Verifikasi LDAP gagal.');
                        }

                        if (usernameInput.value.trim() !== username) {
                            return;
                        }

                        showMessage('success', data.message);
                        markVerified(data.user);
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        showMessage('danger', error.message);
                        resetVerifiedState({ keepMessage: true });
                    });
            }

            function scheduleLdapVerification() {
                const username = usernameInput.value.trim();

                window.clearTimeout(verifyTimer);
                if (verifyRequest) {
                    verifyRequest.abort();
                }

                resetVerifiedState();

                if (!username) {
                    return;
                }

                if (username.length < 3) {
                    showMessage('warning', 'Ketik minimal 3 karakter username LDAP.');
                    return;
                }

                showMessage('info', 'Menunggu input selesai...');
                verifyTimer = window.setTimeout(() => verifyLdapUsername(username), 700);
            }

            if (usernameInput && roleSelect) {
                usernameInput.addEventListener('input', scheduleLdapVerification);

                roleSelect.addEventListener('change', function() {
                    syncSaveButton();
                });

                if (addUserModal) {
                    addUserModal.addEventListener('hidden.bs.modal', function() {
                        window.clearTimeout(verifyTimer);
                        if (verifyRequest) {
                            verifyRequest.abort();
                        }

                        addUserForm.reset();
                        $('#companyIds').val(null).trigger('change');
                        resetVerifiedState();
                    });

                    addUserModal.addEventListener('shown.bs.modal', function() {
                        if (usernameInput.value.trim()) {
                            scheduleLdapVerification();
                        }
                    });
                }

                if (addUserForm) {
                    addUserForm.addEventListener('submit', function(event) {
                        syncSaveButton();

                        if (saveButton.disabled) {
                            event.preventDefault();
                            showMessage('warning', 'Tunggu verifikasi LDAP berhasil dan pilih role sebelum menyimpan.');
                        }
                    });
                }

                if (@json($errors->has('role_id'))) {
                    showMessage('warning', 'Silakan cek role sebelum menyimpan user.');
                }

                if (usernameInput.value.trim()) {
                    scheduleLdapVerification();
                }
            }
        });
    </script>
    @if(session('error') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalEl = document.getElementById('addUserModal');
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
    @endif
@endpush
