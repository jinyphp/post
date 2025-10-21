@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 카테고리 관리')

@section('content')
<div class="container-fluid">

    <x-heading
        :title="$config['title'] ?? '블로그 카테고리'"
        :subtitle="$config['subtitle'] ?? ''">
        <x-btn-create :route="route('admin.cms.blog.category.create')">
            신규 등록
        </x-btn-create>
    </x-heading>


    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-tags me-2 text-primary"></i>
                        카테고리 목록
                    </h5>
                    <p class="text-muted mb-0 small">블로그 카테고리를 관리합니다.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- 검색 폼 -->
                    <form method="GET" class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="카테고리명으로 검색..."
                                   value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- 필터 드롭다운 -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i>필터
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?status=active">활성 카테고리</a></li>
                            <li><a class="dropdown-item" href="?status=inactive">비활성 카테고리</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.cms.blog.category') }}">전체 보기</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="categoryTable">
                    <thead class="table-light">
                        <tr>
                            <th width='50' class="text-center ps-4 text-nowrap">순서</th>
                            <th width='60' class="text-center text-nowrap">색상</th>
                            <th class="text-nowrap">카테고리명</th>
                            <th width='120' class="text-center text-nowrap">게시글 수</th>
                            <th width='100' class="text-center text-nowrap">상태</th>
                            <th width='180' class="text-nowrap">등록일자</th>
                            <th width='120' class="text-center pe-4 text-nowrap">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $item)
                            <tr data-id="{{ $item->id }}">
                                <td width='50' class="text-center ps-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="drag-handle me-1" style="cursor: move;">
                                            <i class="bi bi-grip-vertical text-muted"></i>
                                        </span>
                                        <span class="sort-order">{{ $item->sort_order }}</span>
                                    </div>
                                </td>
                                <td width='60' class="text-center">
                                    <span class="badge" style="background-color: {{ $item->color }}; color: white;">
                                        @if($item->icon)
                                            <i class="{{ $item->icon }}"></i>
                                        @else
                                            ●
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-dark">{{ $item->name }}</strong>
                                        <code class="text-muted ms-2">{{ $item->slug }}</code>
                                    </div>
                                    @if($item->description)
                                        <div class="text-muted small mt-1">
                                            {{ Str::limit($item->description, 80) }}
                                        </div>
                                    @endif
                                </td>
                                <td width='120' class="text-center">
                                    <span class="badge bg-secondary">{{ number_format($item->post_count) }}</span>
                                </td>
                                <td width='100' class="text-center">
                                    @if($item->is_active)
                                        <span class="badge bg-success">활성</span>
                                    @else
                                        <span class="badge bg-secondary">비활성</span>
                                    @endif
                                </td>
                                <td width='180'>
                                    <small class="text-muted">{{ $item->created_at }}</small>
                                    @if($item->created_by)
                                        <div class="text-muted small">{{ $item->created_by }}</div>
                                    @endif
                                </td>
                                <td width='120' class="text-center pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.cms.blog.category.edit', $item->id) }}"
                                           class="btn btn-outline-primary" title="수정">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.cms.blog.category.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('정말 삭제하시겠습니까?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="삭제">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-tags fs-1 d-block mb-2"></i>
                                    <p class="mb-0">등록된 카테고리가 없습니다.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($rows, 'links'))
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            총 {{ $rows->total() }}개의 카테고리가 있습니다.
                        </div>
                        <div>
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* 테이블 행 호버 효과 */
.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.02);
    transition: background-color 0.15s ease-in-out;
}

/* 카드 헤더 스타일 */
.card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

/* 검색 입력 필드 스타일 */
.input-group-sm .form-control {
    border-radius: 0.375rem 0 0 0.375rem;
}

.input-group-sm .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

/* 배지 스타일 개선 */
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

/* 액션 버튼 개선 */
.btn-group-sm .btn {
    padding: 0.375rem 0.5rem;
}

/* 드래그 핸들 스타일 */
.drag-handle {
    display: inline-block;
    margin-right: 0.5rem;
}

/* 드롭다운 메뉴 */
.dropdown-menu {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.375rem;
}

/* 페이지네이션 스타일 */
.card-footer {
    padding: 1rem 1.5rem;
}

/* 정렬 가능한 테이블 */
.sortable-placeholder {
    background-color: #f8f9fa !important;
    border: 2px dashed #dee2e6 !important;
}

.ui-sortable-helper {
    background-color: white;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 드래그 앤 드롭 기능은 SortableJS 같은 라이브러리나
    // 네이티브 HTML5 드래그 API를 사용할 수 있지만,
    // 간단히 order 번호 직접 수정 기능으로 대체합니다.

    const tbody = document.querySelector('#categoryTable tbody');
    if (!tbody) return;

    // 순서 변경 기능 (간단한 +/- 버튼으로 구현 가능)
    // 여기서는 기본적인 정렬 기능만 제공
    console.log('카테고리 테이블이 로드되었습니다.');
});
</script>
@endpush
@endsection