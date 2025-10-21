@extends('jiny-site::layouts.app')

@section('title', '블로그')

@section('content')
    <!-- Page header -->
    <section class="py-8">
        <div class="container">

            {{-- hero --}}
            <div class="row">
                <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12 col-12">
                    <div class="text-center mb-5">
                        <h1 class="display-2 fw-bold">블로그</h1>
                        <p class="lead">최신 기술 동향, 개발 팁, 그리고 다양한 이야기들을 만나보세요.</p>
                    </div>
                    <!-- Search Form -->
                    <form method="GET" class="row px-md-8 mx-md-8">
                        <div class="mb-3 col ps-0 ms-2 ms-md-0">
                            <label class="form-label visually-hidden" for="search">검색어</label>
                            <input type="text" class="form-control" placeholder="검색어를 입력하세요" id="search"
                                name="search" value="{{ $searchTerm ?? '' }}" />
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
                            <a class="nav-link {{ !isset($currentCategory) ? 'active' : '' }} ps-0"
                                href="{{ route('blog.index') }}">전체</a>
                            @foreach ($categories ?? [] as $category)
                                <a class="nav-link {{ isset($currentCategory) && $currentCategory == $category->slug ? 'active' : '' }}"
                                    href="{{ route('blog.category', $category->slug) }}">
                                    @if ($category->icon)
                                        <i class="{{ $category->icon }} me-1"></i>
                                    @endif
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>

                @if (isset($posts) && $posts->count() > 0)
                    @php $firstPost = $posts->first(); @endphp

                    <!-- Featured Post -->
                    <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                        <div class="card mb-4 shadow-lg card-lift">
                            <div class="row g-0">
                                <!-- Image -->
                                <a href="{{ route('blog.show', $firstPost->slug) }}"
                                    class="col-lg-8 col-md-12 col-12 bg-cover img-left-rounded"
                                    @if ($firstPost->featured_image) style="background-image: url({{ $firstPost->featured_image }})"
                               @else
                                   style="background-image: url('{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-2.jpg') }}')" @endif>
                                    @if ($firstPost->featured_image)
                                        <img src="{{ $firstPost->featured_image }}" class="img-fluid d-lg-none invisible"
                                            alt="{{ $firstPost->title }}" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-2.jpg') }}"
                                            class="img-fluid d-lg-none invisible" alt="{{ $firstPost->title }}" />
                                    @endif
                                </a>
                                <div class="col-lg-4 col-md-12 col-12">
                                    <!-- Card body -->
                                    <div class="card-body">
                                        @if ($firstPost->category_name)
                                            <a href="{{ route('blog.category', $firstPost->category_slug) }}"
                                                class="fs-5 mb-3 fw-semibold d-block"
                                                @if ($firstPost->category_color) style="color: {{ $firstPost->category_color }};" @endif>
                                                @if ($firstPost->category_icon)
                                                    <i class="{{ $firstPost->category_icon }} me-1"></i>
                                                @endif
                                                {{ $firstPost->category_name }}
                                            </a>
                                        @endif
                                        <h1 class="mb-2 mb-lg-4">
                                            <a href="{{ route('blog.show', $firstPost->slug) }}" class="text-inherit">
                                                {{ $firstPost->title }}
                                            </a>
                                        </h1>
                                        @if ($firstPost->excerpt)
                                            <p>{{ Str::limit($firstPost->excerpt, 120) }}</p>
                                        @endif

                                        <!-- Meta info -->
                                        <div class="row align-items-center g-0 mt-lg-7 mt-4">
                                            <div class="col-auto">
                                                @if (isset($firstPost->author_avatar) && $firstPost->author_avatar)
                                                    <img src="{{ $firstPost->author_avatar }}"
                                                        alt="{{ $firstPost->author_name }}"
                                                        class="rounded-circle avatar-sm me-2" />
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
                                                <p class="fs-6 mb-0">
                                                    {{ \Carbon\Carbon::parse($firstPost->published_at)->format('Y년 m월 d일') }}
                                                </p>
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
                    @foreach ($posts->skip(1) as $post)
                        <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                            <div class="card mb-4 shadow-lg card-lift">
                                <a href="{{ route('blog.show', $post->slug) }}">
                                    @if ($post->featured_image)
                                        <img src="{{ $post->featured_image }}" class="card-img-top"
                                            alt="{{ $post->title }}" style="height: 200px; object-fit: cover;" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}"
                                            class="card-img-top" alt="{{ $post->title }}"
                                            style="height: 200px; object-fit: cover;" />
                                    @endif
                                </a>

                                <!-- Card body -->
                                <div class="card-body">
                                    @if ($post->category_name)
                                        <a href="{{ route('blog.category', $post->category_slug) }}"
                                            class="fs-5 mb-2 fw-semibold d-block"
                                            @if ($post->category_color) style="color: {{ $post->category_color }};" @endif>
                                            @if ($post->category_icon)
                                                <i class="{{ $post->category_icon }} me-1"></i>
                                            @endif
                                            {{ $post->category_name }}
                                        </a>
                                    @endif

                                    <h3>
                                        <a href="{{ route('blog.show', $post->slug) }}" class="text-inherit">
                                            {{ Str::limit($post->title, 60) }}
                                        </a>
                                    </h3>

                                    @if ($post->excerpt)
                                        <p>{{ Str::limit($post->excerpt, 100) }}</p>
                                    @endif

                                    <!-- Tags -->
                                    @if ($post->tags)
                                        <div class="mb-3">
                                            @foreach (array_slice(explode(',', $post->tags), 0, 3) as $tag)
                                                <span class="badge bg-light text-dark me-1">#{{ trim($tag) }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Meta content -->
                                    <div class="row align-items-center g-0 mt-4">
                                        <div class="col-auto">
                                            @if (isset($post->author_avatar) && $post->author_avatar)
                                                <img src="{{ $post->author_avatar }}" alt="{{ $post->author_name }}"
                                                    class="rounded-circle avatar-sm me-2" />
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
                                            <p class="fs-6 mb-0">
                                                {{ \Carbon\Carbon::parse($post->published_at)->format('m월 d일') }}</p>
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
                    <!-- Empty state -->
                    <div class="col-12">
                        <div class="text-center py-8">
                            <i class="bi bi-journal-text fs-1 text-muted d-block mb-3"></i>
                            <h3 class="text-muted">등록된 블로그 글이 없습니다</h3>
                            <p class="text-muted">새로운 글이 곧 업데이트될 예정입니다.</p>
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                @if (isset($posts) && method_exists($posts, 'links'))
                    <div class="col-xl-12 col-lg-12 col-md-12 col-12 text-center mt-4">
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="pb-8">
        <!-- Sidebar with Latest Posts -->
        @if (isset($latestPosts) && $latestPosts->count() > 0)
            <div class="container mt-8">
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-4">최신 글</h4>
                    </div>
                    @foreach ($latestPosts as $latestPost)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                            <div class="card mb-3 border-0">
                                <a href="{{ route('blog.show', $latestPost->slug) }}">
                                    @if (isset($latestPost->featured_image) && $latestPost->featured_image)
                                        <img src="{{ $latestPost->featured_image }}" class="card-img-top rounded"
                                            alt="{{ $latestPost->title }}" style="height: 100px; object-fit: cover;" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}"
                                            class="card-img-top rounded" alt="{{ $latestPost->title }}"
                                            style="height: 100px; object-fit: cover;" />
                                    @endif
                                </a>
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1">
                                        <a href="{{ route('blog.show', $latestPost->slug) }}" class="text-inherit">
                                            {{ Str::limit($latestPost->title, 40) }}
                                        </a>
                                    </h6>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($latestPost->published_at)->format('m/d') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="pb-8">
        <!-- Popular Posts -->
        @if (isset($popularPosts) && $popularPosts->count() > 0)
            <div class="container mt-6">
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-4">인기 글</h4>
                    </div>
                    @foreach ($popularPosts as $popularPost)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                            <div class="card mb-3 border-0">
                                <a href="{{ route('blog.show', $popularPost->slug) }}">
                                    @if (isset($popularPost->featured_image) && $popularPost->featured_image)
                                        <img src="{{ $popularPost->featured_image }}" class="card-img-top rounded"
                                            alt="{{ $popularPost->title }}" style="height: 100px; object-fit: cover;" />
                                    @else
                                        <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}"
                                            class="card-img-top rounded" alt="{{ $popularPost->title }}"
                                            style="height: 100px; object-fit: cover;" />
                                    @endif
                                </a>
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1">
                                        <a href="{{ route('blog.show', $popularPost->slug) }}" class="text-inherit">
                                            {{ Str::limit($popularPost->title, 40) }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-eye me-1"></i>{{ number_format($popularPost->views) }}
                                    </small>
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
        }

        .flush-nav .nav-link:hover,
        .flush-nav .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
        }

        .badge {
            font-size: 0.75rem;
        }
    </style>
@endpush
