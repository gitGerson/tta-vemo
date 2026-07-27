<style>
    .btn-icon {
        padding: 0.4rem;
        line-height: 1;
    }

    .detail-perbaikan-table {
        border: 1px solid var(--bs-border-color);
        border-radius: 0.6rem;
        overflow: hidden;
    }

    .detail-perbaikan-header {
        background: var(--bs-secondary-bg);
        padding: 0.6rem 0.9rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
    }

    .detail-perbaikan-item {
        padding: 0.55rem 0.9rem;
        border-top: 1px solid var(--bs-border-color);
    }

    .detail-perbaikan-item .form-select,
    .detail-perbaikan-item .form-control {
        color: var(--bs-body-color);
        background-color: var(--bs-paper-bg, var(--bs-body-bg));
        border-color: var(--bs-border-color);
        border-radius: 0.35rem;
        min-height: calc(1.5em + 0.5rem + 2px);
        font-size: 0.875rem;
    }

    .detail-perbaikan-item .input-group-text {
        color: var(--bs-body-color);
        background: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
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
        background: var(--bs-secondary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.6rem;
        padding: 0.85rem 1rem;
    }

    .detail-servis-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
    }

    .detail-servis-textarea {
        min-height: 120px;
    }

    .card-header-compact {
        color: var(--bs-body-color);
        background: var(--bs-secondary-bg);
        border-bottom: 1px solid var(--bs-border-color);
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
        color: var(--bs-secondary-color);
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
        background-color: var(--bs-secondary-bg);
        border: 1px dashed var(--bs-border-color);
    }

    .filepond--drop-label,
    .filepond--drop-label label {
        color: var(--bs-secondary-color);
    }

    .filepond--label-action {
        text-decoration: underline;
        color: var(--bs-primary);
    }

    .filepond--item-panel {
        background-color: var(--bs-primary);
    }

    .filepond--drip-blob {
        background-color: var(--bs-primary);
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

    html[data-bs-theme='dark'] #maintenanceInfoBanner {
        --bs-alert-color: var(--bs-body-color);
        --bs-alert-bg: var(--bs-secondary-bg);
        --bs-alert-border-color: var(--bs-border-color);
    }

    html[data-bs-theme='dark'] .card-header-compact,
    html[data-bs-theme='dark'] .detail-servis-panel,
    html[data-bs-theme='dark'] .detail-perbaikan-header,
    html[data-bs-theme='dark'] .filepond--panel-root {
        color: var(--bs-body-color);
        background-color: var(--bs-secondary-bg) !important;
        border-color: var(--bs-border-color);
    }

    html[data-bs-theme='dark'] .filepond--drop-label,
    html[data-bs-theme='dark'] .filepond--drop-label label {
        color: var(--bs-secondary-color);
    }

    html[data-bs-theme='dark'] .form-switch .form-check-input:not(:checked) {
        background-color: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
    }
</style>
