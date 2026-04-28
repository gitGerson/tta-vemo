@extends('layouts.sneat')

@section('title')
    Daftar Kendaraan
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
                <h5 class="mb-0">Daftar Kendaraan</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.vehicles.create'))
                        <button type="button" class="btn btn-primary btn-create-vehicle" data-bs-toggle="modal"
                            data-bs-target="#vehicleModal">
                            <i class="bx bx-plus me-1"></i> Tambah Kendaraan
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="vehicle-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Perusahaan</th>
                                <th>Tipe</th>
                                <th>Plat Baru</th>
                                <th>Plat Lama</th>
                                <th>Status</th>
                                <th>MTC</th>
                                <th>Private</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="vehicle-form" class="modal-content" action="{{ route('vehicle.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="vehicle-form-method" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="vehicleModalLabel">Tambah Kendaraan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-company_id">Perusahaan</label>
                                <select class="form-select" id="vehicle-company_id" name="company_id">
                                    <option value="">- Pilih Perusahaan -</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">
                                            {{ $company->name }} ({{ $company->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-type">Tipe<small class="text-danger">*</small></label>
                                <input type="text" class="form-control" id="vehicle-type" name="type" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-entry_year">Tahun Masuk<small class="text-danger">*</small></label>
                                <input type="text" class="form-control" id="vehicle-entry_year" name="entry_year" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-plat_new">Plat Baru<small class="text-danger">*</small></label>
                                <input type="text" class="form-control" id="vehicle-plat_new" name="plat_new" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-plat_old">Plat Lama</label>
                                <input type="text" class="form-control" id="vehicle-plat_old" name="plat_old">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-mileage">Kilometer</label>
                                <input type="text" class="form-control" id="vehicle-mileage" name="mileage" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-status">Status<small class="text-danger">*</small></label>
                                <select class="form-select" id="vehicle-status" name="status" required>
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="PASSIVE">PASSIVE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-mtc">Maintenance<small class="text-danger">*</small></label>
                                <select class="form-select" id="vehicle-mtc" name="mtc" required>
                                    <option value="NO">NO</option>
                                    <option value="YES">YES</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="vehicle-isprivate">Private<small class="text-danger">*</small></label>
                                <select class="form-select" id="vehicle-isprivate" name="isprivate" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="vehicle-desc">Keterangan</label>
                                <textarea class="form-control" id="vehicle-desc" name="desc" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="vehicle-submit-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const table = $('#vehicle-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('vehicle.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'company_name', name: 'company.name' },
                    { data: 'type', name: 'type' },
                    { data: 'plat_new', name: 'plat_new' },
                    { data: 'plat_old', name: 'plat_old' },
                    { data: 'status', name: 'status' },
                    { data: 'mtc', name: 'mtc' },
                    { data: 'isprivate_label', name: 'isprivate', orderable: false, searchable: false },
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

            const vehicleModalEl = document.getElementById('vehicleModal');
            const vehicleModal = new bootstrap.Modal(vehicleModalEl);
            const vehicleForm = document.getElementById('vehicle-form');
            const methodInput = document.getElementById('vehicle-form-method');
            const modalTitle = document.getElementById('vehicleModalLabel');
            const submitBtn = document.getElementById('vehicle-submit-btn');

            const setFormForCreate = () => {
                vehicleForm.action = "{{ route('vehicle.store') }}";
                methodInput.value = '';
                vehicleForm.reset();
                modalTitle.textContent = 'Tambah Kendaraan';
                submitBtn.textContent = 'Simpan';
            };

            const setFormForEdit = (data) => {
                vehicleForm.action = data.updateUrl;
                methodInput.value = 'PUT';
                $('#vehicle-company_id').val(data.company_id || '');
                $('#vehicle-type').val(data.type || '');
                $('#vehicle-entry_year').val(data.entry_year || '');
                $('#vehicle-mileage').val(data.mileage || '0');
                $('#vehicle-plat_new').val(data.plat_new || '');
                $('#vehicle-plat_old').val(data.plat_old || '');
                $('#vehicle-status').val(data.status || 'ACTIVE');
                $('#vehicle-mtc').val(data.mtc || 'NO');
                $('#vehicle-isprivate').val(data.isprivate ? '1' : '0');
                $('#vehicle-desc').val(data.desc || '');
                modalTitle.textContent = 'Edit Kendaraan';
                submitBtn.textContent = 'Perbarui';
            };

            $('.btn-create-vehicle').on('click', function () {
                setFormForCreate();
            });

            $(document).on('click', '.btn-edit-vehicle', function (e) {
                e.preventDefault();
                const button = $(this);
                setFormForEdit({
                    updateUrl: button.data('update-url'),
                    company_id: button.data('company_id'),
                    type: button.data('type'),
                    entry_year: button.data('entry_year'),
                    mileage: button.data('mileage'),
                    plat_new: button.data('plat_new'),
                    plat_old: button.data('plat_old'),
                    status: button.data('status'),
                    mtc: button.data('mtc'),
                    isprivate: button.data('isprivate') === 1 || button.data('isprivate') === '1',
                    desc: button.data('desc'),
                });
                vehicleModal.show();
            });

            vehicleModalEl.addEventListener('hidden.bs.modal', () => {
                setFormForCreate();
            });
        });
    </script>
@endpush
