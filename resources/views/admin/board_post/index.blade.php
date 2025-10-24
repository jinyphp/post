@extends('jiny-site::layouts.admin.sidebar')

@section('title', 'Board Posts - ' . $board->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3>{{ $board->title }} - Posts</h3>
            <p class="text-muted mb-0">{{ $board->subtitle ?? 'Manage board posts' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cms.board.list.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Boards
            </a>
            <a href="{{ url('/admin/cms/board/posts/' . $code . '/create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> New Post
            </a>
        </div>
    </div>

    <hr>

    <!-- Board Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Posts</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_posts']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Views</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_views']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-eye fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Recent Posts (7 days)</h5>
                            <h3 class="mb-0">{{ number_format($stats['recent_posts']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search title, content, or author..."
                           value="{{ $search }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control"
                           placeholder="Filter by category..."
                           value="{{ $category }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                    <a href="{{ url('/admin/cms/board/posts/' . $code) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2 text-primary"></i>
                Posts List
                @if(method_exists($rows, 'total') ? $rows->total() > 0 : $rows->count() > 0)
                    <span class="badge bg-secondary ms-2">{{ method_exists($rows, 'total') ? $rows->total() : $rows->count() }} posts</span>
                @endif
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Title</th>
                            <th width="150">Author</th>
                            <th width="80" class="text-center">Files</th>
                            <th width="120" class="text-center">Views</th>
                            <th width="100" class="text-center">Likes</th>
                            <th width="150">Created</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $post)
                            <tr>
                                <td>{{ $post->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(isset($post->level) && $post->level > 0)
                                            <span style="margin-left: {{ $post->level * 20 }}px;">
                                                <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                            </span>
                                        @endif

                                        <div>
                                            <a href="{{ url('/admin/cms/board/posts/' . $code . '/' . $post->id . '/edit') }}"
                                               class="text-decoration-none fw-bold">
                                                {{ $post->title }}
                                            </a>

                                            @if(isset($post->level) && $post->level > 0)
                                                <span class="badge bg-success ms-2">Reply Lv.{{ $post->level }}</span>
                                            @endif

                                            @if(isset($childCounts[$post->id]) && $childCounts[$post->id] > 0)
                                                <span class="badge bg-primary ms-2">
                                                    <i class="bi bi-chat-left-text me-1"></i>{{ $childCounts[$post->id] }}
                                                </span>
                                            @endif

                                            @if($post->categories)
                                                <span class="badge bg-info ms-2">{{ $post->categories }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($post->content)
                                        <small class="text-muted d-block mt-1">
                                            {{ Str::limit(strip_tags($post->content), 80) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $post->name ?? 'Anonymous' }}</div>
                                    @if($post->email)
                                        <small class="text-muted">{{ $post->email }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(isset($imageCounts[$post->id]) && $imageCounts[$post->id] > 0)
                                            <span class="badge bg-success" title="이미지 {{ $imageCounts[$post->id] }}개">
                                                <i class="bi bi-image"></i> {{ $imageCounts[$post->id] }}
                                            </span>
                                        @endif
                                        @if(isset($attachmentCounts[$post->id]) && $attachmentCounts[$post->id] > 0)
                                            <span class="badge bg-info" title="첨부파일 {{ $attachmentCounts[$post->id] }}개">
                                                <i class="bi bi-paperclip"></i> {{ $attachmentCounts[$post->id] }}
                                            </span>
                                        @endif
                                        @if((!isset($imageCounts[$post->id]) || $imageCounts[$post->id] == 0) && (!isset($attachmentCounts[$post->id]) || $attachmentCounts[$post->id] == 0))
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ number_format($post->click ?? 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ number_format($post->like ?? 0) }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y H:i') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Reply Button -->
                                        <a href="{{ url('/admin/cms/board/posts/' . $code . '/create?parent=' . $post->id) }}"
                                           class="btn btn-outline-success"
                                           title="Reply">
                                            <i class="bi bi-reply"></i>
                                        </a>

                                        <!-- Edit Button -->
                                        <a href="{{ url('/admin/cms/board/posts/' . $code . '/' . $post->id . '/edit') }}"
                                           class="btn btn-outline-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ url('/admin/cms/board/posts/' . $code . '/' . $post->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No posts found.</p>
                                    <p class="mb-0">
                                        <a href="{{ url('/admin/cms/board/posts/' . $code . '/create') }}"
                                           class="btn btn-primary mt-2">
                                            Create First Post
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(method_exists($rows, 'hasPages') && $rows->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $rows->firstItem() }}-{{ $rows->lastItem() }}
                            of {{ $rows->total() }} posts
                        </div>
                        <div>
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Table hover effect */
.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.02);
    transition: background-color 0.15s ease-in-out;
}

/* Badge styles */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

/* Statistics cards */
.card {
    border-radius: 0.75rem;
}

/* Action buttons */
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush
@endsection