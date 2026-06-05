<style>
    .select2-container--default .select2-selection--single {
        height: 40px;
        display: flex;
        align-items: center;
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

    .filepond--root {
        font-family: inherit;
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
