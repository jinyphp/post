@props([
    'padding' => ''
])

<div class="card-body{{ $padding ? ' ' . $padding : '' }}" {{ $attributes }}>
    {{ $slot }}
</div>