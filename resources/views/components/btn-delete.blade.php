@props(['route' => '#', 'confirmMessage' => '정말 삭제하시겠습니까?'])

<form action="{{ $route }}" method="POST"
      onsubmit="return confirm('{{ $confirmMessage }}')"
      class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">
        <i class="bi bi-trash me-1"></i> {{ $slot }}
    </button>
</form>