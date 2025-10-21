@props([
    'col' => 'col-lg-8'
])

<div class="{{ $col }}">
    {{ $slot }}
</div>