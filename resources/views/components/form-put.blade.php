@props([
    'action' => '',
    'enctype' => null
])

<form
    method="POST"
    action="{{ $action }}"
    @if($enctype) enctype="{{ $enctype }}" @endif
    {{ $attributes }}
>
    @csrf
    @method('PUT')

    {{ $slot }}
</form>