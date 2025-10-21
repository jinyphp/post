@extends('jiny-site::layouts.app')

@section('title', $category->name . ' - 블로그')

@section('meta')
    @if($category->meta_title)
        <meta name="title" content="{{ $category->meta_title }}">
    @endif
    @if($category->meta_description)
        <meta name="description" content="{{ $category->meta_description }}">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $category->meta_title ?? $category->name . ' - 블로그' }}">
    <meta property="og:description" content="{{ $category->meta_description ?? $category->description }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary">
    <meta property="twitter:title" content="{{ $category->meta_title ?? $category->name . ' - 블로그' }}">
    <meta property="twitter:description" content="{{ $category->meta_description ?? $category->description }}">
@endsection

@section('content')
<!-- Page header -->
<section class="py-8">
    <div class="container">
        <div class="row">
            <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12 col-12">
                <div class="text-center mb-5">
                    <div class="mb-4">
                        @if($category->icon)
                            <i class="{{ $category->icon }} fs-1 mb-3"
                               @if($category->color) style="color: {{ $category->color }};" @endif></i>
                        @endif
                        <h1 class="display-2 fw-bold" @if($category->color) style="color: {{ $category->color }};" @endif>
                            {{ $category->name }}
                        </h1>
                    </div>

                    @if($category->description)
                        <p class="lead">{{ $category->description }}</p>
                    @endif

                    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="bi bi-file-text me-1"></i>
                            {{ $posts->total() }}개의 글
                        </span>
                        @if($category->post_count > 0)
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="bi bi-eye me-1"></i>
                                총 {{ number_format($category->total_views ?? 0) }} 조회
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Search Form -->
                <form method="GET" class="row px-md-8 mx-md-8">
                    <div class="mb-3 col ps-0 ms-2 ms-md-0">
                        <label class="form-label visually-hidden" for="search">검색어</label>
                        <input type="text" class="form-control" placeholder="이 카테고리에서 검색..." id="search" name="search" value="{{ $searchTerm ?? '' }}" />
                    </div>
                    <div class="mb-3 col-auto ps-0">
                        <button class="btn btn-primary" type="submit">검색</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Content -->
