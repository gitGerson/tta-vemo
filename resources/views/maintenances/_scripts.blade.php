<script>
    $(document).ready(function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const pageConfig = document.getElementById('maintenancesPageConfig');
        const uploadsProcessUrl = pageConfig?.dataset.uploadsProcess || '';
        const uploadsRevertUrl = pageConfig?.dataset.uploadsRevert || '';
        const uploadsLoadUrl = pageConfig?.dataset.uploadsLoad || '';
        const requireUpload = pageConfig?.dataset.requireUpload === '1';
        let existingUploadCount = parseInt(pageConfig?.dataset.existingUploadCount || '0', 10) || 0;
        let pond = null;

        // Info banner: dismissable, with a "Petunjuk" button to bring it back.
        // Dismissal is remembered per browser session only.
        const infoBanner = document.getElementById('maintenanceInfoBanner');
        const infoBannerClose = document.getElementById('maintenanceInfoBannerClose');
        const infoBannerToggle = document.getElementById('maintenanceHintsToggle');
        const BANNER_KEY = 'maintenanceInfoBannerDismissed';

        function hideInfoBanner() {
            infoBanner?.classList.add('d-none');
            sessionStorage.setItem(BANNER_KEY, '1');
        }

        function showInfoBanner() {
            if (!infoBanner) return;
            infoBanner.classList.remove('d-none');
            sessionStorage.removeItem(BANNER_KEY);
            infoBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (infoBanner && sessionStorage.getItem(BANNER_KEY) === '1') {
            infoBanner.classList.add('d-none');
        }
        infoBannerClose?.addEventListener('click', hideInfoBanner);
        infoBannerToggle?.addEventListener('click', showInfoBanner);

        // Select2
        $('.select2').select2({ width: '100%' });

        // FilePond
        if (typeof FilePond !== 'undefined') {
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview
            );

            const inputElement = document.querySelector('input.filepond');
            if (inputElement) {
                const pondOptions = {
                    allowMultiple: true,
                    allowReorder: true,
                    maxFileSize: '3MB',
                    labelIdle: '<span class="filepond--label-action">Klik untuk upload</span> atau tarik file ke sini',
                    acceptedFileTypes: ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'application/pdf'],
                    // Uniform item height so the 2-column grid (see _styles.blade.php)
                    // stays inside the card — FilePond sizes items from this ratio
                    // instead of each loaded image, so the container height is correct.
                    styleItemPanelAspectRatio: 0.75,
                    credits: false
                };

                if (uploadsProcessUrl && uploadsRevertUrl && uploadsLoadUrl) {
                    pondOptions.server = {
                        process: { url: uploadsProcessUrl, headers: { 'X-CSRF-TOKEN': csrfToken } },
                        revert: { url: uploadsRevertUrl, headers: { 'X-CSRF-TOKEN': csrfToken } },
                        load: uploadsLoadUrl
                    };
                }

                pond = FilePond.create(inputElement, pondOptions);
                pond.on('addfile', updateSubmitState);
                pond.on('removefile', updateSubmitState);

                // Click-to-preview for newly added images
                document.addEventListener('click', function (e) {
                    const previewWrapper = e.target.closest('.filepond--image-preview-wrapper');
                    if (!previewWrapper || !pond) return;

                    const allItems = Array.from(document.querySelectorAll('.filepond--item'));
                    const index = allItems.indexOf(previewWrapper.closest('.filepond--item'));
                    const files = pond.getFiles();
                    if (index < 0 || index >= files.length) return;

                    const fileItem = files[index];
                    if (!fileItem.fileType || !fileItem.fileType.startsWith('image/')) return;

                    let imageUrl = '';
                    if (fileItem.file instanceof File || fileItem.file instanceof Blob) {
                        imageUrl = URL.createObjectURL(fileItem.file);
                    } else if (fileItem.serverId && uploadsLoadUrl) {
                        imageUrl = uploadsLoadUrl + encodeURIComponent(fileItem.serverId);
                    }
                    if (!imageUrl) return;

                    Swal.fire({
                        imageUrl: imageUrl,
                        imageAlt: fileItem.filename || 'Preview',
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 'auto',
                        customClass: { image: 'filepond-preview-image' }
                    });
                });
            }
        }

        // Service routine toggle
        $('#isServiceRoutineToggle').on('change', function () {
            $('#isServiceRoutineValue').val($(this).is(':checked') ? '0' : '1');
        });

        // Agree checkbox
        $('#agreeCheckbox').on('change', updateSubmitState);

        function isFieldFilled(field) {
            if (field.disabled) return true;
            if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
            return field.value.trim() !== '';
        }

        function updateSubmitState() {
            const form = document.getElementById('mainForm');
            if (!form) return;

            const missing = [];

            const hasEmptyRequired = Array.from(form.querySelectorAll('[required]')).some((field) => !isFieldFilled(field));
            if (hasEmptyRequired) missing.push('lengkapi kolom wajib');

            if (requireUpload) {
                const fileCount = pond ? pond.getFiles().length : 0;
                if (fileCount + existingUploadCount === 0) missing.push('unggah bukti pembayaran');
            }

            const agreeCheckbox = document.getElementById('agreeCheckbox');
            if (agreeCheckbox && !agreeCheckbox.checked) missing.push('centang konfirmasi');

            const isValid = missing.length === 0;

            const $submitButton = $('#submitButton');
            const $saveButton = $('button[form="mainForm"][type="submit"]');
            if ($submitButton.length) $submitButton.prop('disabled', !isValid);
            if ($saveButton.length) $saveButton.prop('disabled', !isValid);

            // Tell the user exactly what is still blocking the primary save.
            const $hint = $('#submitHint');
            const $hintText = $('#submitHintText');
            if ($hint.length) {
                if (isValid) {
                    $hint.addClass('d-none');
                } else {
                    $hintText.text('Untuk menyimpan: ' + missing.join(', ') + '.');
                    $hint.removeClass('d-none');
                }
            }
        }

        // Preview existing images
        $(document).on('click', '.preview-existing-image', function (e) {
            e.preventDefault();
            const imageUrl = $(this).data('url');
            if (!imageUrl) return;
            Swal.fire({
                imageUrl: imageUrl,
                imageAlt: $(this).text().trim() || 'Preview',
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                customClass: { image: 'filepond-preview-image' }
            });
        });

        // Auto-fill mileage from selected vehicle
        $('select[name="no_pol"]').on('change', function () {
            const mileage = $(this).find('option:selected').data('mileage');
            if (mileage) {
                $('#mileage').val(mileage);
                $('#mileage-info').text('KM terakhir tercatat: ' + mileage);
            }
        });

        // Delete existing note file
        $(document).on('click', 'button[data-note-id]', function () {
            const btn = $(this);
            const href = btn.data('href');
            const row = btn.closest('.d-flex');

            Swal.fire({
                title: 'Hapus file?',
                text: 'File akan dihapus permanen',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: href,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: function () {
                        row.remove();
                        existingUploadCount = Math.max(0, existingUploadCount - 1);
                        updateSubmitState();
                        Swal.fire('Terhapus!', 'File berhasil dihapus.', 'success');
                    },
                    error: function () {
                        Swal.fire('Error!', 'Gagal menghapus file.', 'error');
                    }
                });
            });
        });

        // ── Detail Perbaikan ───────────────────────────────────────
        // The grid is the shared x-table component in local mode (see
        // resources/js/modules/tabulator.js). It owns add/edit/delete and items[]
        // serialization; here we only mirror its running total into #totalDisplay
        // and refresh the submit state.
        const detailTableEl = document.getElementById('maintenance-detail-tabulator');
        if (detailTableEl) {
            detailTableEl.addEventListener('tabulator:local-changed', function () {
                const table = this.tabulatorInstance;
                if (!table) return;
                const total = table.getData().reduce(function (sum, r) {
                    return sum + (Number(r.price) || 0) * (Number(r.qty) || 1);
                }, 0);
                const totalEl = document.getElementById('totalDisplay');
                if (totalEl) totalEl.textContent = total.toLocaleString('id-ID');
                updateSubmitState();
            });
        }

        $(document).on('input change', '#mainForm input, #mainForm select, #mainForm textarea', updateSubmitState);
        $(document).on('change', '.select2', updateSubmitState);

        updateSubmitState();
    });
</script>
