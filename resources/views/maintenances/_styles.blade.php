<style>
    /* Render the CDN select2 widgets at the small (form-select-sm) size. */
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px);
        min-height: calc(1.5em + 0.5rem + 2px);
        display: flex;
        align-items: center;
        font-size: 0.8125rem;
        border-color: #dee2e6;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.3;
        padding-left: 0.5rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.5rem);
    }

    .select2-container--default .select2-results__option {
        font-size: 0.8125rem;
    }

    .btn-icon {
        padding: 0.4rem;
        line-height: 1;
    }

    .detail-perbaikan-table {
        border: 1px solid #e9ecef;
        border-radius: 0.6rem;
        overflow: hidden;
    }

    .detail-perbaikan-header {
        background: #f3f5f7;
        padding: 0.6rem 0.9rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6c757d;
    }

    .detail-perbaikan-item {
        padding: 0.55rem 0.9rem;
        border-top: 1px solid #f1f3f5;
    }

    .detail-perbaikan-item .form-select,
    .detail-perbaikan-item .form-control {
        background-color: #fff;
        border-radius: 0.35rem;
        min-height: calc(1.5em + 0.5rem + 2px);
        font-size: 0.875rem;
    }

    .detail-perbaikan-item .input-group-text {
        background: #f8f9fa;
        font-size: 0.8rem;
    }

    .detail-perbaikan-item .select2-container {
        width: 100% !important;
    }

    @media (max-width: 768px) {
        .detail-perbaikan-header {
            display: none;
        }

        .detail-perbaikan-item {
            padding: 0.75rem 0.9rem;
        }

        .detail-perbaikan-item .row>[class*='col-md-'] {
            width: 100%;
        }
    }

    .detail-servis-panel {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.6rem;
        padding: 0.85rem 1rem;
    }

    .detail-servis-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6c757d;
    }

    .detail-servis-textarea {
        min-height: 120px;
    }

    .card-header-compact {
        background: #f8f9fa;
        border-bottom: 1px solid #eef1f4;
        padding: 0.9rem 0.9rem;
        margin-bottom: 1rem;
    }

    .info-kendaraan-title {
        font-size: 0.95rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .info-kendaraan-label {
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    /* Smaller agree checkbox to match its small label. */
    #agreeCheckbox {
        width: 0.95em;
        height: 0.95em;
        margin-top: 0.2em;
    }

    .filepond--root {
        font-family: inherit;
    }

    /* Lay uploaded proofs out in a 2-column grid so many files don't stack tall. */
    .filepond--item {
        width: calc(50% - 0.5em);
    }

    @media (max-width: 575.98px) {
        .filepond--item {
            width: 100%;
        }
    }

    .filepond--panel-root {
        background-color: #f8f9fa;
        border: 1px dashed #dee2e6;
    }

    .filepond--label-action {
        text-decoration: underline;
        color: #696cff;
    }

    .filepond--item-panel {
        background-color: #696cff;
    }

    .filepond--drip-blob {
        background-color: #696cff;
    }

    .filepond--image-preview-wrapper {
        cursor: pointer;
    }

    .filepond-preview-image {
        max-height: 80vh;
        object-fit: contain;
        border-radius: 0.5rem;
        padding: 1rem;
    }

    .swal2-container {
        z-index: 9999 !important;
    }
</style>
