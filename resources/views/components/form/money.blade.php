@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'help' => null,
    'id' => null,
    'prefix' => 'Rp',
    'inputClass' => null,
    'groupClass' => null,
    'old' => true,
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $resolvedValue = $old ? old($name, $value) : $value;
    $normalizedValue = is_string($resolvedValue) ? preg_replace('/[^\d.]/', '', $resolvedValue) : $resolvedValue;
    $displayValue = is_numeric($normalizedValue) ? number_format((float) $normalizedValue, 0, '.', ',') : $resolvedValue;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
@endphp

<div {{ $attributes->only('class')->class(['mb-3', $groupClass]) }}>
    @if ($label)
        <label for="{{ $fieldId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="input-group input-group-sm has-validation">
        <span class="input-group-text">{{ $prefix }}</span>
        <input
            type="text"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $displayValue }}"
            inputmode="decimal"
            @if ($required) required @endif
            data-rupiah-input
            {{ $attributes->except('class')->class(['form-control', $inputClass, 'is-invalid' => $isInvalid]) }}
        >
        @if ($isInvalid)
            <div class="invalid-feedback">{{ $errorBag->first($name) }}</div>
        @endif
    </div>

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>

@once
    @push('scripts')
        @vite(['resources/js/modules/rupiah-input.js'])
    @endpush
@endonce
