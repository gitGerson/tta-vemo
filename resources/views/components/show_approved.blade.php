<div class="container-xxl">
    <div class="row mb-4">
        <!-- Kartu Pertama -->
        <div class="col-md-5 mb-3 mb-md-0">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
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
                                <td>: {{ \Carbon\Carbon::parse($maintenances->date_in)->translatedFormat('d F Y') }}</td>
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
                                            <span class="badge bg-warning">PROSES PERSETUJUAN</span>
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
                                <td>: {{ $maintenances->requested_by_name ?? ''}}</td>
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
                                <td>: {{ $maintenances->desc }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="control-group ml-2" id="fields">
                        <label class="control-label font-weight-bold" for="field1">BUKTI PERBAIKAN</label>
                        <div class="controls">
                            @foreach ($notes as $note)
                                <div class="d-flex mt-2">
                                    <a target="blank" href="{{ $note->url }}" class="text-truncate btn btn-sm btn-success shadow-sm">
                                        {{ $note->image }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Kedua -->
        <div class="col-md-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Perbaikan</h6>
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
                    @if ($maintenances->status == 'NEW')
                        <div class="mt-3">
                            <input type="checkbox" id="agreeCheckbox">
                            <label for="agreeCheckbox">&nbsp;Saya Setuju dengan perbaikan ini.</label>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <form class="mr-2" id="pandingForm" action="/maintenances/{{ $maintenances->id }}/status" method="post">
                                @csrf
                                <input type="hidden" name="status" value="PANDING">
                                <button type="button" id="pandingButton" class="btn btn-success btn-lg" style="font-size: 16px;" disabled>Kirim Persetujuan</button>
                            </form>
                            <form id="cancelForm" action="/maintenances/{{ $maintenances->id }}/status" method="post">
                                @csrf
                                <input type="hidden" name="status" value="CANCEL">
                                <button type="button" id="cancelButton" class="btn btn-danger btn-lg" style="font-size: 16px;">Batal</button>
                            </form>
                        </div>
                    @elseif($maintenances->status == 'CANCEL')
                        <div class="d-flex justify-content-end mt-3">
                            <form id="newForm" action="/maintenances/{{ $maintenances->id }}/status" method="post">
                                @csrf
                                <input type="hidden" name="status" value="NEW">
                                <button type="button" id="newButton" class="btn btn-success btn-lg" style="font-size: 16px;">Ajukan Persetujuan Lagi</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for PANDING or NEW -->
<div class="modal fade" id="pandingModal" tabindex="-1" role="dialog" aria-labelledby="pandingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pandingModalLabel">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if ($maintenances->status == 'CANCEL')
                    Apakah Anda yakin mengajukan perbaikan ini lagi?
                @else
                    Apakah Anda yakin setuju dengan perbaikan ini?
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmPandingButton">Ya, Setuju</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for CANCEL -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin membatalkan perbaikan ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmCancelButton">Ya, Batalkan</button>
            </div>
        </div>
    </div>
</div>
