@props([
    'col' => 'col-lg-4'
])

<div class="{{ $col }}">
    {{ $slot }}
</div>