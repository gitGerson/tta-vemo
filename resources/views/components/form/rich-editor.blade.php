@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'help' => null,
    'placeholder' => null,
    'id' => null,
    'inputClass' => null,
    'groupClass' => null,
    'old' => true,
    'minHeight' => '180px',
    'imageUploadUrl' => null,
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $resolvedValue = $old ? old($name, $value) : $value;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
@endphp

<x-form.group :name="$name" :label="$label" :required="$required" :help="$help" :for="$fieldId" :class="$groupClass">
    <div class="form-rich-editor {{ $isInvalid ? 'is-invalid' : '' }} {{ $inputClass }}"
        data-form-rich-editor="1"
        data-placeholder="{{ $placeholder ?? '' }}"
        data-min-height="{{ $minHeight }}"
        data-image-upload-url="{{ $imageUploadUrl ?? '' }}"
    >
        <div class="form-rich-editor__toolbar" role="toolbar" aria-label="Rich text toolbar">
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle form-rich-editor__heading-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Headings">
                    <span class="form-rich-editor__heading-label">H</span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button type="button" class="dropdown-item" data-editor-action="paragraph">
                            <span class="form-rich-editor__heading-option">P</span>
                            <span>Paragraph</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-editor-action="heading1">
                            <span class="form-rich-editor__heading-option">H1</span>
                            <span>Heading 1</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-editor-action="heading2">
                            <span class="form-rich-editor__heading-option">H2</span>
                            <span>Heading 2</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-editor-action="heading3">
                            <span class="form-rich-editor__heading-option">H3</span>
                            <span>Heading 3</span>
                        </button>
                    </li>
                </ul>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="bold" title="Bold">
                <i class="bx bx-bold"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="italic" title="Italic">
                <i class="bx bx-italic"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="underline" title="Underline">
                <i class="bx bx-underline"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="strike" title="Strike">
                <i class="bx bx-strikethrough"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="bulletList" title="Bullet list">
                <i class="bx bx-list-ul"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="orderedList" title="Ordered list">
                <i class="bx bx-list-ol"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="alignLeft" title="Align left">
                <i class="bx bx-align-left"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="alignCenter" title="Align center">
                <i class="bx bx-align-middle"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="alignRight" title="Align right">
                <i class="bx bx-align-right"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="alignJustify" title="Justify">
                <i class="bx bx-align-justify"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="blockquote" title="Quote">
                <i class="bx bx-quote-left"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="codeBlock" title="Code block">
                <i class="bx bx-code-block"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="horizontalRule" title="Horizontal rule">
                <i class="bx bx-minus"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="link" title="Link">
                <i class="bx bx-link"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="image" title="Attach image">
                <i class="bx bx-image-add"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="undo" title="Undo">
                <i class="bx bx-undo"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-editor-action="redo" title="Redo">
                <i class="bx bx-redo"></i>
            </button>
        </div>

        <div id="{{ $fieldId }}" class="form-rich-editor__content" data-rich-editor-content></div>
        <input type="hidden" name="{{ $name }}" value="{{ $resolvedValue }}" data-rich-editor-input>
        <input type="file" accept="image/*" class="d-none" data-rich-editor-image-input>
    </div>
</x-form.group>

