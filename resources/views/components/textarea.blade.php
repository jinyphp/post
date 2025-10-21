@props([
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'rows' => 3,
    'required' => false,
    'disabled' => false,
    'readonly' => false
])

@php
    $id = $id ?? $name;
    $classes = 'form-control';
    if ($errors->has($name)) {
        $classes .= ' is-invalid';
    }
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        class="{{ $classes }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        {{ $attributes }}
    >{{ old($name, $value) }}</textarea>

    @if($slot->isNotEmpty())
        <div class="form-text">{{ $slot }}</div>
    @endif

    @error($name)
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>