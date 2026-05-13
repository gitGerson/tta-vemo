@props([
    'name',
    'label' => null,
    'value' => 1,
    'checked' => null,
    'help' => null,
    'id' => null,
    'inputClass' => null,
    'groupClass' => null,
    'old' => true,
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
    $resolvedChecked = $old ? old($name, $checked) : $checked;
@endphp

<div class="{{ $groupClass }}">
    <div class="form-check mb-2">
        <input
            type="checkbox"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked((bool) $resolvedChecked)
            {{ $attributes->class(['form-check-input', $inputClass, 'is-invalid' => $isInvalid]) }}
        >
        @if ($label)
            <label for="{{ $fieldId }}" class="form-check-label">{{ $label }}</label>
        @endif
    </div>

    <x-form.error :name="$name" />

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
