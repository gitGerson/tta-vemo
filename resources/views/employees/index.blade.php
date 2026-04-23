@extends('layouts.sneat')

@section('title')
    Master Employee
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
                <h5 class="mb-0">Master Employee</h5>
            </div>
            <div class="card-body">
                <form id="sync-jpayroll-form" action="{{ route('employees.sync-jpayroll') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="company" class="form-label">Company</label>
                        <select id="company" name="company" class="form-select" required>
                            <option value="">Pilih Company</option>
                            <option value="TTI">TTI</option>
                            <option value="CTR">CTR</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        @if(auth()->user()->hasPermission('master.employees.sync'))
                            <button type="submit" class="btn btn-primary" id="sync-submit">
                                <i class="bx bx-refresh me-1"></i> Sync JPayroll
                            </button>
                        @else
                            <span class="text-muted">Anda tidak memiliki akses untuk sync.</span>
                        @endif
                    </div>
                </form>

                <div id="sync-result" class="alert d-none mt-3" role="alert"></div>
            </div>
        </div>

        <x-datatable.card title="Daftar Employee" class="mt-4">
            <x-datatable.table
                id="employees-table"
                :columns="['No', 'NIK', 'Nama', 'Department', 'Company', 'Group', 'Status', 'Last Synced', 'Aksi']"
            />
        </x-datatable.card>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('sync-jpayroll-form');
            const submitButton = document.getElementById('sync-submit');
            const result = document.getElementById('sync-result');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!form || !submitButton) {
                return;
            }

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                const company = document.getElementById('company').value;
                result.classList.add('d-none');
                result.classList.remove('alert-success', 'alert-danger');

                submitButton.disabled = true;
                const originalButtonHtml = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ company })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Sync failed');
                    }

                    result.classList.add('alert-success');
                    result.textContent = data.message;

                    if (data.data && data.data.errors && data.data.errors.length > 0) {
                        const errorLines = data.data.errors.map(item => `${item.nik}: ${item.error}`).join('\n');
                        result.textContent = `${data.message}\nErrors:\n${errorLines}`;
                    }
                } catch (error) {
                    result.classList.add('alert-danger');
                    result.textContent = error.message || 'Sync failed';
                } finally {
                    result.classList.remove('d-none');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonHtml;
                }
            });
        });
    </script>

@endpush

<x-datatable.scripts table-id="employees-table" ajax="{{ route('employees.data') }}">
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'nik', name: 'nik' },
    { data: 'name', name: 'name' },
    { data: 'department', name: 'department' },
    { data: 'company', name: 'company' },
    { data: 'group', name: 'group' },
    { data: 'status', name: 'status', orderable: false, searchable: false },
    { data: 'last_synced_at', name: 'last_synced_at' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
