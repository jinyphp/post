@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤딩 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3><i class="bi bi-journal-text me-2 text-primary"></i>블로그 관리</h3>
            <p class="text-muted mb-0">블로그 글을 작성하고 관리합니다.</p>
        </div>
        <div>
            <a href="{{ route('admin.cms.blog.config') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-gear me-1"></i> 블로그 설정
            </a>
            <a href="{{ route('admin.cms.blog.comment') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-chat-dots me-1"></i> 댓글 관리
            </a>
            <a href="{{ route('admin.cms.blog.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> 새 글 작성
            </a>
        </div>
    </div>

    <hr>

    <!-- 상태별 통계 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">전체 글</div>
                            <div class="h4 mb-0 text-primary fw-bold">{{ array_sum($statusCounts) }}</div>
                        </div>
                        <div class="ms-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-file-text text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">발행됨</div>
                            <div class="h4 mb-0 text-success fw-bold">{{ $statusCounts['published'] ?? 0 }}</div>
                        </div>
                        <div class="ms-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">초안</div>
                            <div class="h4 mb-0 text-warning fw-bold">{{ $statusCounts['draft'] ?? 0 }}</div>
                        </div>
                        <div class="ms-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-pencil text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">예약됨</div>
                            <div class="h4 mb-0 text-info fw-bold">{{ $statusCounts['scheduled'] ?? 0 }}</div>
                        </div>
                        <div class="ms-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-clock text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 블로그 목록 -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>
                        블로그 글 목록
                    </h5>
                    <p class="text-muted mb-0 small">등록된 블로그 글을 관리합니다.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- 검색 폼 -->
                    <form method="GET" action="{{ route('admin.cms.blog') }}" class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="제목, 내용으로 검색..."
                                   value="{{ $search }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="category" value="{{ $category }}">
                    </form>

                    <!-- 필터 드롭다운 -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i>필터
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">상태별</h6></li>
                            <li><a class="dropdown-item {{ $status === '' ? 'active' : '' }}" href="{{ route('admin.cms.blog') }}">전체</a></li>
                            <li><a class="dropdown-item {{ $status === 'published' ? 'active' : '' }}" href="?status=published">발행됨</a></li>
                            <li><a class="dropdown-item {{ $status === 'draft' ? 'active' : '' }}" href="?status=draft">초안</a></li>
                            <li><a class="dropdown-item {{ $status === 'scheduled' ? 'active' : '' }}" href="?status=scheduled">예약됨</a></li>
                            @if(count($categories) > 0)
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">카테고리별</h6></li>
                            @foreach($categories as $cat)
                            <li><a class="dropdown-item {{ $category === $cat->slug ? 'active' : '' }}" href="?category={{ $cat->slug }}">{{ $cat->name }}</a></li>
                            @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="min-width: 800px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">제목</th>
                            <th width="120" class="text-center">카테고리</th>
                            <th width="100" class="text-center">상태</th>
                            <th width="100" class="text-center">조회수</th>
                            <th width="100" class="text-center">댓글</th>
                            <th width="160">작성일</th>
                            <th width="140" class="text-center pe-4">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blogs as $blog)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        @if($blog->featured_image)
                                            <img src="{{ $blog->featured_image }}"
                                                 class="rounded me-3"
                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.cms.blog.show', $blog->id) }}"
                                               class="text-decoration-none">
                                                <strong class="text-dark">{{ $blog->title }}</strong>
                                            </a>
                                            @if($blog->slug)
                                                <span class="badge bg-light text-dark border ms-2">
                                                    <i class="bi bi-link-45deg me-1"></i>{{ $blog->slug }}
                                                </span>
                                            @endif
                                            @if($blog->is_featured)
                                                <span class="badge bg-primary ms-2">추천</span>
                                            @endif
                                            @if($blog->is_sticky)
                                                <span class="badge bg-warning ms-1">고정</span>
                                            @endif
                                            @if($blog->excerpt)
                                                <div class="text-muted small mt-1">
                                                    {{ Str::limit($blog->excerpt, 80) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td width="120" class="text-center">
                                    @if($blog->category_name)
                                        <span class="badge" style="background-color: {{ $blog->category_color ?? '#6c757d' }};">
                                            {{ $blog->category_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td width="100" class="text-center">
                                    @php
                                        $statusClass = match($blog->status) {
                                            'published' => 'success',
                                            'draft' => 'warning',
                                            'scheduled' => 'info',
                                            default => 'secondary'
                                        };
                                        $statusText = match($blog->status) {
                                            'published' => '발행됨',
                                            'draft' => '초안',
                                            'scheduled' => '예약됨',
                                            default => '알 수 없음'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td width="100" class="text-center">
                                    <span class="badge bg-secondary">{{ number_format($blog->views ?? 0) }}</span>
                                </td>
                                <td width="100" class="text-center">
                                    @if(isset($blog->comments_count) && $blog->comments_count > 0)
                                        <a href="{{ route('admin.cms.blog.comment') }}?blog_id={{ $blog->id }}"
                                           class="badge bg-primary text-decoration-none"
                                           title="댓글 관리">
                                            <i class="bi bi-chat-dots me-1"></i>{{ number_format($blog->comments_count) }}
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted">0</span>
                                    @endif
                                </td>
                                <td width="160">
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($blog->created_at)->format('Y-m-d H:i') }}</small>
                                </td>
                                <td width="140" class="text-center pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.cms.blog.show', $blog->id) }}"
                                           class="btn btn-outline-info" title="보기">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.cms.blog.edit', $blog->id) }}"
                                           class="btn btn-outline-primary" title="수정">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger delete-btn"
                                                data-id="{{ $blog->id }}"
                                                data-title="{{ $blog->title }}"
                                                title="삭제">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                                    <p class="mb-0">등록된 블로그 글이 없습니다.</p>
                                    <a href="{{ route('admin.cms.blog.create') }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-plus-circle me-1"></i> 첫 번째 글 작성하기
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($blogs, 'links'))
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            총 {{ $blogs->total() }}개의 블로그 글이 있습니다.
                        </div>
                        <div>
                            {{ $blogs->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">블로그 글 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>정말로 <strong id="deleteItemTitle"></strong> 글을 삭제하시겠습니까?</p>
                <p class="text-danger small">이 작업은 되돌릴 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let currentDeleteId = null;

    // 삭제 버튼 클릭 이벤트
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeleteId = this.dataset.id;
            document.getElementById('deleteItemTitle').textContent = this.dataset.title;
            deleteModal.show();
        });
    });

    // 삭제 확인 버튼 클릭 이벤트
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!currentDeleteId) return;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 삭제 중...';

        fetch(`/admin/cms/blog/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteModal.hide();
                location.reload();
            } else {
                alert(data.message || '삭제에 실패했습니다.');
                this.disabled = false;
                this.innerHTML = '삭제';
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('서버 오류가 발생했습니다.');
            this.disabled = false;
            this.innerHTML = '삭제';
        });
    });
});
</script>
@endpush

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
    white-space: nowrap;
    display: inline-block;
    min-width: 40px;
}

/* 액션 버튼 개선 */
.btn-group-sm .btn {
    padding: 0.375rem 0.5rem;
}

/* 빈 상태 개선 */
.table tbody tr td:last-child {
    border-bottom: none;
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

/* 통계 카드 개선 */
.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

/* 카드 그림자 개선 */
.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

/* 이미지 플레이스홀더 */
.bg-light {
    background-color: #f8f9fa !important;
}

/* 액티브 드롭다운 아이템 */
.dropdown-item.active {
    background-color: var(--bs-primary);
    color: white;
}

/* 버튼 그룹 간격 개선 */
.btn-group-sm .btn + .btn {
    margin-left: -1px;
}

/* 테이블 헤더 */
.table-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

/* 테이블 레이아웃 고정 */
.table {
    table-layout: fixed;
}

/* 조회수 컬럼 최적화 */
.table td:nth-child(4),
.table th:nth-child(4) {
    min-width: 100px;
    width: 100px;
}

/* 작업 컬럼 최적화 */
.table td:nth-child(6),
.table th:nth-child(6) {
    min-width: 140px;
    width: 140px;
}

/* 반응형 개선 */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .input-group {
        width: 100% !important;
    }
}
</style>
@endpush
