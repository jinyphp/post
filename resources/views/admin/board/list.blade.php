@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '게시판 목록')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3>{{ $config['title'] ?? '게시판 목록' }}</h3>
            <p class="text-muted mb-0">{{ $config['subtitle'] ?? '' }}</p>
        </div>
        <div>
            <a href="{{ route('admin.cms.board.list.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> 신규 등록
            </a>
        </div>
    </div>

    <hr>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>
                        게시판 목록
                    </h5>
                    <p class="text-muted mb-0 small">등록된 게시판을 관리합니다.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- 검색 폼 -->
                    <form method="GET" class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="게시판명, 코드로 검색..."
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
                            <li><a class="dropdown-item" href="?status=active">활성 게시판</a></li>
                            <li><a class="dropdown-item" href="?status=inactive">비활성 게시판</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.cms.board.list') }}">전체 보기</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width='200' class="ps-4">코드</th>
                            <th width='100' class="text-center">포스트</th>
                            <th width='120' class="text-center">조회수</th>
                            <th>타이틀</th>
                            <th width='200'>디자인</th>
                            <th width='200'>담당자</th>
                            <th width='200'>등록일자</th>
                            <th width='150' class="text-center pe-4">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $item)
                            <tr>
                                <td width='200' class="ps-4">
                                    <div class="d-flex gap-2 align-items-center">
                                        <div>
                                            <div class="text-muted small">{{$item->slug}}</div>
                                            <a href="{{ route('admin.cms.board.posts', $item->code) }}" class="text-decoration-none">
                                                <code class="text-primary">{{$item->code}}</code>
                                            </a>
                                        </div>
                                        <a href="/board/{{$item->code}}" target="_blank" title="미리보기" class="text-muted">
                                            <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </div>
                                </td>
                                <td width='100' class="text-center">
                                    <a href="{{ route('admin.cms.board.posts', $item->code) }}" class="text-decoration-none">
                                        <span class="badge bg-secondary">{{$item->post_count ?? 0}}</span>
                                    </a>
                                </td>
                                <td width='120' class="text-center">
                                    <span class="badge bg-primary">{{number_format($item->total_views ?? 0)}}</span>
                                </td>
                                <td>
                                    <div>
                                        <a href="{{ route('admin.cms.board.posts', $item->code) }}" class="text-decoration-none">
                                            <strong class="text-dark">{{$item->title}}</strong>
                                        </a>
                                        @if($item->enable)
                                            <span class="badge bg-success ms-2">활성</span>
                                        @else
                                            <span class="badge bg-secondary ms-2">비활성</span>
                                        @endif
                                    </div>
                                    @if($item->subtitle)
                                        <div class="text-muted small mt-1">
                                            {{$item->subtitle}}
                                        </div>
                                    @endif
                                </td>
                                <td width='200'>
                                    <span class="text-muted">{{$item->view_layout ?? '-'}}</span>
                                </td>
                                <td width='200'>
                                    <span class="text-muted">{{$item->manager ?? '-'}}</span>
                                </td>
                                <td width='200'>
                                    <small class="text-muted">{{$item->created_at}}</small>
                                </td>
                                <td width='150' class="text-center pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.cms.board.list.edit', $item->id) }}"
                                           class="btn btn-outline-primary" title="수정">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.cms.board.list.destroy', $item->id) }}"
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
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">등록된 게시판이 없습니다.</p>
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
                            총 {{ $rows->total() }}개의 게시판이 있습니다.
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

/* 빈 상태 개선 */
.table tbody tr td:last-child {
    border-bottom: none;
}

/* 코드 스타일 */
code.text-primary {
    background-color: rgba(13, 110, 253, 0.1);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
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
</style>
@endpush
@endsection
