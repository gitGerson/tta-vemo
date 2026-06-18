@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 3,
    'required' => false,
    'help' => null,
    'placeholder' => null,
    'id' => null,
    'inputClass' => null,
    'groupClass' => null,
    'old' => true,
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $resolvedValue = $old ? old($name, $value) : $value;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
@endphp

<x-form.group :name="$name" :label="$label" :required="$required" :help="$help" :for="$fieldId" :class="$groupClass">
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        {{ $attributes->class(['form-control', $inputClass, 'is-invalid' => $isInvalid]) }}
    >{{ $resolvedValue }}</textarea>
</x-form.group>