@once
    @push('styles')
        <style>
            .form-rich-editor {
                border: 1px solid var(--bs-border-color, #e5e7eb);
                border-radius: 0.75rem;
                background-color: var(--bs-body-bg, #fff);
                overflow: hidden;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            }

            .form-rich-editor.is-invalid {
                border-color: var(--bs-danger, #dc3545);
            }

            .form-rich-editor__toolbar {
                padding: 0.35rem 0.4rem;
                border-bottom: 1px solid var(--bs-border-color, #eceff3);
                background-color: color-mix(in srgb, var(--bs-body-bg, #fff) 96%, #f8fafc 4%);
                display: flex;
                flex-wrap: wrap;
                gap: 0.1rem;
            }

            .form-rich-editor__toolbar .btn {
                border: 1px solid transparent;
                border-radius: 0.45rem;
                background: transparent;
                color: var(--bs-body-color, #374151);
                line-height: 1;
                min-width: 1.75rem;
                min-height: 1.75rem;
                padding: 0.25rem 0.35rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: background-color 0.16s ease, border-color 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
            }

            .form-rich-editor__heading-toggle {
                min-width: 2.25rem;
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .form-rich-editor__heading-label {
                font-size: 0.82rem;
            }

            .form-rich-editor__toolbar .btn:hover {
                background-color: color-mix(in srgb, var(--bs-body-bg, #fff) 90%, #e2e8f0 10%);
                border-color: color-mix(in srgb, var(--bs-border-color, #e5e7eb) 85%, #cbd5e1 15%);
            }

            .form-rich-editor__toolbar .btn:focus-visible {
                outline: 0;
                box-shadow: 0 0 0 0.18rem color-mix(in srgb, var(--bs-primary, #6366f1) 22%, transparent);
            }

            .form-rich-editor__toolbar .btn.is-active {
                background-color: color-mix(in srgb, var(--bs-primary, #6366f1) 14%, var(--bs-body-bg, #fff) 86%);
                border-color: color-mix(in srgb, var(--bs-primary, #6366f1) 40%, var(--bs-border-color, #e5e7eb) 60%);
                color: color-mix(in srgb, var(--bs-primary, #6366f1) 85%, #1f2937 15%);
            }

            .form-rich-editor__toolbar .dropdown-menu {
                min-width: 10rem;
            }

            .form-rich-editor__toolbar .dropdown-item {
                display: flex;
                align-items: center;
                gap: 0.55rem;
            }

            .form-rich-editor__toolbar .dropdown-item.is-active {
                background-color: color-mix(in srgb, var(--bs-primary, #6366f1) 14%, var(--bs-body-bg, #fff) 86%);
                color: color-mix(in srgb, var(--bs-primary, #6366f1) 85%, #1f2937 15%);
            }

            .form-rich-editor__heading-option {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 2rem;
                padding: 0.1rem 0.35rem;
                border-radius: 0.35rem;
                font-size: 0.72rem;
                font-weight: 700;
                background-color: color-mix(in srgb, var(--bs-body-bg, #fff) 88%, #e2e8f0 12%);
                color: var(--bs-body-color, #334155);
            }

            .form-rich-editor__content {
                min-height: 180px;
                padding: 0.9rem 1rem;
                outline: none;
                color: var(--bs-body-color, #334155);
                font-size: 0.95rem;
                line-height: 1.65;
                background-color: var(--bs-body-bg, #fff);
            }

            .form-rich-editor__content .ProseMirror {
                min-height: inherit;
                outline: none;
            }

            .form-rich-editor__content .ProseMirror h2,
            .form-rich-editor__content .ProseMirror h3 {
                line-height: 1.35;
                color: color-mix(in srgb, var(--bs-body-color, #334155) 92%, #111827 8%);
                margin-top: 0.85rem;
                margin-bottom: 0.5rem;
            }

            .form-rich-editor__content .ProseMirror h2 {
                font-size: 1.2rem;
            }

            .form-rich-editor__content .ProseMirror h3 {
                font-size: 1.05rem;
            }

            .form-rich-editor__content .ProseMirror p {
                margin-bottom: 0.45rem;
            }

            .form-rich-editor__content .ProseMirror p.is-editor-empty:first-child::before {
                content: attr(data-placeholder);
                color: var(--bs-secondary-color, #8b99a8);
                float: left;
                height: 0;
                pointer-events: none;
            }

            .form-rich-editor__content .ProseMirror ul,
            .form-rich-editor__content .ProseMirror ol {
                padding-left: 1.25rem;
                margin-bottom: 0.5rem;
            }

            .form-rich-editor__content .ProseMirror blockquote {
                border-left: 3px solid color-mix(in srgb, var(--bs-primary, #6366f1) 40%, transparent);
                margin: 0.6rem 0;
                padding: 0.3rem 0 0.3rem 0.75rem;
                color: color-mix(in srgb, var(--bs-body-color, #334155) 70%, #64748b 30%);
            }

            .form-rich-editor__content .ProseMirror pre {
                background-color: color-mix(in srgb, var(--bs-dark, #0f172a) 90%, transparent);
                color: #e2e8f0;
                border-radius: 0.55rem;
                padding: 0.65rem 0.75rem;
                font-size: 0.84rem;
                overflow-x: auto;
                margin: 0.6rem 0;
            }

            .form-rich-editor__content .ProseMirror img {
                max-width: 100%;
                height: auto;
                border-radius: 0.5rem;
                margin: 0.45rem 0;
                border: 1px solid color-mix(in srgb, var(--bs-border-color, #e5e7eb) 88%, transparent);
            }

            [data-bs-theme="dark"] .form-rich-editor {
                border-color: color-mix(in srgb, var(--bs-border-color, #374151) 75%, #475569 25%);
                box-shadow: none;
            }

            [data-bs-theme="dark"] .form-rich-editor__toolbar .btn:hover {
                background-color: color-mix(in srgb, var(--bs-body-bg, #111827) 84%, #334155 16%);
                border-color: color-mix(in srgb, var(--bs-border-color, #374151) 75%, #64748b 25%);
            }
        </style>
    @endpush

    @push('scripts')
        @vite(['resources/js/modules/rich-editor.js'])
    @endpush
@endonce
