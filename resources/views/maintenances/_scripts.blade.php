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

            let isValid = true;
            form.querySelectorAll('[required]').forEach((field) => {
                if (!isFieldFilled(field)) isValid = false;
            });

            const agreeCheckbox = document.getElementById('agreeCheckbox');
            if (agreeCheckbox && !agreeCheckbox.checked) isValid = false;

            if (requireUpload) {
                const fileCount = pond ? pond.getFiles().length : 0;
                if (fileCount + existingUploadCount === 0) isValid = false;
            }

            const $submitButton = $('#submitButton');
            const $saveButton = $('button[form="mainForm"][type="submit"]');
            if ($submitButton.length) $submitButton.prop('disabled', !isValid);
            if ($saveButton.length) $saveButton.prop('disabled', !isValid);
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

        // ── Repeater ───────────────────────────────────────────────
        let repeaterIndex = 0;

        $('#addRepeaterItem').on('click', function () {
            const template = $('#repeaterTemplate').html();
            const $newItem = $(template.replace(/__INDEX__/g, repeaterIndex).trim());
            $('#repeaterItems').append($newItem);
            $newItem.find('.select2').select2({ width: '100%' });
            repeaterIndex++;
            updateTotalDisplay();
        });

        $(document).on('click', '.remove-repeater-item', function () {
            $(this).closest('.repeater-item').remove();
            updateTotalDisplay();
        });

        $(document).on('input', '.repeater-price, .repeater-qty', function () {
            const item = $(this).closest('.repeater-item');
            const price = parseInt(item.find('.repeater-price').val().replace(/[^0-9]/g, ''), 10) || 0;
            const qty = parseInt(item.find('.repeater-qty').val(), 10) || 1;
            item.find('.repeater-subtotal').text('Rp ' + formatNumber(price * qty));
            updateTotalDisplay();
        });

        $(document).on('input', '.repeater-price', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function updateTotalDisplay() {
            let total = 0;
            $('.repeater-item').each(function () {
                const item = $(this);
                const subtotalData = item.data('subtotal');
                if (subtotalData !== undefined) {
                    total += parseInt(subtotalData, 10) || 0;
                    return;
                }
                const price = parseInt(item.find('.repeater-price').val().replace(/[^0-9]/g, ''), 10) || 0;
                const qty = parseInt(item.find('.repeater-qty').val(), 10) || 1;
                total += price * qty;
            });
            $('#totalDisplay').text(formatNumber(total));
        }

        // Delete existing detail item
        $(document).on('click', '.delete-existing-item', function () {
            const btn = $(this);
            const itemDiv = btn.closest('.repeater-item');
            const href = btn.data('href');

            Swal.fire({
                title: 'Hapus item?',
                text: 'Item ini akan dihapus permanen.',
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
                        itemDiv.fadeOut(300, function () {
                            $(this).remove();
                            updateTotalDisplay();
                        });
                        Swal.fire('Terhapus!', 'Item berhasil dihapus.', 'success');
                    },
                    error: function () {
                        Swal.fire('Error!', 'Gagal menghapus item.', 'error');
                    }
                });
            });
        });

        $(document).on('input change', '#mainForm input, #mainForm select, #mainForm textarea', updateSubmitState);
        $(document).on('change', '.select2', updateSubmitState);

        updateSubmitState();
        updateTotalDisplay();
    });
</script>
