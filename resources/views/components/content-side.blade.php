@props([
    'col' => 'col-lg-4'
])

<div class="{{ $col }}">
    <div class="sticky-top" style="top: 2rem;">
        {{ $slot }}
    </div>
</div>