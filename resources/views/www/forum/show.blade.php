@extends('jiny-site::layouts.app')

@section('content')
    <div class="container py-5">

        <div class="row">
            <div class="col-12" style="padding-left: 0; padding-right: 0;">

                <!-- 포럼 헤더 -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="{{ route('forum.index') }}" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left"></i> 포럼 목록
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list"></i> 목록으로
                        </a>
                    </div>
                </div>

                <!-- 포럼 글 헤더 -->
                <section class="mb-4">
                    <div class="d-flex align-items-center flex-wrap mb-2">
                        <h2 class="d-inline-block me-3 mb-0">{{ $post->title }}</h2>
                        <!-- 카테고리 표시 -->
                        @if (!empty($categories))
                            @foreach ($categories as $category)
                                <span class="badge bg-secondary me-1">{{ $category }}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted">
                        <div class="small">
                            <span class="me-3">
                                <i class="bi bi-person"></i> {{ $post->name ?? '익명' }}
                            </span>
                            <span class="me-3">
                                <i class="bi bi-calendar"></i>
                                {{ \Carbon\Carbon::parse($post->created_at)->format('Y-m-d H:i') }}
                            </span>
                            <span class="me-3">
                                <i class="bi bi-eye"></i> {{ number_format($post->click ?? 0) }}
                            </span>
                            <span>
                                <i class="bi bi-heart-fill text-danger"></i> {{ number_format($post->like ?? 0) }}
                            </span>
                        </div>
                        <div>
                            <button class="btn {{ $userLiked ? 'btn-danger' : 'btn-outline-danger' }} btn-sm"
                                onclick="toggleLike({{ $post->id }})">
                                <i class="bi {{ $userLiked ? 'bi-heart-fill' : 'bi-heart' }}" id="likeIcon"></i>
                                <span id="likeCount">{{ $post->like ?? 0 }}</span>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- 알림 메시지 -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- 포럼 글 내용 -->
                <section>
                    <!-- 대표 이미지 -->
                    @if ($post->image)
                        <div class="mb-3 text-center">
                            <img src="{{ $post->image }}" alt="포럼 이미지" class="img-fluid rounded"
                                style="max-height: 400px;">
                        </div>
                    @endif

                    <!-- 글 내용 -->
                    <div class="content">
                        {!! nl2br(e($post->content)) !!}
                    </div>

                    <!-- 태그 표시 -->
                    @if (!empty($tags))
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="text-muted mb-2">태그</h6>
                            @foreach ($tags as $tag)
                                <a href="{{ route('forum.index', ['search' => $tag]) }}"
                                    class="badge bg-light text-dark text-decoration-none me-1">#{{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- 이전글/다음글 네비게이션 -->
                <section class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1 me-3">
                            @if ($prevPost)
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-chevron-left"></i> 이전글
                                </div>
                                <a href="{{ route('forum.show', $prevPost->id) }}"
                                    class="text-decoration-none text-truncate d-block">
                                    {{ $prevPost->title }}
                                </a>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-chevron-left"></i> 이전글이 없습니다
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1 ms-3 text-end">
                            @if ($nextPost)
                                <div class="text-muted small mb-1">
                                    다음글 <i class="bi bi-chevron-right"></i>
                                </div>
                                <a href="{{ route('forum.show', $nextPost->id) }}"
                                    class="text-decoration-none text-truncate d-block">
                                    {{ $nextPost->title }}
                                </a>
                            @else
                                <div class="text-muted">
                                    다음글이 없습니다 <i class="bi bi-chevron-right"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

            </div>
        </div>

        {{-- 관련글 --}}
        @includeIf('jiny-post::www.forum.related')

    </div>

    <script>
        function toggleLike(postId) {
            const likeIcon = document.getElementById('likeIcon');
            const likeCount = document.getElementById('likeCount');
            const button = likeIcon.closest('button');

            // 버튼 비활성화
            button.disabled = true;

            fetch(`/forum/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 아이콘 변경
                        if (data.liked) {
                            likeIcon.className = 'bi bi-heart-fill';
                            button.classList.remove('btn-outline-danger');
                            button.classList.add('btn-danger');
                        } else {
                            likeIcon.className = 'bi bi-heart';
                            button.classList.remove('btn-danger');
                            button.classList.add('btn-outline-danger');
                        }

                        // 좋아요 수 업데이트
                        likeCount.textContent = data.likeCount;

                        // 성공 메시지 (선택사항)
                        // alert(data.message);
                    } else {
                        alert(data.message || '좋아요 처리 중 오류가 발생했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('서버 통신 중 오류가 발생했습니다.');
                })
                .finally(() => {
                    // 버튼 다시 활성화
                    button.disabled = false;
                });
        }

        // 페이지 로드 시 CSRF 토큰 설정
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF 토큰이 없으면 meta 태그 추가
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const csrfMeta = document.createElement('meta');
                csrfMeta.name = 'csrf-token';
                csrfMeta.content = '{{ csrf_token() }}';
                document.head.appendChild(csrfMeta);
            }
        });
    </script>
@endsection
