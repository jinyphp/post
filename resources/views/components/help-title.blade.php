@props([
    'icon' => '',
    'iconColor' => 'text-primary',
    'marginBottom' => 'mb-3'
])

<div class="{{ $marginBottom }}">
    <h6 class="{{ $iconColor }} small">
        @if($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </h6>
</div>