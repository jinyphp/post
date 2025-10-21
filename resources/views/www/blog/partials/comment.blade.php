<div class="comment mb-4" data-comment-id="{{ $comment->id }}">
    <div class="d-flex">
        <!-- Avatar -->
        <div class="me-3">
            <div class="avatar avatar-md bg-primary rounded-circle">
                <span class="avatar-initial">
                    {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                </span>
            </div>
        </div>

        <!-- Comment Content -->
        <div class="flex-grow-1">
            <div class="bg-light rounded p-3 position-relative">
                <!-- Comment Header -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1 text-dark fw-semibold">
                            {{ $comment->author_name }}
                            @if($comment->user_name)
                                <span class="badge bg-info ms-2">회원</span>
                            @endif
                        </h6>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="bi bi-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                            @if($comment->author_website)
                                <span class="mx-2">•</span>
                                <a href="{{ $comment->author_website }}" target="_blank" class="text-decoration-none text-muted">
                                    <i class="bi bi-globe me-1"></i>웹사이트
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Comment Content -->
                <div class="text-dark">
                    {{ $comment->content }}
                </div>

                <!-- Comment Actions -->
                <div class="mt-2">
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted reply-btn"
                            data-comment-id="{{ $comment->id }}" data-author="{{ $comment->author_name }}">
                        <i class="bi bi-reply me-1"></i>답글
                    </button>
                </div>
            </div>

            <!-- Replies -->
            @if(isset($comment->children) && count($comment->children) > 0)
                <div class="ms-4 mt-3">
                    @foreach($comment->children as $reply)
                        @include('jiny-post::www.blog.partials.comment', ['comment' => $reply])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>