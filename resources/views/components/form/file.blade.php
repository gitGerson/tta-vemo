@props([
    'name',
    'label' => null,
    'required' => false,
    'help' => null,
    'id' => null,
    'mode' => 'native',
    'multiple' => false,
    'accept' => null,
    'maxFileSize' => null,
    'acceptedFileTypes' => null,
    'filepondProcessUrl' => null,
    'filepondRevertUrl' => null,
    'filepondLoadUrl' => null,
    'existingFiles' => [],
    'locale' => 'id',
    'inputClass' => null,
    'groupClass' => null,
])

@php
    $defaultProcessUrl = \Illuminate\Support\Facades\Route::has('uploads.process') ? route('uploads.process') : null;
    $defaultRevertUrl = \Illuminate\Support\Facades\Route::has('uploads.revert') ? route('uploads.revert') : null;
    $defaultLoadUrl = \Illuminate\Support\Facades\Route::has('uploads.load') ? route('uploads.load', ['source' => '']) : null;
    $resolvedProcessUrl = $filepondProcessUrl ?: $defaultProcessUrl;
    $resolvedRevertUrl = $filepondRevertUrl ?: $defaultRevertUrl;
    $resolvedLoadUrl = $filepondLoadUrl ?: $defaultLoadUrl;
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $baseFieldName = preg_replace('/\[\]$/', '', $name);
    $inputName = $multiple ? ($baseFieldName.'[]') : $baseFieldName;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($baseFieldName) || $errorBag->has($baseFieldName.'.*');
    $acceptedTypesJson = json_encode($acceptedFileTypes ?? []);
    $existingFilesJson = json_encode($existingFiles ?? []);
    $useFilePond = $mode === 'filepond' && filled($resolvedProcessUrl) && filled($resolvedRevertUrl);
@endphp

<x-form.group :name="$baseFieldName" :label="$label" :required="$required" :help="$help" :for="$fieldId" :class="$groupClass">
    <input
        type="file"
        id="{{ $fieldId }}"
        name="{{ $inputName }}"
        @if ($multiple) multiple @endif
        @if ($accept) accept="{{ $accept }}" @endif
        @if ($required) required @endif
        @if ($useFilePond) data-filepond-enabled="1" @endif
        @if ($useFilePond) data-filepond-process-url="{{ $resolvedProcessUrl }}" @endif
        @if ($useFilePond) data-filepond-revert-url="{{ $resolvedRevertUrl }}" @endif
        @if ($useFilePond && $resolvedLoadUrl) data-filepond-load-url="{{ $resolvedLoadUrl }}" @endif
        @if ($useFilePond && $maxFileSize) data-filepond-max-file-size="{{ $maxFileSize }}" @endif
        @if ($useFilePond) data-filepond-locale="{{ $locale }}" @endif
        @if ($useFilePond) data-filepond-accepted-file-types='{{ $acceptedTypesJson }}' @endif
        @if ($useFilePond) data-filepond-existing-files='{{ $existingFilesJson }}' @endif
        {{ $attributes->class([
            $useFilePond ? 'form-control js-filepond filepond' : 'form-control',
            $inputClass,
            'is-invalid' => $isInvalid,
        ]) }}
    >
</x-form.group>

@if ($useFilePond)
    @once
        @push('styles')
            <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
            <link href="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css" rel="stylesheet" />
            <style>
                .filepond--root {
                    margin-bottom: 0;
                }

                .filepond--panel-root {
                    border-radius: 0.375rem;
                    background-color: var(--bs-tertiary-bg, #f8f9fa);
                    border: 1px dashed var(--bs-border-color, #d9dee3);
                    box-shadow: none !important;
                    outline: none !important;
                    transition: none !important;
                }

                .filepond--root:focus,
                .filepond--root:focus-within,
                .filepond--root *:focus,
                .filepond--browser:focus + .filepond--drop-label,
                .filepond--root[data-focus="true"] .filepond--panel-root,
                .filepond--root[data-focus="true"] .filepond--drop-label,
                .filepond--root.filepond--hopper[data-hopper-state],
                .filepond--root.filepond--hopper[data-hopper-state] .filepond--panel-root,
                .filepond--root.filepond--hopper[data-hopper-state] .filepond--drop-label {
                    outline: none !important;
                    box-shadow: none !important;
                    border-color: var(--bs-border-color, #d9dee3) !important;
                    background-color: var(--bs-tertiary-bg, #f8f9fa) !important;
                }

                .filepond--drop-label {
                    color: var(--bs-secondary-color, #6c757d);
                }

                .filepond--drop-label label {
                    color: inherit;
                }

                .filepond--label-action {
                    color: var(--bs-primary, #696cff);
                }

                .filepond--drip {
                    background: var(--bs-primary, #696cff);
                    opacity: 0.1;
                }

                .filepond--item-panel {
                    background-color: var(--bs-primary, #696cff);
                }

                .filepond--file {
                    color: #fff;
                }

                .filepond--credits {
                    display: none !important;
                }

                [data-bs-theme="dark"] .filepond--panel-root,
                .theme-dark .filepond--panel-root {
                    background-color: #2b3545;
                    border-color: #44546a;
                }

                [data-bs-theme="dark"] .filepond--drop-label,
                .theme-dark .filepond--drop-label {
                    color: #b7c0cd;
                }

                [data-bs-theme="dark"] .filepond--drop-label .filepond--label-action,
                .theme-dark .filepond--drop-label .filepond--label-action {
                    color: #8ea2ff;
                }

                [data-bs-theme="dark"] .filepond--image-preview-overlay-idle,
                .theme-dark .filepond--image-preview-overlay-idle {
                    opacity: 0.35;
                }

                [data-bs-theme="dark"] .filepond--file-info-sub,
                [data-bs-theme="dark"] .filepond--file-status-sub,
                .theme-dark .filepond--file-info-sub,
                .theme-dark .filepond--file-status-sub {
                    opacity: 0.9;
                }
            </style>
        @endpush

        @push('scripts')
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
            <script src="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.js"></script>
            <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
            @vite(['resources/js/modules/filepond.js'])
        @endpush
    @endonce
@endif
