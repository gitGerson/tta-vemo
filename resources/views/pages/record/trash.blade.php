@extends('layouts.sneat')

@section('content')

<div class="card mb-4">
<div class="card-body">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pelacakan BBM Nonaktif</h5>
    </div>

    <div class="table-responsive text-nowrap ms-6">
    <table id="dataTable" class="table table-bordered table-striped">
        <thead class="table-light">
        <tr class="text-nowrap">
            <th scope="col">No</th>
            <th scope="col">TANGGAL</th>
            <th scope="col">KODE</th>
            <th scope="col">NO POLISI</th>
            <th scope="col">SUPIR 1</th>
            <th scope="col">SUPIR 2</th>
            <th scope="col">KM AWAL</th>
            <th scope="col">KM AKHIR</th>
            <th scope="col">SKALA</th>
            <th scope="col">TUJUAN</th>
            @if (auth()->user()->can('activate data nonaktif') || auth()->user()->can('delete data nonaktif'))
                <th scope="col">ACTION</th>
            @endif
        </tr>
        </thead>
        <tbody class="table-border-bottom-0">
        </tbody>
    </table>
    </div>
</div>
</div>

{{-- Modal Restore Bootstrap 5 --}}
<div class="modal fade" id="restoreDateModal" tabindex="-1" aria-labelledby="restoreDateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreDateModalLabel">Konfirmasi Pengembalian Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengaktifkannya kembali?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Batal</button>
                <form id="restoreDateForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Aktifkan Kembali</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Konfirmasi Bootstrap 5 -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Permanen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapusnya?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Batal</button>
                <form id="forceDeleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@push('addon-script')
    <style>
        .table-responsive {
            overflow-x: auto;
        }
    </style>
@endpush
@push('addon-script')
    <!-- Page level custom scripts -->
    <script>
        // AJAX DataTable
        $(document).ready(function() {
            $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: "{{ route('trash.record') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'company_id',
                        name: 'company_id'
                    },
                    {
                        data: 'no_pol',
                        name: 'no_pol'
                    },
                    {
                        data: 'driver_1',
                        name: 'driver_1'
                    },
                    {
                        data: 'driver_2',
                        name: 'driver_2'
                    },
                    {
                        data: 'start_km',
                        name: 'start_km'
                    },
                    {
                        data: 'finish_km',
                        name: 'finish_km'
                    },
                    {
                        data: 'skala',
                        name: 'scale'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },

                    @if (auth()->user()->can('activate data nonaktif') || auth()->user()->can('delete data nonaktif'))
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    @endif
                ],
                dom: 'Bflrtip',
                buttons: [{
                        extend: 'excel',
                        filename: 'Daftar_Pelacakan_BBM_NonAktif',
                        title: 'Daftar Pelacakan BBM Nonaktif',
                        text: "<i class='bx bx-export me-1'></i> Export Excel",
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }

                ],
                language: {
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                drawCallback: function(settings) {
                    let api = this.api();
                    let dataCount = api.data().count();

                    if (dataCount === 0) {
                        $('.buttons-excel').hide();
                    } else {
                        $('.buttons-excel').show();
                    }

                    if (dataCount < 10){
                        $('.dataTables_length').hide();
                    } else{
                        $('.dataTables_length').show();
                    }
                }
            });

            $('#confirmDeleteModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var actionUrl = "{{ route('forceDeleteConfirmed.record', ['id' => ':id']) }}";
                actionUrl = actionUrl.replace(':id', id);
                $('#forceDeleteForm').attr('action', actionUrl);
            });

            $('#restoreDateModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var actionUrl = "{{ route('restore.record', ['id' => ':id']) }}";
                actionUrl = actionUrl.replace(':id', id);
                $('#restoreDateForm').attr('action', actionUrl);
            });
        });
    </script>
@endpush
