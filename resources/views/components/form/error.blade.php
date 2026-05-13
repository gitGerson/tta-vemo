@props([
    'name',
])

@error($name)
    <div {{ $attributes->class(['invalid-feedback d-block']) }}>
        {{ $message }}
    </div>
@enderror
