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
    'mode' => 'native',
    'single' => true,
    'format' => null,
    'minDate' => null,
    'maxDate' => null,
    'opens' => 'right',
    'drops' => 'auto',
    'autoApply' => null,
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $resolvedValue = $old ? old($name, $value) : $value;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
    $useDateRangePicker = $mode === 'daterangepicker';
    $resolvedFormat = $format ?? ($single ? 'YYYY-MM-DD' : 'YYYY-MM-DD');
    $resolvedAutoApply = $autoApply ?? ! $single;
@endphp

<x-form.group :name="$name" :label="$label" :required="$required" :help="$help" :for="$fieldId" :class="$groupClass">
    <input
        type="{{ $useDateRangePicker ? 'text' : 'date' }}"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if (! $useDateRangePicker && $minDate) min="{{ $minDate }}" @endif
        @if (! $useDateRangePicker && $maxDate) max="{{ $maxDate }}" @endif
        @if ($useDateRangePicker) data-date-mode="daterangepicker" @endif
        @if ($useDateRangePicker) data-single-date-picker="{{ $single ? '1' : '0' }}" @endif
        @if ($useDateRangePicker) data-date-format="{{ $resolvedFormat }}" @endif
        @if ($useDateRangePicker && $minDate) data-min-date="{{ $minDate }}" @endif
        @if ($useDateRangePicker && $maxDate) data-max-date="{{ $maxDate }}" @endif
        @if ($useDateRangePicker) data-opens="{{ $opens }}" @endif
        @if ($useDateRangePicker) data-drops="{{ $drops }}" @endif
        @if ($useDateRangePicker) data-auto-apply="{{ $resolvedAutoApply ? '1' : '0' }}" @endif
        value="{{ $resolvedValue }}"
        {{ $attributes->class(['form-control', $inputClass, 'is-invalid' => $isInvalid]) }}
    >
</x-form.group>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
        <style>
            .daterangepicker {
                border: 1px solid var(--bs-border-color, #d9dee3);
                box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
                z-index: 1080;
            }

            .daterangepicker .ranges li.active {
                background-color: var(--bs-primary, #696cff);
            }

            .daterangepicker td.active,
            .daterangepicker td.active:hover {
                background-color: var(--bs-primary, #696cff);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        @vite(['resources/js/modules/date-range-picker.js'])
    @endpush
@endonce
