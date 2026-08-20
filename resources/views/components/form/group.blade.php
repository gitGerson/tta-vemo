@props([
    'name',
    'label' => null,
    'required' => false,
    'help' => null,
    'for' => null,
    'class' => null,
    'wrapperClass' => null,
])

@php
    $fieldId = $for ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
@endphp

<div {{ $attributes->class(['mb-3', $class, $wrapperClass]) }}>
    @if ($label)
        <label for="{{ $fieldId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    <x-form.error :name="$name" />

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
