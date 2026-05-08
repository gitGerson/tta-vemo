@extends('layouts.sneat')

@section('title')
    Daftar Jenis BBM
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
                <h5 class="mb-0">Daftar Jenis BBM</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.fuel_types.create'))
                        <button type="button" class="btn btn-primary btn-create-fuel-type" data-bs-toggle="modal"
                            data-bs-target="#fuelTypeModal">
                            <i class="bx bx-plus me-1"></i> Tambah Jenis BBM
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="fuel-type-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="fuelTypeModal" tabindex="-1" aria-labelledby="fuelTypeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="fuel-type-form" class="modal-content" action="{{ route('fuel-type.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="fuel-type-form-method" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="fuelTypeModalLabel">Tambah Jenis BBM</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="fuel-type-name">Nama<small class="text-danger">*</small></label>
                            <input type="text" class="form-control" id="fuel-type-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="fuel-type-price">Harga<small class="text-danger">*</small></label>
                            <input type="number" class="form-control" id="fuel-type-price" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="fuel-type-submit-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#fuel-type-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('fuel-type.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'price', name: 'price' },
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

            const modalEl = document.getElementById('fuelTypeModal');
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('fuel-type-form');
            const methodInput = document.getElementById('fuel-type-form-method');
            const modalTitle = document.getElementById('fuelTypeModalLabel');
            const submitBtn = document.getElementById('fuel-type-submit-btn');

            const setFormForCreate = () => {
                form.action = "{{ route('fuel-type.store') }}";
                methodInput.value = '';
                form.reset();
                modalTitle.textContent = 'Tambah Jenis BBM';
                submitBtn.textContent = 'Simpan';
            };

            const setFormForEdit = (data) => {
                form.action = data.updateUrl;
                methodInput.value = 'PUT';
                $('#fuel-type-name').val(data.name || '');
                $('#fuel-type-price').val(data.price || '');
                modalTitle.textContent = 'Edit Jenis BBM';
                submitBtn.textContent = 'Perbarui';
            };

            $('.btn-create-fuel-type').on('click', function () {
                setFormForCreate();
            });

            $(document).on('click', '.btn-edit-fuel-type', function (e) {
                e.preventDefault();
                const button = $(this);
                setFormForEdit({
                    updateUrl: button.data('update-url'),
                    name: button.data('name'),
                    price: button.data('price'),
                });
                modal.show();
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                setFormForCreate();
            });
        });
    </script>
@endpush
