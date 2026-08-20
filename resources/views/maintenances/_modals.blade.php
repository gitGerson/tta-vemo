{{-- Cancel Confirmation Modal --}}
<div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
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

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#confirmCancelModal').on('show.bs.modal', function (event) {
                var id = $(event.relatedTarget).data('id');
                $('#cancelForm').attr('action', '{{ url('maintenances') }}/' + id + '/status');
            });

            $('#confirmCancelBtn').on('click', function () {
                $('#cancelForm').submit();
            });

            $('.validate-number').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
@endpush
