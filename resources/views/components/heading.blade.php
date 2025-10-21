@props(['title' => '', 'subtitle' => ''])

<div class="d-flex justify-content-between align-items-center my-3">
    <div>
        <h3>{{ $title }}</h3>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    <div>
        {{ $slot }}
    </div>
</div>