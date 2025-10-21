@props([
    'name' => '',
    'checked' => false,
    'value' => '1',
    'hiddenValue' => '0'
])

@once
    @push('styles')
    <style>
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    </style>
    @endpush
@endonce

<div class="mb-3">
    <div class="form-check form-switch">
        <!-- Hidden input to ensure a value is always sent -->
        <input type="hidden" name="{{ $name }}" value="{{ $hiddenValue }}">
        <!-- Switch input -->
        <input
            class="form-check-input"
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $attributes }}
        >
        <label class="form-check-label" for="{{ $name }}">
            {{ $slot }}
        </label>
    </div>
</div>