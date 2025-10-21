@props([
    'dismissible' => true,
    'icon' => 'bi-check-circle'
])

@if(session('success') || !$slot->isEmpty())
<div class="alert alert-success{{ $dismissible ? ' alert-dismissible fade show' : '' }}" role="alert" {{ $attributes }}>
    @if($icon)
        <i class="{{ $icon }} me-2"></i>
    @endif

    @if($slot->isEmpty())
        {{ session('success') }}
    @else
        {{ $slot }}
    @endif

    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
@endif