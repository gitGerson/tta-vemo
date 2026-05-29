@extends('layouts.sneat')

@section('title')
    Daftar Perusahaan
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
                <h5 class="mb-0">Daftar Perusahaan</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.companies.create'))
                        <button type="button" class="btn btn-primary btn-create-company" data-bs-toggle="modal"
                            data-bs-target="#companyModal">
                            <i class="bx bx-plus me-1"></i> Tambah Perusahaan
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="company-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="company-form" class="modal-content" action="{{ route('company.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="company-form-method" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="companyModalLabel">Tambah Perusahaan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="company-code">Kode<small class="text-danger">*</small></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class='bx bx-qr'></i></span>
                                <input type="text" class="form-control" id="company-code" name="code" value="{{ old('code') }}" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="company-name">Nama<small class="text-danger">*</small></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                <input type="text" class="form-control" id="company-name" name="name" value="{{ old('name') }}" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="company-desc">Keterangan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-detail"></i></span>
                                <textarea id="company-desc" class="form-control" name="desc" rows="3">{{ old('desc') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="company-submit-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const table = $('#company-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('company.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    { data: 'desc', name: 'desc' },
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

            const companyModalEl = document.getElementById('companyModal');
            const companyModal = new bootstrap.Modal(companyModalEl);
            const companyForm = document.getElementById('company-form');
            const methodInput = document.getElementById('company-form-method');
            const modalTitle = document.getElementById('companyModalLabel');
            const submitBtn = document.getElementById('company-submit-btn');

            const setFormForCreate = () => {
                companyForm.action = "{{ route('company.store') }}";
                methodInput.value = '';
                companyForm.reset();
                modalTitle.textContent = 'Tambah Perusahaan';
                submitBtn.textContent = 'Simpan';
            };

            const setFormForEdit = (data) => {
                companyForm.action = data.updateUrl;
                methodInput.value = 'PUT';
                document.getElementById('company-code').value = data.code || '';
                document.getElementById('company-name').value = data.name || '';
                document.getElementById('company-desc').value = data.desc || '';
                modalTitle.textContent = 'Edit Perusahaan';
                submitBtn.textContent = 'Perbarui';
            };

            $('.btn-create-company').on('click', function () {
                setFormForCreate();
            });

            $(document).on('click', '.btn-edit-company', function (e) {
                e.preventDefault();
                const button = $(this);
                setFormForEdit({
                    updateUrl: button.data('update-url'),
                    code: button.data('code'),
                    name: button.data('name'),
                    desc: button.data('desc'),
                });
                companyModal.show();
            });

            companyModalEl.addEventListener('hidden.bs.modal', () => {
                setFormForCreate();
            });
        });
    </script>
@endpush
