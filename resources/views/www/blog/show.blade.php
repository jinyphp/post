@extends('jiny-site::layouts.app')

@section('title', $post->title)

@section('meta')
    @if ($post->meta_title)
        <meta name="title" content="{{ $post->meta_title }}">
    @endif
    @if ($post->meta_description)
        <meta name="description" content="{{ $post->meta_description }}">
    @endif
    @if ($post->tags)
        <meta name="keywords" content="{{ $post->tags }}">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description ?? $post->excerpt }}">
    @if ($post->featured_image)
        <meta property="og:image" content="{{ $post->featured_image }}">
    @endif

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="twitter:description" content="{{ $post->meta_description ?? $post->excerpt }}">
    @if ($post->featured_image)
        <meta property="twitter:image" content="{{ $post->featured_image }}">
    @endif
@endsection

@section('content')
    <!-- Content -->
    <section class="py-7 py-lg-8">
        <div class="container">
            <div class="row justify-content-center">
                <div>
                    <div class="text-center mb-4">
                        @if ($post->category_name)
                            <a href="{{ route('blog.category', $post->category_slug) }}"
                                class="fs-5 fw-semibold d-block mb-4"
                                @if ($post->category_color) style="color: {{ $post->category_color }};" @endif>
                                @if ($post->category_icon)
                                    <i class="{{ $post->category_icon }} me-2"></i>
                                @endif
                                {{ $post->category_name }}
                            </a>
                        @endif
                        <h1 class="display-3 fw-bold mb-4">{{ $post->title }}</h1>

                        @if ($post->excerpt)
                            <p class="lead text-muted mb-4">{{ $post->excerpt }}</p>
                        @endif

                        <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                            <span class="d-inline-block">
                                <i class="bi bi-clock me-1"></i>
                                {{ ceil(str_word_count(strip_tags($post->content)) / 200) }}분 읽기
                            </span>
                            <span class="d-inline-block">
                                <i class="bi bi-eye me-1"></i>
                                {{ number_format($post->views) }} 조회
                            </span>
                            @if ($post->likes > 0)
                                <span class="d-inline-block">
                                    <i class="bi bi-heart me-1"></i>
                                    {{ number_format($post->likes) }} 좋아요
                                </span>
                            @endif
                        </div>

                        @if ($post->tags)
                            <div class="mb-4">
                                @foreach (explode(',', $post->tags) as $tag)
                                    <span class="badge bg-light text-dark me-2 mb-1">#{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Author and Date Info -->
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div class="d-flex align-items-center">
                            @if (isset($post->author_avatar) && $post->author_avatar)
                                <img src="{{ $post->author_avatar }}" alt="{{ $post->author_name }}"
                                    class="rounded-circle avatar-md" />
                            @else
                                <div class="avatar avatar-md me-3">
                                    <div class="avatar-initial rounded-circle bg-primary">
                                        {{ substr($post->author_name ?? 'A', 0, 1) }}
                                    </div>
                                </div>
                            @endif
                            <div class="ms-2 lh-1">
                                <h5 class="mb-1">{{ $post->author_name ?? '관리자' }}</h5>
                                <span
                                    class="text-primary">{{ \Carbon\Carbon::parse($post->published_at)->format('Y년 m월 d일') }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="ms-2">공유</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                                target="_blank" class="ms-2 text-muted" title="Facebook에 공유">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}"
                                target="_blank" class="ms-2 text-muted" title="Twitter에 공유">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->fullUrl()) }}"
                                target="_blank" class="ms-2 text-muted" title="LinkedIn에 공유">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <button class="btn btn-link text-muted p-0 ms-2" onclick="copyToClipboard()" title="링크 복사">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="row justify-content-center">
                <div>
                    @if ($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}"
                            class="img-fluid rounded-3 w-100" />
                    @else
                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-2.jpg') }}"
                            alt="{{ $post->title }}" class="img-fluid rounded-3 w-100" />
                    @endif
                </div>
            </div>

            <!-- Multi Images Gallery -->
            @if(!empty($blogImages) && count($blogImages) > 0)
                <div class="row justify-content-center mt-4">
                    <div class="blog-images-gallery">
                        <h5 class="mb-3 text-center">
                            <i class="bi bi-images me-2 text-primary"></i>첨부 이미지 ({{ count($blogImages) }}개)
                        </h5>
                        <div class="row g-4">
                            @foreach($blogImages as $image)
                                <div class="col-lg-4 col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="overflow-hidden" style="height: 250px;">
                                            <img src="{{ $image->url }}"
                                                 alt="{{ $image->original_name }}"
                                                 class="card-img-top w-100 h-100"
                                                 style="object-fit: cover; cursor: pointer;"
                                                 onclick="openImageModal('{{ $image->url }}', '{{ $image->original_name }}')">
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title text-truncate mb-2" title="{{ $image->original_name }}">
                                                {{ $image->original_name }}
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-file-earmark me-1"></i>{{ number_format($image->size / 1024, 1) }} KB
                                                </small>
                                                <small class="text-primary">
                                                    <i class="bi bi-eye me-1"></i>확대보기
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Content -->
            <div class="row justify-content-center">
                <div>
                    <div class="blog-content">
                        {!! $post->content !!}
                    </div>

                    <!-- Article Actions -->
                    <div class="row align-items-center mt-8 py-4 border-top border-bottom">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-primary me-3" onclick="toggleLike()">
                                    <i class="bi bi-heart me-1"></i>
                                    <span id="likeCount">{{ number_format($post->likes) }}</span>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="copyToClipboard()">
                                    <i class="bi bi-share me-1"></i>
                                    공유하기
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <small class="text-muted">
                                최종 수정: {{ \Carbon\Carbon::parse($post->updated_at)->format('Y년 m월 d일') }}
                            </small>
                        </div>
                    </div>

                    <!-- Author Bio -->
                    @if ($post->author_name && $post->author_bio)
                        <div class="card mt-6">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    @if (isset($post->author_avatar) && $post->author_avatar)
                                        <img src="{{ $post->author_avatar }}" alt="{{ $post->author_name }}"
                                            class="rounded-circle avatar-lg me-3" />
                                    @else
                                        <div class="avatar avatar-lg me-3">
                                            <div class="avatar-initial rounded-circle bg-primary">
                                                {{ substr($post->author_name, 0, 1) }}
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <h5 class="mb-1">{{ $post->author_name }}</h5>
                                        <p class="text-muted mb-2">{{ $post->author_bio }}</p>
                                        @if ($post->author_website)
                                            <a href="{{ $post->author_website }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                웹사이트 방문
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Navigation -->
            @if ($prevPost || $nextPost)
                <div class="row justify-content-center mt-8">
                    <div>
                        <div class="row">
                            @if ($prevPost)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <span class="text-muted small">이전 글</span>
                                            <h6 class="mt-2">
                                                <a href="{{ route('blog.show', $prevPost->slug) }}" class="text-inherit">
                                                    {{ Str::limit($prevPost->title, 60) }}
                                                </a>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($nextPost)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <span class="text-muted small">다음 글</span>
                                            <h6 class="mt-2">
                                                <a href="{{ route('blog.show', $nextPost->slug) }}" class="text-inherit">
                                                    {{ Str::limit($nextPost->title, 60) }}
                                                </a>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Related Posts -->
            @includeIf('jiny-post::www.blog.related')


            <!-- Comments Section -->
            @includeIf('jiny-post::www.blog.comment')


            <!-- Back to Blog -->
            <div class="row justify-content-center mt-8">
                <div class="col-xl-8 col-lg-8 col-md-12 col-12 text-center">
                    <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        블로그 목록으로 돌아가기
                    </a>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('styles')
    <style>
        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-md {
            width: 3rem;
            height: 3rem;
        }

        .avatar-lg {
            width: 4rem;
            height: 4rem;
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: white;
            font-weight: 500;
            font-size: 1.125rem;
        }

        .card-lift {
            transition: all 0.15s ease;
        }

        .card-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .blog-content {
            font-size: 1.125rem;
            line-height: 1.8;
        }

        .blog-content h1,
        .blog-content h2,
        .blog-content h3,
        .blog-content h4,
        .blog-content h5,
        .blog-content h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .blog-content p {
            margin-bottom: 1.5rem;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }

        .blog-content blockquote {
            margin: 2rem 0;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            border-radius: 0.375rem;
        }

        .blog-content code {
            background-color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        .blog-content pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            margin: 1.5rem 0;
        }

        .blog-content ul,
        .blog-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .blog-content li {
            margin-bottom: 0.5rem;
        }

        .blog-content table {
            width: 100%;
            margin: 1.5rem 0;
            border-collapse: collapse;
        }

        .blog-content table th,
        .blog-content table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .blog-content table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        /* Badge styles */
        .badge {
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Comment Section Styles */
        .comment {
            position: relative;
        }

        .comment .bg-light {
            background-color: #f8f9fa !important;
        }

        .comment .reply-btn {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.15s ease-in-out;
        }

        .comment .reply-btn:hover {
            color: #0d6efd;
        }

        .comment .avatar-initial {
            font-size: 1rem;
            font-weight: 600;
        }

        .comment .children-comments {
            border-left: 2px solid #e9ecef;
            margin-left: 1rem;
            padding-left: 1rem;
        }

        .comment-form-section {
            background-color: #fff;
            border-radius: 0.375rem;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Image Gallery Styles */
        .blog-images-gallery .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .blog-images-gallery .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }

        .blog-images-gallery .card img {
            transition: transform 0.3s ease;
        }

        .blog-images-gallery .card:hover img {
            transform: scale(1.05);
        }

        .blog-images-gallery .card-body {
            background-color: rgba(248, 249, 250, 0.8);
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        /* Badge styles for member indicator */
        .badge.bg-info {
            background-color: #0dcaf0 !important;
        }

        /* Loading spinner styles */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Toast container positioning */
        .toast-container {
            z-index: 1055;
        }

        /* Form validation styles */
        .was-validated .form-control:valid {
            border-color: #198754;
        }

        .was-validated .form-control:invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .was-validated .form-control:invalid~.invalid-feedback {
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .blog-content {
                font-size: 1rem;
            }

            .display-3 {
                font-size: 2.5rem;
            }

            .comment .ms-4 {
                margin-left: 1rem !important;
            }

            .comment .children-comments {
                margin-left: 0.5rem;
                padding-left: 0.5rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                // Show toast or alert
                if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                    const toastHtml = `
                <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            링크가 클립보드에 복사되었습니다!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;

                    const toastContainer = document.querySelector('.toast-container') || document.body;
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = toastHtml;
                    const toast = tempDiv.firstElementChild;
                    toastContainer.appendChild(toast);

                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.show();

                    toast.addEventListener('hidden.bs.toast', function() {
                        toast.remove();
                    });
                } else {
                    alert('링크가 클립보드에 복사되었습니다!');
                }
            }).catch(function() {
                alert('링크 복사에 실패했습니다.');
            });
        }

        function toggleLike() {
            const postId = {{ $post->id }};

            fetch(`/blog/{{ $post->slug }}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('likeCount').textContent = data.likes.toLocaleString();

                        // Show feedback
                        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                            const message = data.liked ? '좋아요를 추가했습니다!' : '좋아요를 취소했습니다!';
                            const toastHtml = `
                    <div class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>`;

                            const toastContainer = document.querySelector('.toast-container') || document.body;
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = toastHtml;
                            const toast = tempDiv.firstElementChild;
                            toastContainer.appendChild(toast);

                            const bsToast = new bootstrap.Toast(toast);
                            bsToast.show();

                            toast.addEventListener('hidden.bs.toast', function() {
                                toast.remove();
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Comment functionality
        @if ($post->allow_comments)
            document.addEventListener('DOMContentLoaded', function() {
                const commentForm = document.getElementById('commentForm');
                const parentIdInput = document.getElementById('parent_id');
                const formTitle = document.getElementById('comment-form-title');

                // Comment form submission
                commentForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Validate form
                    if (!commentForm.checkValidity()) {
                        e.stopPropagation();
                        commentForm.classList.add('was-validated');
                        return;
                    }

                    const submitButton = document.getElementById('submitComment');
                    const originalText = submitButton.innerHTML;

                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>전송 중...';

                    // 폼 데이터 수집
                    const formData = {
                        blog_id: document.querySelector('input[name="blog_id"]').value,
                        parent_id: document.querySelector('input[name="parent_id"]').value,
                        author_name: document.querySelector('input[name="author_name"]').value,
                        author_email: document.querySelector('input[name="author_email"]').value,
                        author_website: document.querySelector('input[name="author_website"]').value,
                        content: document.querySelector('textarea[name="content"]').value,
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    };

                    console.log('Form data being sent:', formData);

                    // 필수 필드 검증
                    if (!formData._token) {
                        showToast('CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.', 'danger');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        return;
                    }

                    fetch('/blog/comment', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': formData._token
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            console.log('Response headers:', response.headers);

                            // 응답이 JSON인지 확인
                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                return response.text().then(text => {
                                    console.log('Non-JSON response text:', text);
                                    throw new Error(
                                        `서버에서 JSON이 아닌 응답을 받았습니다. Status: ${response.status}, Content: ${text.substring(0, 200)}`
                                        );
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                showToast(data.message, 'success');

                                // Reset form
                                commentForm.reset();
                                commentForm.classList.remove('was-validated');
                                resetReplyForm();

                                // If it's a new top-level comment, you might want to reload
                                // the comments section or append the new comment
                                if (!parentIdInput.value) {
                                    showToast('댓글이 성공적으로 등록되었습니다. 관리자 승인 후 표시됩니다.', 'info');
                                }
                            } else {
                                showToast(data.message || '댓글 등록에 실패했습니다.', 'danger');
                                if (data.errors) {
                                    console.error('Validation errors:', data.errors);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast(`오류: ${error.message}`, 'danger');
                        })
                        .finally(() => {
                            // Reset button state
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalText;
                        });
                });

                // Reply button functionality
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('reply-btn') || e.target.closest('.reply-btn')) {
                        e.preventDefault();

                        const replyBtn = e.target.classList.contains('reply-btn') ? e.target : e.target
                            .closest('.reply-btn');
                        const commentId = replyBtn.getAttribute('data-comment-id');
                        const authorName = replyBtn.getAttribute('data-author');

                        // Set parent ID
                        parentIdInput.value = commentId;

                        // Update form title
                        formTitle.textContent = `${authorName}님에게 답글 작성`;

                        // Add cancel button if not exists
                        let cancelBtn = document.getElementById('cancelReply');
                        if (!cancelBtn) {
                            cancelBtn = document.createElement('button');
                            cancelBtn.type = 'button';
                            cancelBtn.id = 'cancelReply';
                            cancelBtn.className = 'btn btn-outline-secondary me-2';
                            cancelBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>취소';

                            const submitBtn = document.getElementById('submitComment');
                            submitBtn.parentNode.insertBefore(cancelBtn, submitBtn);

                            // Cancel reply functionality
                            cancelBtn.addEventListener('click', resetReplyForm);
                        }

                        // Scroll to form
                        document.getElementById('comment-form-section').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        // Focus on content textarea
                        document.getElementById('content').focus();
                    }
                });

                // Reset reply form function
                function resetReplyForm() {
                    parentIdInput.value = '';
                    formTitle.textContent = '댓글 작성';

                    const cancelBtn = document.getElementById('cancelReply');
                    if (cancelBtn) {
                        cancelBtn.remove();
                    }
                }

                // Toast notification function
                function showToast(message, type = 'primary') {
                    const bgClass = type === 'success' ? 'bg-success' :
                        type === 'danger' ? 'bg-danger' :
                        type === 'info' ? 'bg-info' : 'bg-primary';

                    const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

                    let toastContainer = document.querySelector('.toast-container');
                    if (!toastContainer) {
                        toastContainer = document.createElement('div');
                        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                        toastContainer.style.zIndex = '1050';
                        document.body.appendChild(toastContainer);
                    }

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = toastHtml;
                    const toast = tempDiv.firstElementChild;
                    toastContainer.appendChild(toast);

                    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                        const bsToast = new bootstrap.Toast(toast);
                        bsToast.show();

                        toast.addEventListener('hidden.bs.toast', function() {
                            toast.remove();
                            if (toastContainer.children.length === 0) {
                                toastContainer.remove();
                            }
                        });
                    } else {
                        // Fallback for when Bootstrap Toast is not available
                        setTimeout(() => {
                            toast.remove();
                            if (toastContainer.children.length === 0) {
                                toastContainer.remove();
                            }
                        }, 5000);
                    }
                }
            });
        @endif

        // Image Modal Functions
        function openImageModal(imageUrl, imageName) {
            // Check if modal exists, if not create it
            let modal = document.getElementById('imageModal');
            if (!modal) {
                createImageModal();
                modal = document.getElementById('imageModal');
            }

            // Set modal content
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalImageTitle').textContent = imageName;

            // Show modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }

        function createImageModal() {
            const modalHTML = `
                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalImageTitle">이미지</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center p-0">
                                <img id="modalImage" class="img-fluid w-100" alt="확대 이미지">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
    </script>
@endpush