<section class="pb-8">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                <!-- Categories Nav -->
                <div class="flush-nav">
                    <nav class="nav">
                        <a class="nav-link ps-0" href="{{ route('blog.index') }}">전체</a>
                        @foreach($categories as $cat)
                            <a class="nav-link {{ $cat->slug == $category->slug ? 'active' : '' }}"
                               href="{{ route('blog.category', $cat->slug) }}">
                                @if($cat->icon)
                                    <i class="{{ $cat->icon }} me-1"></i>
                                @endif
                                {{ $cat->name }}
                                <span class="badge bg-light text-dark ms-1">{{ $cat->post_count ?? 0 }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            @if($posts->count() > 0)
                @php $firstPost = $posts->first(); @endphp

                @if($posts->currentPage() == 1)
                    <!-- Featured Post (only on first page) -->
                    <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                        <div class="card mb-4 shadow-lg card-lift">
                            <div class="row g-0">
                                <!-- Image -->
                                <a href="{{ route('blog.show', $firstPost->slug) }}"
                                   class="col-lg-8 col-md-12 col-12 bg-cover img-left-rounded"
                                   @if($firstPost->featured_image)
                                       style="background-image: url({{ $firstPost->featured_image }})"
                                   @else
                                       style="background-image: url('{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-2.jpg') }}')"
                                   @endif>
                                    @if($firstPost->featured_image)
                                        <img src="{{ $firstPost->featured_image }}" class="img-fluid d-lg-none invisible" alt="{{ $firstPost->title }}" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-2.jpg') }}" class="img-fluid d-lg-none invisible" alt="{{ $firstPost->title }}" />
                                    @endif
                                </a>
                                <div class="col-lg-4 col-md-12 col-12">
                                    <!-- Card body -->
                                    <div class="card-body">
                                        <a href="{{ route('blog.category', $category->slug) }}"
                                           class="fs-5 mb-3 fw-semibold d-block"
                                           @if($category->color) style="color: {{ $category->color }};" @endif>
                                            @if($category->icon)
                                                <i class="{{ $category->icon }} me-1"></i>
                                            @endif
                                            {{ $category->name }}
                                        </a>
                                        <h1 class="mb-2 mb-lg-4">
                                            <a href="{{ route('blog.show', $firstPost->slug) }}" class="text-inherit">
                                                {{ $firstPost->title }}
                                            </a>
                                        </h1>
                                        @if($firstPost->excerpt)
                                            <p>{{ Str::limit($firstPost->excerpt, 120) }}</p>
                                        @endif

                                        <!-- Meta info -->
                                        <div class="row align-items-center g-0 mt-lg-7 mt-4">
                                            <div class="col-auto">
                                                @if(isset($firstPost->author_avatar) && $firstPost->author_avatar)
                                                    <img src="{{ $firstPost->author_avatar }}" alt="{{ $firstPost->author_name }}" class="rounded-circle avatar-sm me-2" />
                                                @else
                                                    <div class="avatar avatar-sm me-2">
                                                        <div class="avatar-initial rounded-circle bg-primary">
                                                            {{ substr($firstPost->author_name ?? 'A', 0, 1) }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col lh-1">
                                                <h5 class="mb-1">{{ $firstPost->author_name ?? '관리자' }}</h5>
                                                <p class="fs-6 mb-0">{{ \Carbon\Carbon::parse($firstPost->published_at)->format('Y년 m월 d일') }}</p>
                                            </div>
                                            <div class="col-auto">
                                                <p class="fs-6 mb-0">
                                                    <i class="bi bi-eye me-1"></i>{{ number_format($firstPost->views) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Posts Grid -->
                    @foreach($posts->skip(1) as $post)
                        <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                            <div class="card mb-4 shadow-lg card-lift">
                                <a href="{{ route('blog.show', $post->slug) }}">
                                    @if($post->featured_image)
                                        <img src="{{ $post->featured_image }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;" />
                                    @endif
                                </a>

                                <!-- Card body -->
                                <div class="card-body">
                                    <a href="{{ route('blog.category', $category->slug) }}"
                                       class="fs-5 mb-2 fw-semibold d-block"
                                       @if($category->color) style="color: {{ $category->color }};" @endif>
                                        @if($category->icon)
                                            <i class="{{ $category->icon }} me-1"></i>
                                        @endif
                                        {{ $category->name }}
                                    </a>

                                    <h3>
                                        <a href="{{ route('blog.show', $post->slug) }}" class="text-inherit">
                                            {{ Str::limit($post->title, 60) }}
                                        </a>
                                    </h3>

                                    @if($post->excerpt)
                                        <p>{{ Str::limit($post->excerpt, 100) }}</p>
                                    @endif

                                    <!-- Tags -->
                                    @if($post->tags)
                                        <div class="mb-3">
                                            @foreach(array_slice(explode(',', $post->tags), 0, 3) as $tag)
                                                <span class="badge bg-light text-dark me-1">#{{ trim($tag) }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Meta content -->
                                    <div class="row align-items-center g-0 mt-4">
                                        <div class="col-auto">
                                            @if(isset($post->author_avatar) && $post->author_avatar)
                                                <img src="{{ $post->author_avatar }}" alt="{{ $post->author_name }}" class="rounded-circle avatar-sm me-2" />
                                            @else
                                                <div class="avatar avatar-sm me-2">
                                                    <div class="avatar-initial rounded-circle bg-primary">
                                                        {{ substr($post->author_name ?? 'A', 0, 1) }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col lh-1">
                                            <h5 class="mb-1">{{ $post->author_name ?? '관리자' }}</h5>
                                            <p class="fs-6 mb-0">{{ \Carbon\Carbon::parse($post->published_at)->format('m월 d일') }}</p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-6 mb-0">
                                                <i class="bi bi-eye me-1"></i>{{ number_format($post->views) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Regular Grid for Other Pages -->
                    @foreach($posts as $post)
                        <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                            <div class="card mb-4 shadow-lg card-lift">
                                <a href="{{ route('blog.show', $post->slug) }}">
                                    @if($post->featured_image)
                                        <img src="{{ $post->featured_image }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;" />
                                    @endif
                                </a>

                                <!-- Card body -->
                                <div class="card-body">
                                    <a href="{{ route('blog.category', $category->slug) }}"
                                       class="fs-5 mb-2 fw-semibold d-block"
                                       @if($category->color) style="color: {{ $category->color }};" @endif>
                                        @if($category->icon)
                                            <i class="{{ $category->icon }} me-1"></i>
                                        @endif
                                        {{ $category->name }}
                                    </a>

                                    <h3>
                                        <a href="{{ route('blog.show', $post->slug) }}" class="text-inherit">
                                            {{ Str::limit($post->title, 60) }}
                                        </a>
                                    </h3>

                                    @if($post->excerpt)
                                        <p>{{ Str::limit($post->excerpt, 100) }}</p>
                                    @endif

                                    <!-- Tags -->
                                    @if($post->tags)
                                        <div class="mb-3">
                                            @foreach(array_slice(explode(',', $post->tags), 0, 3) as $tag)
                                                <span class="badge bg-light text-dark me-1">#{{ trim($tag) }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Meta content -->
                                    <div class="row align-items-center g-0 mt-4">
                                        <div class="col-auto">
                                            @if(isset($post->author_avatar) && $post->author_avatar)
                                                <img src="{{ $post->author_avatar }}" alt="{{ $post->author_name }}" class="rounded-circle avatar-sm me-2" />
                                            @else
                                                <div class="avatar avatar-sm me-2">
                                                    <div class="avatar-initial rounded-circle bg-primary">
                                                        {{ substr($post->author_name ?? 'A', 0, 1) }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col lh-1">
                                            <h5 class="mb-1">{{ $post->author_name ?? '관리자' }}</h5>
                                            <p class="fs-6 mb-0">{{ \Carbon\Carbon::parse($post->published_at)->format('m월 d일') }}</p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-6 mb-0">
                                                <i class="bi bi-eye me-1"></i>{{ number_format($post->views) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @else
                <!-- Empty state -->
                <div class="col-12">
                    <div class="text-center py-8">
                        @if($category->icon)
                            <i class="{{ $category->icon }} fs-1 text-muted d-block mb-3"
                               @if($category->color) style="color: {{ $category->color }};" @endif></i>
                        @else
                            <i class="bi bi-journal-text fs-1 text-muted d-block mb-3"></i>
                        @endif
                        <h3 class="text-muted">{{ $category->name }} 카테고리에 글이 없습니다</h3>
                        @if(isset($searchTerm) && $searchTerm)
                            <p class="text-muted">'{{ $searchTerm }}'에 대한 검색 결과가 없습니다.</p>
                            <a href="{{ route('blog.category', $category->slug) }}" class="btn btn-outline-primary">
                                전체 글 보기
                            </a>
                        @else
                            <p class="text-muted">새로운 글이 곧 업데이트될 예정입니다.</p>
                            <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">
                                다른 카테고리 보기
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            @if(method_exists($posts, 'links'))
                <div class="col-xl-12 col-lg-12 col-md-12 col-12 text-center mt-4">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Latest Posts from Other Categories -->
    @if(isset($latestPosts) && $latestPosts->count() > 0)
        <div class="container mt-8">
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-4">다른 카테고리의 최신 글</h4>
                </div>
                @foreach($latestPosts as $latestPost)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card mb-3 border-0">
                            <a href="{{ route('blog.show', $latestPost->slug) }}">
                                @if(isset($latestPost->featured_image) && $latestPost->featured_image)
                                    <img src="{{ $latestPost->featured_image }}" class="card-img-top rounded" alt="{{ $latestPost->title }}" style="height: 100px; object-fit: cover;" />
                                @else
                                    <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}" class="card-img-top rounded" alt="{{ $latestPost->title }}" style="height: 100px; object-fit: cover;" />
                                @endif
                            </a>
                            <div class="card-body p-2">
                                @if(isset($latestPost->category_name) && $latestPost->category_name)
                                    <small class="badge mb-1"
                                           @if(isset($latestPost->category_color) && $latestPost->category_color)
                                               style="background-color: {{ $latestPost->category_color }}; color: white;"
                                           @else
                                               style="background-color: #6c757d; color: white;"
                                           @endif>
                                        {{ $latestPost->category_name }}
                                    </small>
                                @endif
                                <h6 class="card-title mb-1">
                                    <a href="{{ route('blog.show', $latestPost->slug) }}" class="text-inherit">
                                        {{ Str::limit($latestPost->title, 40) }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($latestPost->published_at)->format('m/d') }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection

@push('styles')
<style>
.card-lift {
    transition: all 0.15s ease;
}

.card-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}

.img-left-rounded {
    border-radius: 0.375rem 0 0 0.375rem;
}

.bg-cover {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 300px;
}

.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: white;
    font-weight: 500;
    font-size: 0.875rem;
}

.flush-nav .nav-link {
    padding: 0.5rem 1rem;
    color: #6c757d;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    position: relative;
}

.flush-nav .nav-link:hover,
.flush-nav .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
}

.flush-nav .nav-link .badge {
    font-size: 0.65rem;
}

.badge {
    font-size: 0.75rem;
}

/* Category header styling */
.display-2 {
    font-size: 3rem;
}

@media (max-width: 767.98px) {
    .display-2 {
        font-size: 2.25rem;
    }
}
</style>
@endpush