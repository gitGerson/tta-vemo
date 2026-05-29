@extends('layouts.sneat')

@section('title')
    Daftar Tujuan
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
                <h5 class="mb-0">Daftar Tujuan</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.destinations.create'))
                        <button type="button" class="btn btn-primary btn-create-destination" data-bs-toggle="modal"
                            data-bs-target="#destinationModal">
                            <i class="bx bx-plus me-1"></i> Tambah Tujuan
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="destination-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tujuan</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="destinationModal" tabindex="-1" aria-labelledby="destinationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="destination-form" class="modal-content" action="{{ route('destination.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="destination-form-method" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="destinationModalLabel">Tambah Tujuan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="destination-name">Tujuan<small class="text-danger">*</small></label>
                            <input type="text" class="form-control" id="destination-name" name="destination" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="destination-desc">Keterangan</label>
                            <textarea class="form-control" id="destination-desc" name="desc" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="destination-submit-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const table = $('#destination-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('destination.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'destination', name: 'destination' },
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

            const destinationModalEl = document.getElementById('destinationModal');
            const destinationModal = new bootstrap.Modal(destinationModalEl);
            const destinationForm = document.getElementById('destination-form');
            const methodInput = document.getElementById('destination-form-method');
            const modalTitle = document.getElementById('destinationModalLabel');
            const submitBtn = document.getElementById('destination-submit-btn');

            const setFormForCreate = () => {
                destinationForm.action = "{{ route('destination.store') }}";
                methodInput.value = '';
                destinationForm.reset();
                modalTitle.textContent = 'Tambah Tujuan';
                submitBtn.textContent = 'Simpan';
            };

            const setFormForEdit = (data) => {
                destinationForm.action = data.updateUrl;
                methodInput.value = 'PUT';
                $('#destination-name').val(data.destination || '');
                $('#destination-desc').val(data.desc || '');
                modalTitle.textContent = 'Edit Tujuan';
                submitBtn.textContent = 'Perbarui';
            };

            $('.btn-create-destination').on('click', function () {
                setFormForCreate();
            });

            $(document).on('click', '.btn-edit-destination', function (e) {
                e.preventDefault();
                const button = $(this);
                setFormForEdit({
                    updateUrl: button.data('update-url'),
                    destination: button.data('destination'),
                    desc: button.data('desc'),
                });
                destinationModal.show();
            });

            destinationModalEl.addEventListener('hidden.bs.modal', () => {
                setFormForCreate();
            });
        });
    </script>
@endpush
