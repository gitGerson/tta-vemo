<div class="col-md-6">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Detail Perbaikan</h6>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                @if (isset($maintenances))
                    <button type="button" class="btn btn-success mb-2 hidden-approved mt-3" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop" style="font-size: 15px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-circle-plus">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M8 12h8" />
                            <path d="M12 8v8" />
                        </svg>
                        Tambah Detail
                    </button>
                @endif
                <div class="table-responsive mt-3">
                    <table class="table table-bordered search-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">NAMA PERBAIKAN</th>
                                <th scope="col">HARGA</th>
                                <th scope="col">QTY</th>
                                {{-- <th scope="col">SUB TOTAL</th> --}}
                                <th scope="col">MATERIAL</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!isset($maintenances))
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Data belum diisi</td>
                                </tr>
                            @elseif ($detail_maintenances && $detail_maintenances->isNotEmpty())
                                @foreach ($detail_maintenances as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}.</td>
                                        <td>{{ $row->maintenance_name }}</td>
                                        <td>Rp {{ number_format($row->price, 0, ',', '.') }}</td>
                                        <td>{{ $row->qty }}</td>
                                        <td>{{ $row->material ?? '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm edit-detail-mt hidden-approved"
                                                data-id="{{ $row->id }}">
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm hidden-approved"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-href="{{ route('detail_maintenances.destroy', $row->id) }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @isset($maintenances)
                    <form action="/maintenances/{{ $maintenances->id }}/status" method="post" class="hidden-approved">
                        @csrf
                        <input type="hidden" name="status" value="PENDING">
                        <div style="margin-top: 20px;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="shareApproved">
                                <label class="form-check-label" for="shareApproved">Apakah sudah selesai rekap detailnya ?.</label>
                            </div>
                        </div>
                        <button type="submit" id="submitApproved"
                            class="btn btn-primary btn-lg d-flex justify-content-end ms-auto disabled"
                            style="font-size: 16px;" disabled>Ya, Kirim persetujuan
                        </button>
                    </form>
                @endisset
            </div>
        </div>
    </div>
</div>

@isset($maintenances)
    {{--  modal create  --}}
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('detail_maintenances.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Detail Perbaikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <input type="hidden" value="{{ $maintenances->id }}" name="maintenance_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="maintenance_name" class="form-label">NAMA PERBAIKAN <small class="text-danger">*</small></label>
                            <select id="typeMaintenances" class="form-control select2 typeMaintenances" style="width: 100%;"
                                name="maintenance_name" aria-label="Default select example" required>
                                <option selected disabled>- pilih jenis -</option>
                                @foreach ($type as $row)
                                    <option value="{{ $row->name }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <a href="javascript:void(0)" class="text-primary mt-1" style="font-size: 13px !important;"
                                id="openModalLink">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-diff">
                                    <path d="M12 3v14" />
                                    <path d="M5 10h14" />
                                    <path d="M5 21h14" />
                                </svg> Tambah Jenis Maintenances
                            </a>
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label">HARGA <small class="text-danger">*</small> </label>
                            <input type="text" inputmode="numeric" class="form-control validate-number"
                                id="price" name="price" required placeholder="Masukan harga perdetail">
                        </div>
                        <div class="col-md-6">
                            <label for="qty" class="form-label">QTY <small class="text-danger">*</small> </label>
                            <input type="text" inputmode="numeric" class="form-control validate-number"
                                id="qty" name="qty" placeholder="Masukan Qty">
                        </div>
                        <div class="col-md-12">
                            <label for="material" class="form-label">MATERIAL</label>
                            <textarea class="form-control" id="material" name="material"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{--  modal edit  --}}
    <div class="modal fade" id="editModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="" method="POST" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit detail Perbaikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="maintenance_name" class="form-label">NAMA PERBAIKAN <small class="text-danger">*</small> </label>
                            <select class="form-select select2" id="edit-maintenance-name"
                                name="maintenance_name" aria-label="Default select example" required>
                                <option selected>- pilih jenis perbaikan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">HARGA <small class="text-danger">*</small> </label>
                            <input type="text" inputmode="numeric" class="form-control validate-number"
                                id="edit-price" name="price" required placeholder="Masukan harga perdetail">
                        </div>
                        <div class="col-md-6">
                            <label for="qty" class="form-label">QTY <small class="text-danger">*</small> </label>
                            <input type="text" inputmode="numeric" class="form-control validate-number"
                                id="edit-qty" name="qty" placeholder="Masukan Qty">
                        </div>
                        <div class="col-md-12">
                            <label for="material" class="form-label">MATERIAL</label>
                            <textarea class="form-control" id="edit-material" name="material"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{--  modal cerated type maintenances  --}}
    <div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="maintenanceForm" class="modal-content" action="{{ route('type_maintenances.storeResponse') }}"
                method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceModalLabel">Tambah Jenis Perbaikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea id="maintenanceTextarea" name="name" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeBtn" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
@endisset


{{-- <script>
// Auto calculate price functionality for both create and edit modals
$(document).ready(function() {
    
    // Function to format number for display (with thousands separator)
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Function to clean number for calculation (remove formatting)
    function cleanNumber(str) {
        return parseFloat(str.toString().replace(/[^\d.-]/g, '')) || 0;
    }
    
    // For CREATE modal
    const createPriceInput = $('#price');
    const createQtyInput = $('#qty');
    let createBasePrice = 0; // Store original price per unit
    
    // Store base price when user first enters price
    createPriceInput.on('input blur', function() {
        const currentValue = cleanNumber($(this).val());
        const currentQty = cleanNumber(createQtyInput.val());
        
        // If qty is 0 or 1, this is likely the base price
        if (currentQty <= 1) {
            createBasePrice = currentValue;
        } else {
            // If qty > 1, calculate back to base price
            createBasePrice = currentQty > 0 ? currentValue / currentQty : currentValue;
        }
    });
    
    // Calculate when quantity changes
    createQtyInput.on('input blur', function() {
        const qty = cleanNumber($(this).val());
        
        if (createBasePrice > 0 && qty > 0) {
            const total = createBasePrice * qty;
            createPriceInput.val(total); // Store as plain number for form submission
        } else if (qty === 0 || $(this).val() === '') {
            // Reset to base price if qty is cleared
            if (createBasePrice > 0) {
                createPriceInput.val(createBasePrice);
            }
        }
    });
    
    // For EDIT modal
    const editPriceInput = $('#edit-price');
    const editQtyInput = $('#edit-qty');
    let editBasePrice = 0; // Store original price per unit
    
    // Store base price when user first enters price
    editPriceInput.on('input blur', function() {
        const currentValue = cleanNumber($(this).val());
        const currentQty = cleanNumber(editQtyInput.val());
        
        // If qty is 0 or 1, this is likely the base price
        if (currentQty <= 1) {
            editBasePrice = currentValue;
        } else {
            // If qty > 1, calculate back to base price
            editBasePrice = currentQty > 0 ? currentValue / currentQty : currentValue;
        }
    });
    
    // Calculate when quantity changes
    editQtyInput.on('input blur', function() {
        const qty = cleanNumber($(this).val());
        
        if (editBasePrice > 0 && qty > 0) {
            const total = editBasePrice * qty;
            editPriceInput.val(total); // Store as plain number for form submission
        } else if (qty === 0 || $(this).val() === '') {
            // Reset to base price if qty is cleared
            if (editBasePrice > 0) {
                editPriceInput.val(editBasePrice);
            }
        }
    });
    
    // Reset base prices when modals are closed
    $('#staticBackdrop').on('hidden.bs.modal', function() {
        createBasePrice = 0;
        createPriceInput.val('');
        createQtyInput.val('');
    });
    
    $('#editModal').on('hidden.bs.modal', function() {
        editBasePrice = 0;
    });
    
    // Initialize base price when edit modal is opened with existing data
    $('#editModal').on('shown.bs.modal', function() {
        const currentPrice = cleanNumber(editPriceInput.val());
        const currentQty = cleanNumber(editQtyInput.val()) || 1;
        
        if (currentPrice > 0) {
            editBasePrice = currentQty > 0 ? currentPrice / currentQty : currentPrice;
        }
    });
});

</script> --}}
