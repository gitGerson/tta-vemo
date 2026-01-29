@extends('layouts.sneat')

@section('title')
    Daftar Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                <h5 class="mb-0">Daftar Driver</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.drivers.create'))
                        <button type="button" class="btn btn-primary btn-create-driver" data-bs-toggle="modal"
                            data-bs-target="#driverModal">
                            <i class="bx bx-plus me-1"></i> Tambah Driver
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="driver-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>No Identitas</th>
                                <th>No Telepon</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <form id="driver-form" class="modal-content" action="{{ route('driver.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="driver-form-method" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="driverModalLabel">Daftarkan Supir</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3 d-flex flex-column align-items-center">
                                <div class="mb-2"
                                    style="width: 120px; height: 120px; border: 1px solid #e0e0e0; border-radius: 6px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                    <img id="driver-photo-preview" src="{{ asset('assets/driver_avatar.png') }}"
                                        alt="Preview" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <!-- <div class="w-100">
                                    <label class="form-label" for="driver-image">Foto</label>
                                    <input type="file" class="form-control" id="driver-image" name="image"
                                        accept=".jpg,.jpeg,.png,.gif">
                                </div> -->
                            </div>
                            <div class="col-md-9 d-flex flex-column justify-content-center h-100">
                                <!-- <label class="form-label" for="driver-nik">NIK Karyawan <small class="text-danger">*</small></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                            <input type="text" class="form-control" id="driver-nik" name="nik" placeholder="Masukkan NIK" required>
                                            <button type="button" class="btn btn-outline-success" id="nik-search-btn"><i class="bx bx-search"></i></button>
                                        </div> -->
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-6">
                                <label class="form-label" for="driver-image">Foto</label>
                                <input type="file" class="form-control" id="driver-image" name="image"
                                    accept=".jpg,.jpeg,.png,.gif">
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="driver-nik">NIK Karyawan <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                    <input type="text" class="form-control" id="driver-nik" name="nik"
                                        placeholder="Masukkan NIK" required>
                                    <button type="button" class="btn btn-outline-success" id="nik-search-btn"><i
                                            class="bx bx-search"></i></button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="driver-address">Alamat <small
                                        class="text-danger">*</small></label>
                                <textarea class="form-control" id="driver-address" name="address" rows="2"
                                    placeholder="Masukkan Alamat" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-name">Nama <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                    <input type="text" class="form-control" id="driver-name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-no_identity">No Identitas <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                    <input type="text" class="form-control" id="driver-no_identity" name="no_identity"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-residence">Tempat <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-home-alt"></i></span>
                                    <input type="text" class="form-control" id="driver-residence" name="residence" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-date_birth">Tanggal Lahir <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                    <input type="date" class="form-control" id="driver-date_birth" name="date_birth"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-no_phone_1">HP <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                    <input type="text" class="form-control" id="driver-no_phone_1" name="no_phone_1"
                                        required placeholder="08xxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-no_phone_2">HP Wali <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-phone-call"></i></span>
                                    <input type="text" class="form-control" id="driver-no_phone_2" name="no_phone_2"
                                        required placeholder="08xxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-entry_date">Tanggal Masuk <small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="driver-entry_date" name="entry_date"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="driver-status">Status <small
                                        class="text-danger">*</small></label>
                                <select class="form-select" id="driver-status" name="status" required>
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="PASSIVE">PASSIVE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                        <button type="submit" class="btn btn-primary" id="driver-submit-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const table = $('#driver-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('driver.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'no_identity', name: 'no_identity' },
                    { data: 'no_phone_1', name: 'no_phone_1' },
                    { data: 'status', name: 'status' },
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

            const driverModalEl = document.getElementById('driverModal');
            const driverModal = new bootstrap.Modal(driverModalEl);
            const driverForm = document.getElementById('driver-form');
            const methodInput = document.getElementById('driver-form-method');
            const modalTitle = document.getElementById('driverModalLabel');
            const submitBtn = document.getElementById('driver-submit-btn');

            const setFormForCreate = () => {
                driverForm.action = "{{ route('driver.store') }}";
                methodInput.value = '';
                driverForm.reset();
                modalTitle.textContent = 'Tambah Driver';
                submitBtn.textContent = 'Simpan';
                $('#driver-photo-preview').attr('src', "{{ asset('assets/driver_avatar.png') }}");
            };

            const setFormForEdit = (data) => {
                driverForm.action = data.updateUrl;
                methodInput.value = 'PUT';
                $('#driver-name').val(data.name || '');
                $('#driver-nik').val(data.nik || '');
                $('#driver-no_identity').val(data.no_identity || '');
                $('#driver-image').val(data.image || '');
                $('#driver-address').val(data.address || '');
                $('#driver-residence').val(data.residence || '');
                $('#driver-date_birth').val(data.date_birth || '');
                $('#driver-entry_date').val(data.entry_date || '');
                $('#driver-out_date').val(data.out_date || '');
                $('#driver-no_phone_1').val(data.no_phone_1 || '');
                $('#driver-no_phone_2').val(data.no_phone_2 || '');
                $('#driver-status').val(data.status || 'ACTIVE');
                modalTitle.textContent = 'Edit Driver';
                submitBtn.textContent = 'Perbarui';
            };

            $('.btn-create-driver').on('click', function () {
                setFormForCreate();
            });

            $(document).on('click', '.btn-edit-driver', function (e) {
                e.preventDefault();
                const button = $(this);
                setFormForEdit({
                    updateUrl: button.data('update-url'),
                    name: button.data('name'),
                    nik: button.data('nik'),
                    no_identity: button.data('no_identity'),
                    image: button.data('image'),
                    address: button.data('address'),
                    residence: button.data('residence'),
                    date_birth: button.data('date_birth'),
                    entry_date: button.data('entry_date'),
                    out_date: button.data('out_date'),
                    no_phone_1: button.data('no_phone_1'),
                    no_phone_2: button.data('no_phone_2'),
                    status: button.data('status'),
                });
                driverModal.show();
            });

            driverModalEl.addEventListener('hidden.bs.modal', () => {
                setFormForCreate();
            });

            // Preview photo
            $('#driver-image').on('change', function (e) {
                const [file] = e.target.files;
                if (file) {
                    $('#driver-photo-preview').attr('src', URL.createObjectURL(file));
                }
            });

            const fillFromEmp = (emp) => {
                if (!emp) return;
                $('#driver-name').val(emp.Name || emp.name || emp.employee_name || emp.emp_name || $('#driver-name').val());
                $('#driver-no_identity').val(emp.IdCardNumber || emp.id_card_number || emp.nik || $('#driver-no_identity').val());
                $('#driver-address').val(emp.IDAddress || emp.currentAddress || emp.address || $('#driver-address').val());
                $('#driver-residence').val(emp.IDCity || emp.currentCity || emp.residence || $('#driver-residence').val());
                $('#driver-no_phone_1').val(emp.mobilePhone || $('#driver-no_phone_1').val());
                const dob = emp.BirthDate || emp.birth_date || emp.tanggal_lahir;
                if (dob && dob.includes('/')) {
                    const parts = dob.split('/'); // MM/DD/YYYY or DD/MM/YYYY
                    if (parts.length === 3) {
                        const iso = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                        $('#driver-date_birth').val(iso);
                    }
                } else if (dob) {
                    $('#driver-date_birth').val(dob);
                }
                const join = emp.JoinDate || emp.join_date || emp.entry_date;
                if (join && join.includes('/')) {
                    const jparts = join.split('/');
                    if (jparts.length === 3) {
                        const jiso = jparts[2] + '-' + jparts[1].padStart(2, '0') + '-' + jparts[0].padStart(2, '0');
                        $('#driver-entry_date').val(jiso);
                    }
                } else if (join) {
                    $('#driver-entry_date').val(join);
                }
            };

            const fetchNik = () => {
                const nik = $('#driver-nik').val();
                if (!nik) return;
                $.get('{{ route('driver.empInfo') }}', { nik })
                    .done(function (resp) {
                        if (!resp || !resp.success || !resp.data || !resp.data.length) {
                            return;
                        }
                        fillFromEmp(resp.data[0]);
                    })
                    .fail(function () {
                        console.error('Gagal memuat data NIK dari server.');
                    });
            };

            $('#driver-nik').on('blur', fetchNik);
            $('#nik-search-btn').on('click', fetchNik);
        });
    </script>
@endpush
