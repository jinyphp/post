<div class="comment mb-3" data-comment-id="{{ $comment->id }}">
    <div class="d-flex">
        <!-- 아바타 -->
        <div class="me-3">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                 style="width: 48px; height: 48px;">
                {{ strtoupper(substr($comment->author_name, 0, 1)) }}
            </div>
        </div>

        <!-- 댓글 내용 -->
        <div class="flex-grow-1">
            <div class="bg-light rounded p-3">
                <!-- 헤더 -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong class="text-dark">{{ $comment->author_name }}</strong>
                        @if($comment->user_name)
                            <span class="badge bg-info ms-1">회원</span>
                        @endif
                        @if($comment->author_website)
                            <a href="{{ $comment->author_website }}" target="_blank" class="text-decoration-none ms-2">
                                <i class="bi bi-globe"></i>
                            </a>
                        @endif
                    </div>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                    </small>
                </div>

                <!-- 내용 -->
                <div class="text-dark">
                    {{ $comment->content }}
                </div>

                <!-- 액션 -->
                <div class="mt-2">
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted reply-btn"
                            data-comment-id="{{ $comment->id }}" data-author="{{ $comment->author_name }}">
                        <i class="bi bi-reply me-1"></i>답글
                    </button>
                </div>
            </div>

            <!-- 답글들 -->
            @if(count($comment->children) > 0)
                <div class="ms-4 mt-3">
                    @foreach($comment->children as $reply)
                        @include('jiny-post::admin.blog.partials.comment', ['comment' => $reply])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>