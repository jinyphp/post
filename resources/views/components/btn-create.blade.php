@props(['route' => '#'])

<a href="{{ $route }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> {{ $slot }}
</a>