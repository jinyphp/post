@props([
    'name',
    'id' => null,
    'type' => 'text',
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'min' => null,
    'max' => null,
    'step' => null
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

    <input
        type="{{ $type }}"
        id="{{ $id }}"
        name="{{ $name }}"
        class="{{ $classes }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        @if($step !== null) step="{{ $step }}" @endif
        {{ $attributes }}
    >

    @if($slot->isNotEmpty())
        <div class="form-text">{{ $slot }}</div>
    @endif

    @error($name)
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>