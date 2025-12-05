<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-5 mb-3 mb-md-0">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex">
                    <h6 class="m-0 fw-bold text-primary">Kode : {{ $maintenances->code_mt }}</h6>
                    <div class="ms-auto">
                        @if ($maintenances->status == 'NEW')
                            <span class="badge bg-secondary">BELUM SELESAI</span>
                        @elseif($maintenances->status == 'PENDING')
                            <span class="badge bg-warning text-dark">PROSES PERSETUJUAN</span>
                        @elseif($maintenances->status == 'APPROVED')
                            <span class="badge bg-success">SUDAH DISETUJUI</span>
                        @elseif($maintenances->status == 'CANCEL')
                            <span class="badge bg-danger">DIBATALKAN</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table border-0">
                        <tbody class="border-0">
                            <tr>
                                <th class="border-0" scope="row">NO POLISI</th>
                                <td class="border-0">: {{ $maintenances->no_pol }}</td>
                            </tr>
                            <tr>
                                <th scope="row">TOTAL PERBAIKAN</th>
                                <td>: {{ formatRupiah($maintenances->total_amount) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">TANGGAL MASUK</th>
                                <td>: {{ \Carbon\Carbon::parse($maintenances->date_in)->translatedFormat('d F Y') }}
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">TANGGAL KELUAR</th>
                                <td>:
                                    @if ($maintenances->date_out)
                                        {{ \Carbon\Carbon::parse($maintenances->date_out)->translatedFormat('d F Y') }}
                                    @else
                                        @if ($maintenances->status == 'NEW')
                                            <span class="badge bg-secondary">BELUM SELESAI</span>
                                        @elseif($maintenances->status == 'PENDING')
                                            <span class="badge bg-warning text-dark">PROSES PERSETUJUAN</span>
                                        @elseif($maintenances->status == 'APPROVED')
                                            <span class="badge bg-success">SUDAH DISETUJUI</span>
                                        @elseif($maintenances->status == 'CANCEL')
                                            <span class="badge bg-danger">DIBATALKAN</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">KM</th>
                                <td>: {{ $maintenances->mileage }}</td>
                            </tr>
                            <tr>
                                <th scope="row">SERVIS RUTIN</th>
                                <td>:
                                    @if ($maintenances->isServiceRoutine == 0)
                                        <span class="badge bg-success">
                                            YA, TERMASUK
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            TIDAK TERMASUK
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">DIBUAT OLEH</th>
                                <td>: {{ $maintenances->requested_by_name ?? '' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">DISETUJUI OLEH</th>
                                <td>
                                    :
                                    @if ($maintenances->status == 'CANCEL')
                                        <span class="badge bg-danger">DIBATALKAN</span>
                                    @else
                                        {!! $maintenances->approved_by_name 
                                            ? $maintenances->approved_by_name 
                                            : '<span class="badge bg-warning">BELUM DISETUJUI</span>' !!}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">KETERANGAN</th>
                                <td>: {{ $maintenances->description }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mb-3 ms-2" id="fields">
                        <label class="form-label fw-bold" for="field1">
                            BUKTI PERBAIKAN
                        </label>
                        <div>
                            @foreach ($notes as $note)
                                <div class="d-flex mt-2">
                                    <a target="blank" href="{{ $note->url }}"
                                        class="text-truncate btn btn-sm btn-success shadow-sm ">
                                        {{ $note->image }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Detail Perbaikan</h6>
                </div>
                <div class="card-body">
                    <div class="container-fluid">
                        <div class="table-responsive">
                            <table class="table table-bordered search-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">NAMA PERBAIKAN</th>
                                        <th scope="col">HARGA</th>
                                        <th scope="col">QTY</th>
                                        <th scope="col">MATERIAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detail_maintenances as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}. </td>
                                            <td>{{ $row->maintenance_name }}</td>
                                            <td>{{ formatRupiah($row->price) }}</td>
                                            <td>{{ $row->qty }}</td>
                                            <td>{{ $row->material ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($maintenances->status != 'APPROVED')
                        <div class="mt-3" style="{{ $maintenances->status == 'CANCEL' ? 'display: none;' : '' }}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agreeCheckbox">
                                <label class="form-check-label" for="agreeCheckbox">Saya Setuju dengan perbaikan ini.</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <form class="me-1" id="approvedForm"
                                action="/maintenances/{{ $maintenances->id }}/status" method="post"
                                style="{{ $maintenances->status == 'CANCEL' ? 'display: none;' : '' }}">
                                @csrf
                                <input type="hidden" name="status" value="APPROVED">
                                <input type="hidden" name="approval_date" id="approvalDateHidden">
                                <button type="button" id="approvedButton"
                                    class="btn btn-success btn-lg disabled"
                                    style="font-size: 16px;" disabled>Setujui Perbaikan</button>
                            </form>
                            <form id="cancelFromApprovedForm" action="/maintenances/{{ $maintenances->id }}/status"
                                method="post">
                                @csrf
                                <input type="hidden" name="status" value="CANCEL">
                                <button type="button" id="cancelFromApprovedButton"
                                    class="btn btn-danger btn-lg"
                                    style="font-size: 16px;">Batalkan</button>
                            </form>
                        </div>
                    @elseif($maintenances->status == 'APPROVED')
                        <div class="d-flex justify-content-end mt-3">
                            <form action="/maintenances/{{ $maintenances->id }}/status" method="post">
                                @csrf
                                <input type="hidden" name="status" value="PENDING">
                                <button type="submit" class="btn btn-warning btn-lg"
                                    style="font-size: 16px;">Kembalikan ke proses persetujuan</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pandingModal" tabindex="-1" aria-labelledby="pandingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pandingModalLabel">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin menyetujui perbaikan ini ? <br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmapprovedButton">Ya, Setuju</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for CANCEL --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin membatalkan perbaikan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmCancelFromApprovedButton">Ya,
                        Batalkan</button>
                </div>
            </div>
        </div>
    </div>


    @push('addon-script')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#agreeCheckbox').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#approvedButton').removeClass('disabled').prop('disabled', false);
                    } else {
                        $('#approvedButton').addClass('disabled').prop('disabled', true);
                    }
                });

                $('#approvedButton, #newButton').on('click', function() {
                    var pandingModal = new bootstrap.Modal(document.getElementById('pandingModal'));
                    pandingModal.show();
                });

                $('#confirmapprovedButton').on('click', function() {
                    if ($('#approvedForm').length) {
                        $('#approvedForm').submit();
                    } else if ($('#newForm').length) {
                        $('#newForm').submit();
                    }
                    bootstrap.Modal.getInstance(document.getElementById('pandingModal')).hide();
                });

                $('#confirmapprovedButton').on('click', function() {
                    // Get the selected date from the modal
                    var selectedDate = $('#approvalDate').val();

                    // Update the hidden input field in the form with the selected date
                    $('#approvalDateHidden').val(selectedDate);

                    // Submit the form
                    $('#approvedForm').submit();
                });

                $('#cancelFromApprovedButton').on('click', function() {
                    var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
                    cancelModal.show();
                });

                $('#confirmCancelFromApprovedButton').on('click', function() {
                    $('#cancelFromApprovedForm').submit();
                    bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
                });
            });
        </script>
    @endpush
