@props([
    'title' => '',
    'icon' => 'bi-lightbulb',
    'iconColor' => 'text-warning'
])

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-secondary-subtle border-bottom">
        <h6 class="mb-0">
            <i class="{{ $icon }} me-2 {{ $iconColor }}"></i>
            {{ $title }}
        </h6>
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>