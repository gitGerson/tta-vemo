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
                color: var(--bs-body-color);
                background-color: var(--bs-paper-bg, var(--bs-body-bg));
                border: 1px solid var(--bs-border-color, #d9dee3);
                box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
                z-index: 1080;
            }

            .daterangepicker::before {
                border-bottom-color: var(--bs-border-color);
            }

            .daterangepicker::after {
                border-bottom-color: var(--bs-paper-bg, var(--bs-body-bg));
            }

            .daterangepicker.drop-up::before {
                border-top-color: var(--bs-border-color);
                border-bottom-color: transparent;
            }

            .daterangepicker.drop-up::after {
                border-top-color: var(--bs-paper-bg, var(--bs-body-bg));
                border-bottom-color: transparent;
            }

            .daterangepicker .calendar-table {
                color: var(--bs-body-color);
                background-color: var(--bs-paper-bg, var(--bs-body-bg));
                border-color: var(--bs-paper-bg, var(--bs-body-bg));
            }

            .daterangepicker th,
            .daterangepicker td {
                color: var(--bs-body-color);
            }

            .daterangepicker td.off,
            .daterangepicker td.off.in-range,
            .daterangepicker td.off.start-date,
            .daterangepicker td.off.end-date {
                color: var(--bs-secondary-color);
                background-color: var(--bs-paper-bg, var(--bs-body-bg));
            }

            .daterangepicker td.available:hover,
            .daterangepicker th.available:hover,
            .daterangepicker .ranges li:hover {
                color: var(--bs-body-color);
                background-color: var(--bs-secondary-bg);
            }

            .daterangepicker td.in-range {
                color: var(--bs-body-color);
                background-color: rgba(var(--bs-primary-rgb), 0.16);
            }

            .daterangepicker td.active,
            .daterangepicker td.active:hover {
                background-color: var(--bs-primary, #696cff);
                color: #fff;
            }

            .daterangepicker .ranges li.active {
                color: #fff;
                background-color: var(--bs-primary, #696cff);
            }

            .daterangepicker .calendar-table .next span,
            .daterangepicker .calendar-table .prev span {
                border-color: var(--bs-body-color);
            }

            .daterangepicker select.monthselect,
            .daterangepicker select.yearselect {
                color: var(--bs-body-color);
                background-color: var(--bs-paper-bg, var(--bs-body-bg));
                border: 1px solid var(--bs-border-color);
            }

            .daterangepicker .drp-buttons,
            .daterangepicker.show-ranges.ltr .drp-calendar.left {
                border-color: var(--bs-border-color);
            }

            .daterangepicker .drp-selected {
                color: var(--bs-secondary-color);
            }

            .daterangepicker .cancelBtn {
                color: var(--bs-body-color);
                background-color: var(--bs-secondary-bg);
                border-color: var(--bs-border-color);
            }

            .daterangepicker .applyBtn {
                color: #fff;
                background-color: var(--bs-primary);
                border-color: var(--bs-primary);
            }

            html[data-bs-theme='dark'] .daterangepicker,
            html[data-bs-theme='dark'] .daterangepicker .calendar-table,
            html[data-bs-theme='dark'] .daterangepicker td.off,
            html[data-bs-theme='dark'] .daterangepicker td.off.in-range,
            html[data-bs-theme='dark'] .daterangepicker td.off.start-date,
            html[data-bs-theme='dark'] .daterangepicker td.off.end-date {
                color: var(--bs-body-color);
                background-color: var(--bs-paper-bg, var(--bs-body-bg));
                border-color: var(--bs-border-color);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        @vite(['resources/js/modules/date-range-picker.js'])
    @endpush
@endonce
