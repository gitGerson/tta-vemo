$(document).ready(function() {
    const $ = window.$;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const pageConfig = document.getElementById('maintenancesPageConfig');
    const uploadsProcessUrl = pageConfig?.dataset.uploadsProcess || '';
    const uploadsRevertUrl = pageConfig?.dataset.uploadsRevert || '';
    const uploadsLoadUrl = pageConfig?.dataset.uploadsLoad || '';

    // Initialize Select2
    $('.select2').select2({
        width: '100%'
    });

    // Initialize FilePond
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
                labelIdle: '<span class="filepond--label-action">Klik untuk upload</span> atau tarik file ke sini<br><small>PNG, JPG, GIF, SVG, PDF (Maks. 3MB)</small>',
                labelFileProcessing: 'Mengupload...',
                labelFileProcessingComplete: 'Upload selesai',
                labelFileProcessingAborted: 'Upload dibatalkan',
                labelFileProcessingError: 'Gagal upload',
                labelFileLoading: 'Memuat...',
                labelTapToCancel: 'Tap untuk batal',
                labelTapToRetry: 'Tap untuk coba lagi',
                labelTapToUndo: 'Tap untuk undo',
                labelButtonRemoveItem: 'Hapus',
                labelButtonAbortItemLoad: 'Batal',
                labelButtonRetryItemLoad: 'Coba lagi',
                labelButtonAbortItemProcessing: 'Batal',
                labelButtonUndoItemProcessing: 'Undo',
                labelButtonRetryItemProcessing: 'Coba lagi',
                labelButtonProcessItem: 'Upload',
                acceptedFileTypes: ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'application/pdf'],
                credits: false
            };

            if (uploadsProcessUrl && uploadsRevertUrl && uploadsLoadUrl) {
                pondOptions.server = {
                    process: {
                        url: uploadsProcessUrl,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    },
                    revert: {
                        url: uploadsRevertUrl,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    },
                    load: uploadsLoadUrl
                };
            }

            const pond = FilePond.create(inputElement, pondOptions);

            pond.on('addfile', function() {
                updateFileStatus();
            });
            pond.on('removefile', function() {
                updateFileStatus();
            });

            function updateFileStatus() {
                const fileCount = pond.getFiles().length;
                void fileCount;
            }
        }
    }

    // Service Routine Toggle
    $('#isServiceRoutineToggle').on('change', function() {
        $('#isServiceRoutineValue').val($(this).is(':checked') ? '0' : '1');
    });

    // Agree Checkbox enables submit buttons
    $('#agreeCheckbox').on('change', function() {
        updateSubmitState();
    });

    function isFieldFilled(field) {
        if (field.disabled) {
            return true;
        }

        if (field.type === 'checkbox' || field.type === 'radio') {
            return field.checked;
        }

        return field.value.trim() !== '';
    }

    function updateSubmitState() {
        const form = document.getElementById('mainForm');
        if (!form) {
            return;
        }

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach((field) => {
            if (field.id === 'date_out') {
                return;
            }

            if (!isFieldFilled(field)) {
                isValid = false;
            }
        });

        const agreeCheckbox = document.getElementById('agreeCheckbox');
        if (agreeCheckbox && !agreeCheckbox.checked) {
            isValid = false;
        }

        const $submitButton = $('#submitButton');
        const $saveButton = $('button[form=\"mainForm\"][type=\"submit\"]');
        const $draftButton = $('button[name=\"save_as_draft\"]');

        if ($submitButton.length) {
            $submitButton.prop('disabled', !isValid);
        }
        if ($saveButton.length) {
            $saveButton.prop('disabled', !isValid);
        }
        if ($draftButton.length) {
            $draftButton.prop('disabled', !isValid);
        }
    }

    // Auto-fill mileage from selected vehicle
    $('select[name="no_pol"]').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const mileage = selectedOption.data('mileage');
        if (mileage) {
            $('#mileage').val(mileage);
            $('#mileage-info').text('KM terakhir tercatat: ' + mileage);
        }
    });

    // Delete note file
    $('button[data-id]').on('click', function() {
        const noteId = $(this).data('id');
        const row = $(this).closest('.d-flex');

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
            if (result.isConfirmed) {
                $.ajax({
                    url: '/notes/' + noteId,
                    type: 'DELETE',
                    data: {
                        _token: csrfToken
                    },
                    success: function() {
                        row.remove();
                        Swal.fire('Terhapus!', 'File berhasil dihapus.', 'success');
                    },
                    error: function() {
                        Swal.fire('Error!', 'Gagal menghapus file.', 'error');
                    }
                });
            }
        });
    });

    // Cancel maintenance confirmation
    $('.confrmCancel').on('click', function() {
        const maintenanceId = $(this).data('id');

        Swal.fire({
            title: 'Batalkan perbaikan?',
            text: 'Status akan diubah menjadi DIBATALKAN',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, batalkan',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form method="POST" action="/maintenances/' + maintenanceId + '/status"></form>');
                form.append($('<input>', { type: 'hidden', name: '_token', value: csrfToken }));
                form.append('<input type="hidden" name="status" value="CANCELLED">');
                $('body').append(form);
                form.submit();
            }
        });
    });

    // ========================================
    // REPEATER FUNCTIONALITY
    // ========================================
    let repeaterIndex = 0;

    // Add new repeater item
    $('#addRepeaterItem').on('click', function() {
        const template = $('#repeaterTemplate').html();
        const newItem = template.replace(/__INDEX__/g, repeaterIndex).trim();
        const $newItem = $(newItem);
        $('#repeaterItems').append($newItem);
        $newItem.find('.select2').select2({
            width: '100%'
        });
        repeaterIndex++;
        updateTotalDisplay();
    });

    // Remove repeater item
    $(document).on('click', '.remove-repeater-item', function() {
        $(this).closest('.repeater-item').remove();
        updateTotalDisplay();
    });

    // Calculate subtotal when price or qty changes
    $(document).on('input', '.repeater-price, .repeater-qty', function() {
        const item = $(this).closest('.repeater-item');
        const price = parseInt(item.find('.repeater-price').val().replace(/[^0-9]/g, ''), 10) || 0;
        const qty = parseInt(item.find('.repeater-qty').val(), 10) || 1;
        const subtotal = price * qty;
        item.find('.repeater-subtotal').text('Rp ' + formatNumber(subtotal));
        updateTotalDisplay();
    });

    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Update total display (sum of new items)
    function updateTotalDisplay() {
        let total = 0;
        $('.repeater-item').each(function() {
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
        $('#total_amount').val(total);
    }

    // Number only input for repeater price fields
    $(document).on('input', '.repeater-price', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Delete existing item
    $(document).on('click', '.delete-existing-item', function() {
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
            if (result.isConfirmed) {
                $.ajax({
                    url: href,
                    type: 'DELETE',
                    data: {
                        _token: csrfToken
                    },
                    success: function() {
                        itemDiv.fadeOut(300, function() {
                            $(this).remove();
                            updateTotalDisplay();
                        });
                        Swal.fire('Terhapus!', 'Item berhasil dihapus.', 'success');
                    },
                    error: function() {
                        Swal.fire('Error!', 'Gagal menghapus item.', 'error');
                    }
                });
            }
        });
    });

    $(document).on('input change', '#mainForm input, #mainForm select, #mainForm textarea', function() {
        updateSubmitState();
    });

    $(document).on('change', '.select2', function() {
        updateSubmitState();
    });

    updateSubmitState();
    updateTotalDisplay();
});
