@props([
    'type' => 'submit',
    'icon' => 'bi-check-circle',
    'size' => '',
    'disabled' => false
])

<div class="text-end mb-4">
    <button
        type="{{ $type }}"
        class="btn btn-primary{{ $size ? ' ' . $size : '' }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
        @if($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </button>
</div>