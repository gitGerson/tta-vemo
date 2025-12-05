@extends('layouts.sneat')

@section('title')
    Daftar Tipe Maintenance
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
                <h5 class="mb-0">Daftar Tipe Maintenance</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.type_maintenances.create'))
                        <button type="button" class="btn btn-primary btn-create-type" data-bs-toggle="modal"
                            data-bs-target="#typeMaintenanceModal">
                            <i class="bx bx-plus me-1"></i> Tambah Tipe
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="type-maintenance-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="typeMaintenanceModal" tabindex="-1" aria-labelledby="typeMaintenanceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="type-maintenance-form" class="modal-content" action="{{ route('type-maintenance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="type-maintenance-form-method" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="typeMaintenanceModalLabel">Tambah Tipe</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="type-maintenance-name">Nama<small class="text-danger">*</small></label>
                            <input type="text" class="form-control" id="type-maintenance-name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="type-maintenance-submit-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const table = $('#type-maintenance-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('type-maintenance.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                dom: 'lBfrtip',
                buttons: [
                    { extend: 'copy', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'csv', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'excel', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'pdf', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'print', exportOptions: { columns: ':not(:last-child)' } },
                    'colvis'
                ],
            });

            const typeModalEl = document.getElementById('typeMaintenanceModal');
            const typeModal = new bootstrap.Modal(typeModalEl);
            const typeForm = document.getElementById('type-maintenance-form');
            const methodInput = document.getElementById('type-maintenance-form-method');
            const modalTitle = document.getElementById('typeMaintenanceModalLabel');
            const submitBtn = document.getElementById('type-maintenance-submit-btn');

            const setFormForCreate = () => {
                typeForm.action = "{{ route('type-maintenance.store') }}";
                methodInput.value = '';
                typeForm.reset();
                modalTitle.textContent = 'Tambah Tipe';
                submitBtn.textContent = 'Simpan';
            };

            const setFormForEdit = (data) => {
                typeForm.action = data.updateUrl;
                methodInput.value = 'PUT';
                $('#type-maintenance-name').val(data.name || '');
                modalTitle.textContent = 'Edit Tipe';
                submitBtn.textContent = 'Perbarui';
            };

            $('.btn-create-type').on('click', function () {
                setFormForCreate();
            });

            $(document).on('click', '.btn-edit-type-maintenance', function (e) {
                e.preventDefault();
                const button = $(this);
                setFormForEdit({
                    updateUrl: button.data('update-url'),
                    name: button.data('name'),
                });
                typeModal.show();
            });

            typeModalEl.addEventListener('hidden.bs.modal', () => {
                setFormForCreate();
            });
        });
    </script>
@endpush
