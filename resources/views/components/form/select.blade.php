@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'id' => null,
    'inputClass' => null,
    'groupClass' => null,
    'optionValue' => null,
    'optionLabel' => null,
    'old' => true,
    'mode' => 'native',
    'multiple' => false,
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $selectedValue = $old ? old($name, $value) : $value;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
    $hasCustomOptions = trim((string) $slot) !== '';
    $optionsAreList = is_array($options) && array_is_list($options);
    $useSelect2 = $mode === 'select2';
    $selectedValues = $multiple
        ? array_map(fn ($v) => (string) $v, (array) ($selectedValue ?? []))
        : [];
@endphp

<x-form.group :name="$name" :label="$label" :required="$required" :help="$help" :for="$fieldId" :class="$groupClass">
    <select
        id="{{ $fieldId }}"
        name="{{ $multiple ? $name . '[]' : $name }}"
        @if ($required) required @endif
        @if ($multiple) multiple @endif
        @if ($useSelect2)
            data-select2-enabled="1"
            data-select2-placeholder="{{ $placeholder }}"
            data-select2-allow-clear="{{ $placeholder !== null ? '1' : '0' }}"
        @endif
        {{ $attributes->class(['form-select', $inputClass, 'is-invalid' => $isInvalid]) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if ($hasCustomOptions)
            {{ $slot }}
        @else
            @foreach ($options as $key => $option)
                @php
                    $optionRawValue = $key;
                    $optionText = $option;

                    if ($optionsAreList && ! is_array($option) && ! is_object($option)) {
                        $optionRawValue = $option;
                        $optionText = $option;
                    }

                    if (is_array($option) || is_object($option)) {
                        $optionRawValue = $optionValue ? data_get($option, $optionValue) : data_get($option, 'id', $key);
                        $optionText = $optionLabel ? data_get($option, $optionLabel) : data_get($option, 'name', (string) $optionRawValue);
                    }
                @endphp

                <option value="{{ $optionRawValue }}" @selected($multiple ? in_array((string) $optionRawValue, $selectedValues, true) : (string) $selectedValue === (string) $optionRawValue)>
                    {{ $optionText }}
                </option>
            @endforeach
        @endif
    </select>
</x-form.group>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
        <style>
            .select2-container .select2-selection--single {
                height: calc(1.5em + 0.875rem + 2px);
                display: flex;
                align-items: center;
            }

            .select2-container--bootstrap-5 .select2-selection {
                border-color: var(--bs-border-color, #d9dee3);
            }

            .is-invalid + .select2-container--bootstrap-5 .select2-selection {
                border-color: var(--bs-danger, #dc3545);
            }

            .select2-container--bootstrap-5 .select2-dropdown {
                z-index: 1080;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        @vite(['resources/js/modules/select2.js'])
    @endpush
@endonce
