@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', $blog->title)

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">블로그 글 보기</h1>
        <div>
            <a href="{{ route('admin.cms.blog.edit', $blog->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> 수정
            </a>
            <button type="button" class="btn btn-danger" id="deleteBtn" data-id="{{ $blog->id }}" data-title="{{ $blog->title }}">
                <i class="fas fa-trash"></i> 삭제
            </button>
            <a href="{{ route('admin.cms.blog') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- 메인 콘텐츠 -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <!-- 상태 배지 -->
                    <div class="mb-3">
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
                        <span class="badge bg-{{ $statusClass }} fs-6">{{ $statusText }}</span>

                        @if($blog->is_featured)
                            <span class="badge bg-primary">추천 글</span>
                        @endif

                        @if($blog->is_sticky)
                            <span class="badge bg-warning">상단 고정</span>
                        @endif
                    </div>

                    <!-- 제목 -->
                    <h1 class="h2 mb-3">{{ $blog->title }}</h1>

                    <!-- 메타 정보 -->
                    <div class="text-muted mb-4">
                        <div class="row">
                            <div class="col-sm-6">
                                <small>
                                    <i class="fas fa-user"></i> {{ $blog->author_name ?? '관리자' }} |
                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($blog->created_at)->format('Y-m-d H:i') }}
                                    @if($blog->updated_at !== $blog->created_at)
                                        <br><i class="fas fa-edit"></i> 수정됨: {{ \Carbon\Carbon::parse($blog->updated_at)->format('Y-m-d H:i') }}
                                    @endif
                                </small>
                            </div>
                            <div class="col-sm-6 text-end">
                                <small>
                                    <i class="fas fa-eye"></i> {{ number_format($blog->views ?? 0) }} |
                                    <i class="fas fa-heart"></i> {{ number_format($blog->likes ?? 0) }} |
                                    <i class="fas fa-share"></i> {{ number_format($blog->shares ?? 0) }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- 카테고리 -->
                    @if($blog->category_name)
                    <div class="mb-3">
                        <span class="badge" style="background-color: {{ $blog->category_color ?? '#6c757d' }};">
                            @if($blog->category_icon)
                                <i class="{{ $blog->category_icon }}"></i>
                            @endif
                            {{ $blog->category_name }}
                        </span>
                    </div>
                    @endif

                    <!-- 대표 이미지 -->
                    @if($blog->featured_image)
                    <div class="mb-4 text-center">
                        <img src="{{ $blog->featured_image }}"
                             class="img-fluid rounded shadow-sm"
                             alt="{{ $blog->featured_image_alt ?? $blog->title }}"
                             style="max-height: 400px;">
                        @if($blog->featured_image_alt)
                            <div class="text-muted small mt-2">{{ $blog->featured_image_alt }}</div>
                        @endif
                    </div>
                    @endif

                    <!-- 요약 -->
                    @if($blog->excerpt)
                    <div class="alert alert-light">
                        <h6><i class="fas fa-quote-left"></i> 요약</h6>
                        <p class="mb-0">{{ $blog->excerpt }}</p>
                    </div>
                    @endif

                    <!-- 본문 -->
                    <div class="blog-content">
                        {!! nl2br(e($blog->content)) !!}
                    </div>

                    <!-- 태그 -->
                    @if($blog->tags)
                    <hr>
                    <div class="mb-3">
                        <h6><i class="fas fa-tags"></i> 태그</h6>
                        @foreach(explode(',', $blog->tags) as $tag)
                            <span class="badge bg-light text-dark me-1">#{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                    @endif

                    <!-- 발행 정보 -->
                    @if($blog->published_at)
                    <hr>
                    <div class="text-muted small">
                        <i class="fas fa-broadcast-tower"></i>
                        발행일: {{ \Carbon\Carbon::parse($blog->published_at)->format('Y년 m월 d일 H:i') }}
                    </div>
                    @endif

                    @if($blog->scheduled_at && $blog->status === 'scheduled')
                    <hr>
                    <div class="text-info small">
                        <i class="fas fa-clock"></i>
                        예약 발행: {{ \Carbon\Carbon::parse($blog->scheduled_at)->format('Y년 m월 d일 H:i') }}
                    </div>
                    @endif
                </div>
            </div>

            @if($blog->allow_comments)
            <!-- 댓글 섹션 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-chat-dots me-2"></i>댓글 ({{ count($commentsTree) }})
                    </h6>
                </div>
                <div class="card-body">
                    @if(count($commentsTree) > 0)
                        <div id="comments-list">
                            @each('jiny-post::admin.blog.partials.comment', $commentsTree, 'comment')
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                            <p class="mb-0">아직 댓글이 없습니다.</p>
                        </div>
                    @endif

                    <!-- 댓글 작성 폼 -->
                    <hr class="mt-4">
                    <div id="comment-form-section">
                        <h6 class="mb-3">댓글 작성</h6>
                        <form id="commentForm">
                            @csrf
                            <input type="hidden" name="parent_id" id="parent_id" value="">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="author_name" class="form-label">이름 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="author_name" name="author_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="author_email" class="form-label">이메일 <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="author_email" name="author_email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="author_website" class="form-label">웹사이트</label>
                                <input type="url" class="form-control" id="author_website" name="author_website">
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">댓글 내용 <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">댓글은 관리자 승인 후 표시됩니다.</small>
                                <button type="submit" class="btn btn-primary" id="submitComment">
                                    <i class="bi bi-send me-1"></i>댓글 등록
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <!-- 댓글 비활성화 안내 -->
            <div class="card shadow mb-4">
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-chat-slash fs-1 d-block mb-2"></i>
                    <p class="mb-0">이 글은 댓글이 허용되지 않습니다.</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- 글 정보 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">글 정보</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">ID</th>
                            <td>{{ $blog->id }}</td>
                        </tr>
                        <tr>
                            <th>슬러그</th>
                            <td>
                                @if($blog->slug)
                                    <code>{{ $blog->slug }}</code>
                                @else
                                    <span class="text-muted">없음</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>상태</th>
                            <td>
                                <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>작성자</th>
                            <td>{{ $blog->author_name ?? '관리자' }}</td>
                        </tr>
                        @if($blog->author_email)
                        <tr>
                            <th>이메일</th>
                            <td>{{ $blog->author_email }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>댓글</th>
                            <td>
                                @if($blog->allow_comments)
                                    <span class="text-success"><i class="fas fa-check"></i> 허용</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-times"></i> 비허용</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>생성일</th>
                            <td>{{ \Carbon\Carbon::parse($blog->created_at)->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>수정일</th>
                            <td>{{ \Carbon\Carbon::parse($blog->updated_at)->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- 통계 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">통계</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-0 text-gray-800">{{ number_format($blog->views ?? 0) }}</div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase">조회수</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-gray-800">{{ number_format($blog->likes ?? 0) }}</div>
                            <div class="text-xs font-weight-bold text-success text-uppercase">좋아요</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-gray-800">{{ number_format($blog->shares ?? 0) }}</div>
                            <div class="text-xs font-weight-bold text-info text-uppercase">공유수</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO 정보 -->
            @if($blog->meta_title || $blog->meta_description || $blog->meta_keywords)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">SEO 정보</h6>
                </div>
                <div class="card-body">
                    @if($blog->meta_title)
                    <div class="mb-2">
                        <strong>메타 제목:</strong><br>
                        <small class="text-muted">{{ $blog->meta_title }}</small>
                    </div>
                    @endif

                    @if($blog->meta_description)
                    <div class="mb-2">
                        <strong>메타 설명:</strong><br>
                        <small class="text-muted">{{ $blog->meta_description }}</small>
                    </div>
                    @endif

                    @if($blog->meta_keywords)
                    <div class="mb-2">
                        <strong>메타 키워드:</strong><br>
                        <small class="text-muted">{{ $blog->meta_keywords }}</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- 댓글 관리 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-chat-dots me-2"></i>댓글 관리
                    </h6>
                    <a href="{{ route('admin.cms.blog.comment', ['blog_id' => $blog->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-gear me-1"></i>관리
                    </a>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="h5 mb-0 text-gray-800">{{ $commentStats['total'] }}</div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase">전체</div>
                        </div>
                        <div class="col-4">
                            <div class="h5 mb-0 text-success">{{ $commentStats['approved'] }}</div>
                            <div class="text-xs font-weight-bold text-success text-uppercase">승인됨</div>
                        </div>
                        <div class="col-4">
                            <div class="h5 mb-0 text-warning">{{ $commentStats['pending'] }}</div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase">대기</div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="allow_comments_display"
                               {{ $blog->allow_comments ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="allow_comments_display">
                            댓글 허용
                            @if($blog->allow_comments)
                                <span class="badge bg-success ms-1">활성</span>
                            @else
                                <span class="badge bg-secondary ms-1">비활성</span>
                            @endif
                        </label>
                    </div>

                    @if($commentStats['pending'] > 0)
                        <div class="alert alert-warning alert-sm p-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <small>승인 대기 중인 댓글 {{ $commentStats['pending'] }}개</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 작업 메뉴 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">작업</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.cms.blog.edit', $blog->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> 수정
                        </a>

                        @if($blog->status === 'published')
                        <a href="#" class="btn btn-info" onclick="alert('공개 페이지 링크 기능은 추후 구현됩니다.')">
                            <i class="fas fa-external-link-alt"></i> 공개 페이지 보기
                        </a>
                        @endif

                        <button type="button" class="btn btn-warning" onclick="alert('복제 기능은 추후 구현됩니다.')">
                            <i class="fas fa-copy"></i> 복제
                        </button>

                        <button type="button" class="btn btn-danger" id="deleteBtn2" data-id="{{ $blog->id }}" data-title="{{ $blog->title }}">
                            <i class="fas fa-trash"></i> 삭제
                        </button>
                    </div>
                </div>
            </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let currentDeleteId = null;

    // 삭제 버튼들에 이벤트 리스너 추가
    document.querySelectorAll('#deleteBtn, #deleteBtn2').forEach(btn => {
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
                window.location.href = data.redirect || '{{ route("admin.cms.blog") }}';
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

    // 댓글 기능
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        // 댓글 제출
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitComment');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i>등록 중...';

            fetch(`/blog/{{ $blog->id }}/comment`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 성공 시 폼 초기화
                    commentForm.reset();
                    document.getElementById('parent_id').value = '';

                    // 성공 메시지 표시
                    showMessage(data.message, 'success');

                    // 답글 모드 해제
                    cancelReply();
                } else {
                    showMessage(data.message || '댓글 등록에 실패했습니다.', 'error');
                }
            })
            .catch(error => {
                console.error('Comment submission error:', error);
                showMessage('서버 오류가 발생했습니다.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send me-1"></i>댓글 등록';
            });
        });

        // 답글 버튼 클릭
        document.addEventListener('click', function(e) {
            if (e.target.closest('.reply-btn')) {
                const btn = e.target.closest('.reply-btn');
                const commentId = btn.dataset.commentId;
                const authorName = btn.dataset.author;

                // 답글 모드 설정
                document.getElementById('parent_id').value = commentId;
                document.querySelector('#comment-form-section h6').textContent = `${authorName}님에게 답글 작성`;

                // 취소 버튼 추가
                if (!document.getElementById('cancelReplyBtn')) {
                    const cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.id = 'cancelReplyBtn';
                    cancelBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
                    cancelBtn.innerHTML = '<i class="bi bi-x"></i> 취소';
                    cancelBtn.onclick = cancelReply;

                    document.querySelector('#comment-form-section h6').appendChild(cancelBtn);
                }

                // 폼으로 스크롤
                document.getElementById('comment-form-section').scrollIntoView({ behavior: 'smooth' });
                document.getElementById('content').focus();
            }
        });
    }

    function cancelReply() {
        document.getElementById('parent_id').value = '';
        document.querySelector('#comment-form-section h6').textContent = '댓글 작성';

        const cancelBtn = document.getElementById('cancelReplyBtn');
        if (cancelBtn) {
            cancelBtn.remove();
        }
    }

    function showMessage(message, type) {
        // 기존 메시지 제거
        const existingAlert = document.querySelector('.comment-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        // 새 메시지 생성
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} comment-alert alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // 폼 위에 추가
        const formSection = document.getElementById('comment-form-section');
        formSection.insertBefore(alertDiv, formSection.firstChild);

        // 3초 후 자동 제거
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
});
</script>

<style>
.blog-content {
    line-height: 1.6;
    font-size: 1.1rem;
}

.blog-content p {
    margin-bottom: 1rem;
}

.blog-content h1,
.blog-content h2,
.blog-content h3,
.blog-content h4,
.blog-content h5,
.blog-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.blog-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.blog-content blockquote {
    border-left: 4px solid #e9ecef;
    padding-left: 1rem;
    margin: 1.5rem 0;
    color: #6c757d;
    font-style: italic;
}

/* 댓글 스타일 */
.comment {
    position: relative;
}

.comment + .comment {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.comment .comment {
    border-top: none;
    padding-top: 0;
}

.reply-btn {
    font-size: 0.875rem;
    text-decoration: none !important;
}

.reply-btn:hover {
    color: var(--bs-primary) !important;
}

.comment-alert {
    margin-bottom: 1rem;
}

/* 스핀 애니메이션 */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection
