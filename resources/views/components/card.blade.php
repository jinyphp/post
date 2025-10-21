@props([
    'shadow' => 'shadow-sm',
    'border' => 'border-0',
    'margin' => 'mb-4'
])

@once
    @push('styles')
    <style>
    .card {
        transition: all 0.15s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    </style>
    @endpush
@endonce

<div class="card {{ $border }} {{ $shadow }} {{ $margin }}" {{ $attributes }}>
    {{ $slot }}
</div>