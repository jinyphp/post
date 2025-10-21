@props([
    'gap' => 'g-4'
])

<div class="row {{ $gap }}">
    {{ $slot }}
</div>