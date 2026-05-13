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
])

@php
    $fieldId = $id ?: preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $name);
    $selectedValue = $old ? old($name, $value) : $value;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $isInvalid = $errorBag->has($name);
    $hasCustomOptions = trim((string) $slot) !== '';
    $optionsAreList = is_array($options) && array_is_list($options);
@endphp

<x-form.group :name="$name" :label="$label" :required="$required" :help="$help" :for="$fieldId" :class="$groupClass">
    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @if ($required) required @endif
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

                <option value="{{ $optionRawValue }}" @selected((string) $selectedValue === (string) $optionRawValue)>
                    {{ $optionText }}
                </option>
            @endforeach
        @endif
    </select>
</x-form.group>
