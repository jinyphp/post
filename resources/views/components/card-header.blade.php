@props([
    'bg' => 'bg-white',
    'border' => 'border-bottom',
    'icon' => '',
    'iconColor' => 'text-primary',
    'title' => ''
])

<div class="card-header {{ $bg }} {{ $border }}" {{ $attributes }}>
    @if($title)
        <h5 class="mb-0">
            @if($icon)
                <i class="{{ $icon }} me-2 {{ $iconColor }}"></i>
            @endif
            {{ $title }}
        </h5>
    @else
        {{ $slot }}
    @endif
</div>