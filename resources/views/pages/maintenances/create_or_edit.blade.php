@extends('layouts.sneat')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div id="loader-wrapper" class="text-center" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    @php
        $isApproved = isset($maintenances) && $maintenances->status == 'APPROVED';
    @endphp

    @if ($isApproved)
        @isset($maintenances)
            <div class="row show-approved">
                @include('components.show_approved')
            </div>
        @endisset
    @else
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row hidden-approved">
                <div class="col-xl-6">
                    <div class="card mb-6">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                {{ isset($maintenances) ? 'Kode : ' . $maintenances->code_mt : 'Tambah Perbaikan' }}
                            </h5>
                            @isset($maintenances)
                                <div>
                                    @if ($maintenances->status == 'NEW')
                                        <span class="badge bg-secondary">BELUM SELESAI</span>
                                    @elseif($maintenances->status == 'PENDING')
                                        <span class="badge bg-warning">PROSES PERSETUJUAN</span>
                                    @elseif($maintenances->status == 'APPROVED')
                                        <span class="badge bg-success">SUDAH DISETUJUI</span>
                                    @elseif($maintenances->status == 'CANCEL')
                                        <span class="badge bg-danger">DIBATALKAN</span>
                                    @endif
                                </div>
                            @endisset
                        </div>
                        <div class="card-body mt-3">
                            <form id="addForm" method="POST"
                                action="{{ isset($maintenances) ? route('maintenances.update', $maintenances->id) : route('maintenances.store') }}"
                                enctype="multipart/form-data">
                                @csrf
                                @if (isset($maintenances))
                                    @method('PUT')
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="no_pol" class="form-label">NO POLISI <small
                                                class="text-danger">*</small></label>
                                        <select class="form-select select2" name="no_pol" required>
                                            <option selected disabled>- pilih kendaraan -</option>
                                            @foreach ($vehicle as $row)
                                                <option {{ old('no_pol', isset($maintenances) ? $maintenances->no_pol : request()->query('vehicle')) == $row->plat_new ? 'selected' : '' }}
                                                    value="{{ $row->plat_new }}" data-mileage="{{ $row->mileage }}">
                                                    {{ $row->plat_new }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="mileage" class="form-label">KM <small class="text-danger">*</small></label>
                                        <input type="text" inputmode="numeric" id="mileage" placeholder="Masukan KM "
                                            name="mileage" class="form-control validate-number"
                                            value="{{ old('mileage', isset($maintenances) ? $maintenances->mileage : '') }}">
                                        <small class="text-info" id="mileage-info"></small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="date_in" class="form-label">TANGGAL MASUK <small
                                                class="text-danger">*</small></label>
                                        <input type="date" id="date_in" placeholder="Masukan Tanggal Masuk" name="date_in"
                                            class="form-control validate-number"
                                            value="{{ old('date_in', isset($maintenances) ? $maintenances->date_in : Carbon\Carbon::now()->format('Y-m-d')) }}"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date_out" class="form-label">
                                            TANGGAL KELUAR
                                            @if (isset($maintenances))
                                                <small class="text-danger">*</small>
                                            @endif
                                        </label>
                                        <input type="date" id="date_out" placeholder="Masukan Tanggal Keluar" name="date_out"
                                            class="form-control validate-number"
                                            value="{{ old('date_out', isset($maintenances) ? $maintenances->date_out : '') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">APAKAH TERMASUK SERVIS RUTIN ? <small
                                            class="text-danger">*</small></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="isServiceRoutine"
                                            id="isServiceRoutinetrue" value="0" {{ old('isServiceRoutine', isset($maintenances) ? $maintenances->isServiceRoutine : '') == '0' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isServiceRoutinetrue">
                                            YA
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="isServiceRoutine"
                                            id="isServiceRoutinefalse" value="1" {{ old('isServiceRoutine', isset($maintenances) ? $maintenances->isServiceRoutine : '') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isServiceRoutinefalse">
                                            TIDAK
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="desc" class="form-label">KETERANGAN</label>
                                    <textarea name="desc" id="desc" rows="5"
                                        class="form-control">{{ old('desc', isset($maintenances) ? $maintenances->description : '') }}</textarea>
                                </div>
                        </div>
                    </div>

                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="mb-0">Harga dan Bukti Perbaikan</h5>
                        </div>
                        <div class="card-body mt-3">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_amount" class="form-label">Total Pengeluaran Perbaikan</label>
                                    <input type="text" inputmode="numeric" id="total_amount" placeholder="Masukan sesuai NOTA"
                                        name="total_amount" class="form-control validate-number"
                                        value="{{ old('total_amount', isset($maintenances) ? $maintenances->total_amount : '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kalkulasi" class="form-label">Kalkulasi Detail Harga</label>
                                    <input type="text" inputmode="numeric" id="kalkulasi" readonly placeholder="0"
                                        name="kalkulasi" class="form-control validate-number"
                                        value="{{ isset($maintenances) ? $sum_detail_maintenances : '' }}">
                                </div>
                            </div>

                            @if (isset($maintenances))
                                <div class="mb-3">
                                    <label class="form-label">File Bukti Perbaikan</label>
                                    <div class="control-group" id="fields">
                                        <div class="controls">
                                            @foreach ($notes as $note)
                                                <div class="d-flex mt-2 align-items-center">
                                                    <a target="blank" href="{{ $note->url }}" class="btn btn-sm btn-success me-2">
                                                        {{ $note->image }}
                                                    </a>
                                                    <div class="hidden-approved">
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-id="{{ $note->id }}">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="controls hidden-approved">
                                                <div class="entry input-group mt-2">
                                                    <input class="form-control" name="fields[]" type="file"
                                                        accept=".jpg,.jpeg,.png,.gif,.pdf">
                                                    <button class="btn btn-success btn-add" type="button">
                                                        <i class="bx bx-plus"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-remove" type="button">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label">Upload Bukti Perbaikan</label>
                                    <div class="control-group" id="fields">
                                        <div class="controls">
                                            <div class="entry input-group mt-2">
                                                <input class="form-control" name="fields[]" type="file"
                                                    accept=".jpg,.jpeg,.png,.gif,.pdf">
                                                <button class="btn btn-success btn-add" type="button">
                                                    <i class="bx bx-plus"></i>
                                                </button>
                                                <button class="btn btn-danger btn-remove" type="button">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                            <small class="text-danger" style="display: none;" id="confrm_entry">
                                                Kolom input hanya terdapat 1 (satu) tidak bisa dihapus.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3 hidden-approved">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeCheckbox">
                                    <label class="form-check-label" for="agreeCheckbox">
                                        Apakah data sudah benar ?
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                @isset($maintenances)
                                    <button type="button" class="btn btn-danger me-2 confrmCancel hidden-approved"
                                        data-id="{{ $maintenances->id }}">Batalkan</button>
                                @endisset
                                @if(!isset($maintenances))
                                    <button type="submit" id="submitButton" class="btn btn-primary hidden-approved">
                                        Simpan
                                    </button>
                                @endif

                                @if(isset($maintenances))
                                    <button type="submit" id="submitButton" class="btn btn-primary hidden-approved">
                                        Perbarui
                                    </button>
                                @endif

                            </div>
                            </form>
                        </div>
                    </div>
                </div>
                @include('components.detail_maintenances')
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade hidden-approved" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Penghapusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data ini?
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" id="deleteId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi cancel -->
    <div class="modal fade hidden-approved" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmCancelModalLabel">Konfirmasi Pembatalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin membatalkan perbaikan ini?
                    <form id="cancelStatus" method="POST" action="">
                        <input type="hidden" name="status" value="CANCEL">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelButton">Hapus</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('custom/loading.css') }}">
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            display: flex;
            align-items: center;
        }

        .custom-file-input~.custom-file-label::after {
            content: "Browse";
        }

        .custom-file-label::after {
            content: "";
        }

        .custom-file-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 70px;
        }

        .file-chosen {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file-chosen span {
            display: inline-block;
            margin-right: 10px;
        }

        .file-chosen svg {
            color: green;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table th,
        .table td {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, .075);
        }

        .card-header {
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body {
            padding: 1.25rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .text-truncate {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .card {
                font-size: 10px !important;
            }

            .font-weight-bold {

                font-size: 10px !important;
            }
        }

        @media (max-width: 576px) {
            .btn {
                font-size: 14px;
            }
        }
    </style>
@endpush

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            $('select[name="no_pol"]').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                var mileage = selectedOption.data('mileage');
                $('#mileage-info').html(`
                    <span class="mileage-info ml-1 text-sm text-info" style="font-size: 14px;">
                        - Tercatat Saat ini: ${mileage} KM
                    </span>
                `);
                if (mileage) {
                    $('#mileage').val(mileage);
                }
            });

            var defaultValue = '{{ request()->query('vehicle') }}';
            if (defaultValue) {

                $('select[name="no_pol"]').val(defaultValue).trigger('change');
            }


            $('#staticBackdrop').on('shown.bs.modal', function () {
                $('.select2', this).select2({
                    dropdownParent: $(this)
                });
            });

            $('#staticBackdrop').on('hidden.bs.modal', function () {
                $('.select2', this).select2('destroy');
            });


            // Validasi angka
            $('.validate-number').on('keypress', function (event) {
                if (event.which < 48 || event.which > 57) {
                    event.preventDefault();
                }
            });

            $('#agreeCheckbox').on('change', function () {
                $('#submitButton').prop('disabled', !this.checked);
                $('#submitButton').toggleClass('disabled', !this.checked);
            });

            $('#shareApproved').on('change', function () {
                $('#submitApproved').prop('disabled', !this.checked);
                $('#submitApproved').toggleClass('disabled', !this.checked);
            });


            $(document).on('click', '.edit-detail-mt', function (e) {
                var id_data = $(this).data('id');
                $('.select2').select2({
                    dropdownParent: $('#editModal')
                });
                $('#loader-wrapper').show();
                $.ajax({
                    url: '{{ url('detail_maintenances') }}/' + id_data + '/edit',
                    method: 'GET',
                    success: function (response) {

                        var maintenancesName = document.getElementById('edit-maintenance-name');
                        maintenancesName.innerHTML =
                            '<option value="">- pilih jenis perbaikan</option>';

                        response.jenisMtc.forEach(function (jenisMtc) {
                            const selected = jenisMtc.name == response.data
                                .maintenance_name ? 'selected' : '';
                            maintenancesName.innerHTML +=
                                `<option value="${jenisMtc.name}" ${selected}>${jenisMtc.name}</option>`;
                        });

                        $('#edit-maintenance-name').trigger('change');

                        $('#edit-price').val(response.data.price);
                        $('#edit-qty').val(response.data.qty);
                        $('#edit-material').html(response.data.material);

                        $('#editModal form').attr('action',
                            '{{ url('detail_maintenances') }}/' + id_data);
                        $('#loader-wrapper').hide();
                        $('#editModal').modal('show');
                    }
                });
            });

            { { --handle entry-- } }
            $(document).on('click', '.btn-add', function (e) {
                e.preventDefault();

                var controlForm = $('.controls:first'),
                    currentEntry = $(this).parents('.entry:first'),
                    newEntry = $(currentEntry.clone()).appendTo(controlForm);

                newEntry.find('input').val('');
                newEntry.find('.custom-file-label').html('Choose file');
            }).on('click', '.btn-remove', function (e) {
                e.preventDefault();

                var $entry = $(this).parents('.entry:first');
                if ($('.entry').length > 1) {
                    $entry.remove();
                } else {

                    $entry.find('input').val('');
                    $entry.find('.custom-file-label').html('deleting files ....');
                    $('#confrm_entry').show();

                    setTimeout(function () {
                        $entry.find('.custom-file-label').html('Choose file');
                        $('#confrm_entry').hide();
                    }, 1000);
                }

                return false;
            });


            $(document).on('change', '.custom-file-input', function (e) {
                var fileName = e.target.files[0].name;
                var label = $(this).next('.custom-file-label');
                label.html(`
                        <div class="file-chosen">
                            <span class="font-italic font-weight-bold text-success">${fileName} &#x2713; </span>
                        </div>
                    `);
            });


            { { --handle deleted attecment files-- } }

            let deleteId = null;

            $('a[data-id]').on('click', function (e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                $('#deleteId').val(deleteId);
                $('#deleteForm').attr('action', `/note/${deleteId}`);
                $('#confirmDeleteModal').modal('show');
            });

            $('#confirmDeleteButton').on('click', function () {
                $('#deleteForm').submit();
            });


            { { --handle confrm global deleted-- } }

            $('.confrmCancel').on('click', function () {
                var cancelId = $(this).data('id');
                var actionUrl = "/maintenances/" + cancelId + "/status";
                $('#cancelStatus').attr('action', actionUrl);
                $('#confirmCancelModal').modal('show');
            });

            $('#confirmCancelButton').on('click', function () {
                $('#cancelStatus').submit();
            });


            { { --handle modal create type maintenances  --} }
            var maintenanceModalEl = $('#maintenanceModal');
            var maintenanceModal = (window.bootstrap && maintenanceModalEl.length)
                ? new bootstrap.Modal(maintenanceModalEl[0])
                : null;

            $('#openModalLink').on('click', function () {

                $('#staticBackdrop').hide();
                $('#maintenanceTextarea').val('');
                maintenanceModal ? maintenanceModal.show() : maintenanceModalEl.modal('show');
            });

            $('#closeBtn').on('click', function () {

                $('#staticBackdrop').show();
                maintenanceModal ? maintenanceModal.hide() : maintenanceModalEl.modal('hide');
            });

            $('#saveBtn').on('click', function () {
                var form = $('#maintenanceForm');
                var textarea = $('#maintenanceTextarea');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        maintenanceModal ? maintenanceModal.hide() : maintenanceModalEl.modal('hide');
                        $('#staticBackdrop').show();

                        $('#typeMaintenances').append(
                            $('<option>', {
                                value: response.data,
                                text: response.data
                            }).prop('selected', true)
                        );

                        textarea.val('');
                    },
                    error: function (xhr) {
                        alert('Terjadi kesalahan: ' + xhr.responseText);
                    }
                });
            });

        });
    </script>

    {{-- disabled if status done --}}

    @isset($maintenances)
        @if ($maintenances->status == 'APPROVED')
            <script>
                $(".hidden-approved").hide()
                $(".show-approved").show()
            </script>
        @endif
    @endisset
@endpush