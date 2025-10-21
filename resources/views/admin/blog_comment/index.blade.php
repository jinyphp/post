@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 댓글 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤딩 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3><i class="bi bi-chat-dots me-2 text-primary"></i>블로그 댓글 관리</h3>
            <p class="text-muted mb-0">블로그 댓글을 관리하고 승인/거부를 처리합니다.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createCommentModal">
                <i class="bi bi-plus-circle me-1"></i> 새 댓글 작성
            </button>
            <a href="{{ route('admin.cms.blog') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> 블로그 관리
            </a>
        </div>
    </div>

    <hr>

    <!-- 상태별 통계 -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">전체 댓글</div>
                            <div class="h4 mb-0 text-primary fw-bold">{{ $statusCounts['total'] }}</div>
                        </div>
                        <div class="ms-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-chat-dots text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">승인됨</div>
                            <div class="h4 mb-0 text-success fw-bold">{{ $statusCounts['approved'] }}</div>
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
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase mb-1">승인 대기</div>
                            <div class="h4 mb-0 text-warning fw-bold">{{ $statusCounts['pending'] }}</div>
                        </div>
                        <div class="ms-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-clock text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 댓글 목록 -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        댓글 목록
                    </h5>
                    <p class="text-muted mb-0 small">등록된 블로그 댓글을 관리합니다.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- 검색 폼 -->
                    <form method="GET" action="{{ route('admin.cms.blog.comment') }}" class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="작성자, 내용, 블로그 제목으로 검색..."
                                   value="{{ $search }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="blog_id" value="{{ $blogId }}">
                    </form>

                    <!-- 필터 드롭다운 -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i>필터
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">상태별</h6></li>
                            <li><a class="dropdown-item {{ $status === '' ? 'active' : '' }}" href="?status=">전체</a></li>
                            <li><a class="dropdown-item {{ $status === 'approved' ? 'active' : '' }}" href="?status=approved">승인됨</a></li>
                            <li><a class="dropdown-item {{ $status === 'pending' ? 'active' : '' }}" href="?status=pending">승인 대기</a></li>
                            @if($recentBlogs->count() > 0)
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">최근 블로그</h6></li>
                            @foreach($recentBlogs as $blog)
                                <li><a class="dropdown-item {{ $blogId == $blog->id ? 'active' : '' }}" href="?blog_id={{ $blog->id }}">{{ Str::limit($blog->title, 30) }}</a></li>
                            @endforeach
                            @endif
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
                            <th width="50" class="ps-4 text-center">ID</th>
                            <th>댓글 내용</th>
                            <th width="150">작성자</th>
                            <th width="200">블로그 글</th>
                            <th width="100" class="text-center">상태</th>
                            <th width="160">작성일</th>
                            <th width="140" class="text-center pe-4">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comments as $comment)
                            <tr data-comment-id="{{ $comment->id }}">
                                <td width="50" class="ps-4 text-center">
                                    <span class="badge bg-light text-dark">{{ $comment->id }}</span>
                                </td>
                                <td>
                                    <div class="mb-2">
                                        <div class="text-dark">{{ Str::limit($comment->content, 100) }}</div>
                                        @if($comment->parent_id)
                                            <small class="text-muted">
                                                <i class="bi bi-arrow-return-right me-1"></i>답글
                                            </small>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        IP: {{ $comment->ip_address }}
                                    </div>
                                </td>
                                <td width="150">
                                    <div>
                                        <strong>{{ $comment->author_name }}</strong>
                                        @if($comment->user_name)
                                            <span class="badge bg-info ms-1">회원</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">{{ $comment->author_email }}</div>
                                    @if($comment->author_website)
                                        <div class="text-muted small">
                                            <a href="{{ $comment->author_website }}" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-globe me-1"></i>웹사이트
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td width="200">
                                    @if($comment->blog_title)
                                        <a href="{{ route('admin.cms.blog.show', $comment->blog_id) }}"
                                           class="text-decoration-none" target="_blank">
                                            <strong class="text-dark">{{ Str::limit($comment->blog_title, 30) }}</strong>
                                        </a>
                                        <div class="text-muted small">ID: {{ $comment->blog_id }}</div>
                                    @else
                                        <span class="text-muted">삭제된 글</span>
                                    @endif
                                </td>
                                <td width="100" class="text-center">
                                    @if($comment->is_approved)
                                        <span class="badge bg-success">승인됨</span>
                                    @else
                                        <span class="badge bg-warning">대기</span>
                                    @endif
                                </td>
                                <td width="160">
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($comment->created_at)->format('Y-m-d H:i') }}</small>
                                </td>
                                <td width="140" class="text-center pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button"
                                                class="btn btn-outline-primary edit-btn"
                                                data-id="{{ $comment->id }}"
                                                title="편집">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-{{ $comment->is_approved ? 'warning' : 'success' }} approve-btn"
                                                data-id="{{ $comment->id }}"
                                                data-approved="{{ $comment->is_approved }}"
                                                title="{{ $comment->is_approved ? '승인 취소' : '승인' }}">
                                            <i class="bi bi-{{ $comment->is_approved ? 'x-circle' : 'check-circle' }}"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-danger delete-btn"
                                                data-id="{{ $comment->id }}"
                                                title="삭제">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                                    <p class="mb-0">등록된 댓글이 없습니다.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($comments, 'links'))
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            총 {{ $comments->total() }}개의 댓글이 있습니다.
                        </div>
                        <div>
                            {{ $comments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">댓글 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>정말로 이 댓글을 삭제하시겠습니까?</p>
                <p class="text-danger small">이 작업은 되돌릴 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>

<!-- 댓글 편집 모달 -->
<div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">댓글 편집</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCommentForm">
                <div class="modal-body">
                    <input type="hidden" id="editCommentId" name="comment_id">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editAuthorName" class="form-label">작성자명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAuthorName" name="author_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editAuthorEmail" class="form-label">이메일 <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editAuthorEmail" name="author_email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editAuthorWebsite" class="form-label">웹사이트</label>
                        <input type="url" class="form-control" id="editAuthorWebsite" name="author_website">
                    </div>

                    <div class="mb-3">
                        <label for="editContent" class="form-label">댓글 내용 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editContent" name="content" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editStatus" class="form-label">상태</label>
                        <select class="form-select" id="editStatus" name="is_approved">
                            <option value="1">승인됨</option>
                            <option value="0">승인 대기</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">수정 완료</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 댓글 생성 모달 -->
<div class="modal fade" id="createCommentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">새 댓글 작성</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCommentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createBlogId" class="form-label">블로그 글 선택 <span class="text-danger">*</span></label>
                        <select class="form-select" id="createBlogId" name="blog_id" required>
                            <option value="">블로그 글을 선택하세요</option>
                            @foreach($recentBlogs as $blog)
                                <option value="{{ $blog->id }}" {{ $blogId == $blog->id ? 'selected' : '' }}>
                                    {{ $blog->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="createAuthorName" class="form-label">작성자명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="createAuthorName" name="author_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="createAuthorEmail" class="form-label">이메일 <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="createAuthorEmail" name="author_email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="createAuthorWebsite" class="form-label">웹사이트</label>
                        <input type="url" class="form-control" id="createAuthorWebsite" name="author_website">
                    </div>

                    <div class="mb-3">
                        <label for="createContent" class="form-label">댓글 내용 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="createContent" name="content" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="createStatus" class="form-label">상태</label>
                        <select class="form-select" id="createStatus" name="is_approved">
                            <option value="1">승인됨</option>
                            <option value="0">승인 대기</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">댓글 작성</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let editModal = new bootstrap.Modal(document.getElementById('editCommentModal'));
    let createModal = new bootstrap.Modal(document.getElementById('createCommentModal'));
    let currentDeleteId = null;

    // 승인/승인취소 버튼 클릭 이벤트
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.dataset.id;
            const isApproved = this.dataset.approved === '1';

            this.disabled = true;
            this.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';

            fetch(`/admin/cms/blog/comment/${commentId}/approve`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '처리에 실패했습니다.');
                    this.disabled = false;
                    this.innerHTML = `<i class="bi bi-${isApproved ? 'x-circle' : 'check-circle'}"></i>`;
                }
            })
            .catch(error => {
                console.error('Approval error:', error);
                alert('서버 오류가 발생했습니다.');
                this.disabled = false;
                this.innerHTML = `<i class="bi bi-${isApproved ? 'x-circle' : 'check-circle'}"></i>`;
            });
        });
    });

    // 삭제 버튼 클릭 이벤트
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeleteId = this.dataset.id;
            deleteModal.show();
        });
    });

    // 삭제 확인 버튼 클릭 이벤트
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!currentDeleteId) return;

        this.disabled = true;
        this.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i> 삭제 중...';

        fetch(`/admin/cms/blog/comment/${currentDeleteId}`, {
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

    // 편집 버튼 클릭 이벤트
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.dataset.id;

            // 댓글 정보 가져오기
            fetch(`/admin/cms/blog/comment/${commentId}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const comment = data.comment;
                        document.getElementById('editCommentId').value = comment.id;
                        document.getElementById('editAuthorName').value = comment.author_name;
                        document.getElementById('editAuthorEmail').value = comment.author_email;
                        document.getElementById('editAuthorWebsite').value = comment.author_website || '';
                        document.getElementById('editContent').value = comment.content;
                        document.getElementById('editStatus').value = comment.is_approved;

                        editModal.show();
                    } else {
                        alert(data.message || '댓글 정보를 불러오는데 실패했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Edit fetch error:', error);
                    alert('서버 오류가 발생했습니다.');
                });
        });
    });

    // 편집 폼 제출 이벤트
    document.getElementById('editCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const commentId = document.getElementById('editCommentId').value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i> 수정 중...';

        fetch(`/admin/cms/blog/comment/${commentId}/update`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editModal.hide();
                location.reload();
            } else {
                alert(data.message || '수정에 실패했습니다.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '수정 완료';
            }
        })
        .catch(error => {
            console.error('Edit submit error:', error);
            alert('서버 오류가 발생했습니다.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '수정 완료';
        });
    });

    // 생성 폼 제출 이벤트
    document.getElementById('createCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i> 작성 중...';

        fetch('/admin/cms/blog/comment/store', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                createModal.hide();
                location.reload();
            } else {
                alert(data.message || '작성에 실패했습니다.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '댓글 작성';
            }
        })
        .catch(error => {
            console.error('Create submit error:', error);
            alert('서버 오류가 발생했습니다.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '댓글 작성';
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

/* 스핀 애니메이션 */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* 액티브 드롭다운 아이템 */
.dropdown-item.active {
    background-color: var(--bs-primary);
    color: white;
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