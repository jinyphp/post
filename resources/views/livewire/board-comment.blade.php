<div>
    <!-- 댓글 섹션 -->
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bi bi-chat-dots me-2"></i>댓글
                @if(count($comments) > 0)
                    <span class="badge bg-primary ms-2">{{ count($comments) }}</span>
                @endif
            </h5>
        </div>

        <!-- 댓글 작성 폼 -->
        @if($canComment)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form wire:submit.prevent="store">
                        <div class="mb-3">
                            <label for="content" class="form-label">댓글 작성</label>
                            <textarea
                                wire:model="content"
                                class="form-control @error('content') is-invalid @enderror"
                                id="content"
                                rows="3"
                                placeholder="댓글을 입력하세요..."
                                maxlength="1000"
                            ></textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <span wire:ignore>
                                    <span id="char-count">0</span>/1000자
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            @auth
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>{{ auth()->user()->name }}으로 댓글 작성
                                </small>
                            @else
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>익명으로 댓글 작성
                                </small>
                            @endauth

                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading wire:target="store">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    등록 중...
                                </span>
                                <span wire:loading.remove wire:target="store">
                                    <i class="bi bi-check-circle me-1"></i>댓글 등록
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                @auth
                    댓글 작성 권한이 없습니다.
                @else
                    댓글을 작성하려면 <a href="{{ route('login') }}" class="alert-link">로그인</a>해주세요.
                @endauth
            </div>
        @endif

        <!-- 댓글 목록 -->
        <div class="comments-list">
            @if(count($comments) > 0)
                @foreach($comments as $comment)
                    <div class="card border-0 shadow-sm mb-3 {{ ($comment->is_reply ?? false) ? 'ms-4 reply-comment' : '' }}" wire:key="comment-{{ $comment->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ mb_substr($comment->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $comment->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($comment->created_at)->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                </div>

                                <!-- 댓글 액션 버튼 -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @auth
                                            @if($comment->user_id == auth()->id())
                                                <li>
                                                    <button class="dropdown-item"
                                                            wire:click="startEdit({{ $comment->id }})">
                                                        <i class="bi bi-pencil me-2"></i>수정
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger"
                                                            wire:click="deleteComment({{ $comment->id }})"
                                                            wire:confirm="정말 이 댓글을 삭제하시겠습니까?">
                                                        <i class="bi bi-trash me-2"></i>삭제
                                                    </button>
                                                </li>
                                            @endif
                                        @endauth
                                        <li>
                                            <button class="dropdown-item"
                                                    wire:click="reportComment({{ $comment->id }})">
                                                <i class="bi bi-flag me-2"></i>신고
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="comment-content">
                                @if($editingComment == $comment->id)
                                    <!-- 수정 폼 -->
                                    <form wire:submit.prevent="updateComment">
                                        <div class="mb-3">
                                            <textarea
                                                wire:model="editContent"
                                                class="form-control @error('editContent') is-invalid @enderror"
                                                rows="3"
                                                placeholder="수정할 댓글 내용을 입력하세요..."
                                                maxlength="1000"
                                            ></textarea>
                                            @error('editContent')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    wire:click="cancelEdit">
                                                <i class="bi bi-x me-1"></i>취소
                                            </button>
                                            <button type="submit"
                                                    class="btn btn-sm btn-primary"
                                                    wire:loading.attr="disabled">
                                                <span wire:loading wire:target="updateComment">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                    수정 중...
                                                </span>
                                                <span wire:loading.remove wire:target="updateComment">
                                                    <i class="bi bi-check-circle me-1"></i>수정 완료
                                                </span>
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <!-- 일반 댓글 내용 -->
                                    <p class="mb-0">{!! nl2br(e($comment->content)) !!}</p>
                                    @if($comment->updated_at != $comment->created_at)
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-pencil me-1"></i>수정됨
                                        </small>
                                    @endif
                                @endif
                            </div>

                            <!-- 댓글 액션 (좋아요, 답글 등) -->
                            <div class="comment-actions mt-2 pt-2 border-top">
                                <div class="d-flex gap-3">
                                    <button class="btn btn-sm {{ $comment->user_liked ?? false ? 'btn-danger' : 'btn-outline-secondary' }}"
                                            wire:click="likeComment({{ $comment->id }})"
                                            wire:loading.attr="disabled">
                                        <i class="bi bi-heart{{ $comment->user_liked ?? false ? '-fill' : '' }} me-1"></i>
                                        좋아요 {{ ($comment->likes_count ?? 0) > 0 ? '(' . ($comment->likes_count ?? 0) . ')' : '' }}
                                    </button>
                                    @if(!($comment->is_reply ?? false))
                                        <button class="btn btn-sm btn-outline-secondary"
                                                wire:click="startReply({{ $comment->id }})"
                                                @if($replyingTo == $comment->id) disabled @endif>
                                            <i class="bi bi-reply me-1"></i>답글
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- 답글 작성 폼 -->
                            @if($replyingTo == $comment->id && $canComment)
                                <div class="mt-3 ps-4 border-start border-primary border-2">
                                    <form wire:submit.prevent="storeReply">
                                        <div class="mb-3">
                                            <textarea
                                                wire:model="replyContent"
                                                class="form-control @error('replyContent') is-invalid @enderror"
                                                rows="3"
                                                placeholder="답글을 입력하세요..."
                                                maxlength="1000"
                                            ></textarea>
                                            @error('replyContent')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <span wire:ignore>
                                                    <span id="reply-char-count">{{ strlen($replyContent) }}</span>/1000자
                                                </span>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    wire:click="cancelReply">
                                                <i class="bi bi-x me-1"></i>취소
                                            </button>
                                            <button type="submit"
                                                    class="btn btn-sm btn-primary"
                                                    wire:loading.attr="disabled">
                                                <span wire:loading wire:target="storeReply">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                    등록 중...
                                                </span>
                                                <span wire:loading.remove wire:target="storeReply">
                                                    <i class="bi bi-check-circle me-1"></i>답글 등록
                                                </span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                        <p class="mb-0">아직 댓글이 없습니다.</p>
                        @if($canComment)
                            <small>첫 번째 댓글을 작성해보세요!</small>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Toast 메시지 -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11;">
        <div id="messageToast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">알림</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 댓글 내용 글자수 카운터
    const contentTextarea = document.getElementById('content');
    const charCount = document.getElementById('char-count');

    if (contentTextarea && charCount) {
        contentTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // 답글 내용 글자수 카운터
    const replyTextarea = document.querySelector('textarea[wire\\:model="replyContent"]');
    const replyCharCount = document.getElementById('reply-char-count');

    if (replyTextarea && replyCharCount) {
        replyTextarea.addEventListener('input', function() {
            replyCharCount.textContent = this.value.length;
        });
    }

    // Livewire 이벤트 리스너
    window.addEventListener('show-message', function(event) {
        const { type, message } = event.detail[0];
        showToast(type, message);
    });

    // Toast 메시지 표시 함수
    function showToast(type, message) {
        const toast = document.getElementById('messageToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');

        if (toast && toastTitle && toastMessage) {
            // 타입에 따른 색상 설정
            toast.className = 'toast';
            if (type === 'success') {
                toast.classList.add('border-success');
                toastTitle.textContent = '성공';
            } else if (type === 'error') {
                toast.classList.add('border-danger');
                toastTitle.textContent = '오류';
            } else {
                toast.classList.add('border-info');
                toastTitle.textContent = '알림';
            }

            toastMessage.textContent = message;

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    }

    // 댓글 신고 함수는 Livewire로 처리됨
});
</script>
@endpush

@push('styles')
<style>
.comments-list .card {
    transition: all 0.2s ease;
}

.comments-list .card:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.comment-content {
    word-wrap: break-word;
    line-height: 1.6;
}

.avatar-sm {
    flex-shrink: 0;
}

.comment-actions .btn {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}

.toast {
    min-width: 300px;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* 로딩 상태 스타일 */
[wire\:loading] {
    opacity: 0.7;
}

/* 답글 스타일 */
.reply-comment {
    border-left: 3px solid #0d6efd !important;
    background-color: #f8f9fa;
}

.reply-comment .avatar-sm div {
    background-color: #6c757d !important;
}

/* 반응형 스타일 */
@media (max-width: 768px) {
    .comments-list .card-body {
        padding: 1rem;
    }

    .comment-actions .btn {
        font-size: 0.8rem;
        padding: 0.2rem 0.4rem;
    }

    .reply-comment {
        margin-left: 1rem !important;
    }
}
</style>
@endpush