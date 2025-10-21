@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'min' => null,
    'max' => null,
    'step' => 1,
    'required' => false,
    'disabled' => false,
    'placeholder' => ''
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        <strong>{{ $label }}</strong>
    </label>
    <input
        type="number"
        class="form-control"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        step="{{ $step }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes }}
    >
    @if($slot->isNotEmpty())
        {{ $slot }}
    @endif
</div>