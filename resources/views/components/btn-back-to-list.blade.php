@props(['route' => '#'])

<a href="{{ $route }}" class="btn btn-secondary">
    <i class="bi bi-list me-1"></i> {{ $slot }}
</a>