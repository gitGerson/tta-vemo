{{-- Add Detail Modal --}}
@isset($maintenances)
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('detail_maintenances.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <i class="bx bx-plus-circle me-2 text-success"></i>Tambah Item Perbaikan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <input type="hidden" value="{{ $maintenances->id }}" name="maintenance_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="maintenance_name" class="form-label">NAMA BARANG/JASA <small
                                    class="text-danger">*</small></label>
                            <select id="typeMaintenances" class="form-select select2 typeMaintenances" style="width: 100%;"
                                name="maintenance_name" required>
                                <option selected disabled value="">- Pilih jenis -</option>
                                @foreach ($type as $row)
                                    <option value="{{ $row->name }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <a href="javascript:void(0)" class="text-primary mt-1 small" id="openModalLink">
                                <i class="bx bx-plus-circle me-1"></i>Tambah Jenis Baru
                            </a>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">HARGA SATUAN <small class="text-danger">*</small></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" class="form-control validate-number" id="price"
                                    name="price" required placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="qty" class="form-label">JUMLAH <small class="text-danger">*</small></label>
                            <input type="text" inputmode="numeric" class="form-control validate-number" id="qty" name="qty"
                                placeholder="1" value="1">
                        </div>
                        <div class="col-12">
                            <label for="material" class="form-label">MATERIAL / KETERANGAN</label>
                            <textarea class="form-control" id="material" name="material" rows="2"
                                placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Detail Modal --}}
    <div class="modal fade" id="editModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="bx bx-edit me-2 text-info"></i>Edit Item Perbaikan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit-maintenance-name" class="form-label">NAMA BARANG/JASA <small
                                    class="text-danger">*</small></label>
                            <select class="form-select" id="edit-maintenance-name" name="maintenance_name" required>
                                <option value="">- Pilih jenis perbaikan -</option>
                                @foreach ($type as $row)
                                    <option value="{{ $row->name }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-price" class="form-label">HARGA SATUAN <small
                                    class="text-danger">*</small></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" class="form-control validate-number" id="edit-price"
                                    name="price" required placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-qty" class="form-label">JUMLAH <small class="text-danger">*</small></label>
                            <input type="text" inputmode="numeric" class="form-control validate-number" id="edit-qty"
                                name="qty" placeholder="1">
                        </div>
                        <div class="col-12">
                            <label for="edit-material" class="form-label">MATERIAL / KETERANGAN</label>
                            <textarea class="form-control" id="edit-material" name="material" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Type Maintenance Modal --}}
    <div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="maintenanceForm" class="modal-content" action="{{ route('type_maintenances.storeResponse') }}"
                method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceModalLabel">
                        <i class="bx bx-list-plus me-2 text-primary"></i>Tambah Jenis Perbaikan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nama Jenis Perbaikan <small class="text-danger">*</small></label>
                    <textarea id="maintenanceTextarea" name="name" class="form-control" rows="2" required
                        placeholder="Contoh: Ganti Oli, Tune Up, dll..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="closeBtn"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveBtn">
                        <i class="bx bx-check me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pb-4">
                    <i class="bx bx-trash text-danger" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Hapus Item?</h5>
                    <p class="text-muted">Item ini akan dihapus secara permanen.</p>
                    <form id="deleteItemForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteItem">
                        <i class="bx bx-trash me-1"></i>Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endisset

{{-- Cancel Confirmation Modal --}}
<div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pb-4">
                <i class="bx bx-x-circle text-danger" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Batalkan Perbaikan?</h5>
                <p class="text-muted">Status akan diubah menjadi DIBATALKAN dan tidak dapat dikembalikan.</p>
                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="status" value="CANCELLED">
                </form>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tidak</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="bx bx-x me-1"></i>Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

@push('addon-script')
    <script>
        $(document).ready(function () {
            // Open add type maintenance modal
            $('#openModalLink').on('click', function () {
                $('#staticBackdrop').modal('hide');
                $('#maintenanceModal').modal('show');
            });

            // Save new maintenance type
            $('#saveBtn').on('click', function () {
                var formData = $('#maintenanceForm').serialize();

                $.ajax({
                    url: $('#maintenanceForm').attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        // Add new option to selects
                        var newOption = new Option(response.name, response.name, true, true);
                        $('#typeMaintenances').append(newOption).trigger('change');

                        $('#maintenanceModal').modal('hide');
                        $('#maintenanceTextarea').val('');
                        $('#staticBackdrop').modal('show');
                    },
                    error: function (xhr) {
                        alert('Gagal menyimpan jenis perbaikan');
                    }
                });
            });

            // Delete modal - set action URL
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var href = button.data('href');
                $('#deleteItemForm').attr('action', href);
            });

            // Confirm delete item
            $('#confirmDeleteItem').on('click', function () {
                $('#deleteItemForm').submit();
            });

            // Cancel modal - set action URL
            $('#confirmCancelModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                $('#cancelForm').attr('action', '/maintenances/' + id + '/status');
            });

            // Confirm cancel
            $('#confirmCancelBtn').on('click', function () {
                $('#cancelForm').submit();
            });

            // Number formatting for inputs
            $('.validate-number').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
@endpush