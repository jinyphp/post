@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '포럼 목록')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3>{{ $config['title'] ?? '포럼 목록' }}</h3>
            <p class="text-muted mb-0">{{ $config['subtitle'] ?? '' }}</p>
        </div>
        <div>
            <a href="{{ route('admin.cms.forum.index.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> 신규 등록
            </a>
        </div>
    </div>

    <hr>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-chat-square-text me-2 text-primary"></i>
                        포럼 목록
                    </h5>
                    <p class="text-muted mb-0 small">등록된 포럼 글을 관리합니다.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- 검색 폼 -->
                    <form method="GET" class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="제목, 내용, 작성자로 검색..."
                                   value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- 필터 드롭다운 -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i>필터
                        </button>
                        <ul class="dropdown-menu">
                            @foreach($categories ?? [] as $category)
                                <li>
                                    <a class="dropdown-item" href="?category={{ $category->slug }}">
                                        <span class="badge me-2" style="background-color: {{ $category->color }};">
                                            @if($category->icon)
                                                <i class="{{ $category->icon }}"></i>
                                            @else
                                                ●
                                            @endif
                                        </span>
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                            @if(count($categories ?? []) > 0)
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('admin.cms.forum.index') }}">전체 보기</a></li>
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
                            <th class="ps-4">제목</th>
                            <th width='150'>작성자</th>
                            <th width='80' class="text-center">이미지</th>
                            <th width='100' class="text-center">조회수</th>
                            <th width='100' class="text-center">좋아요</th>
                            <th width='150'>등록일자</th>
                            <th width='120' class="text-center pe-4">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $item)
                            <tr>
                                <td class="ps-4">
                                    <div>
                                        <strong class="text-dark">{{$item->title}}</strong>
                                        @if($item->categories)
                                            @php
                                                $category = collect($categories ?? [])->firstWhere('slug', $item->categories);
                                            @endphp
                                            @if($category)
                                                <span class="badge ms-2" style="background-color: {{ $category->color }}; color: white;">
                                                    @if($category->icon)
                                                        <i class="{{ $category->icon }} me-1"></i>
                                                    @endif
                                                    {{ $category->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-info ms-2">{{$item->categories}}</span>
                                            @endif
                                        @endif
                                    </div>
                                    @if($item->content)
                                        <div class="text-muted small mt-1">
                                            {{ Str::limit($item->content, 80) }}
                                        </div>
                                    @endif
                                </td>
                                <td width='150'>
                                    <div class="text-muted">{{$item->name ?? '-'}}</div>
                                    @if($item->email)
                                        <div class="text-muted small">{{$item->email}}</div>
                                    @endif
                                </td>
                                <td width='80' class="text-center">
                                    @if(($item->image_count ?? 0) > 0)
                                        <span class="badge bg-success">
                                            <i class="bi bi-images me-1"></i>{{ $item->image_count }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td width='100' class="text-center">
                                    <span class="badge bg-secondary">{{number_format($item->click ?? 0)}}</span>
                                </td>
                                <td width='100' class="text-center">
                                    <span class="badge bg-danger">{{number_format($item->like ?? 0)}}</span>
                                </td>
                                <td width='150'>
                                    <small class="text-muted">{{$item->created_at}}</small>
                                </td>
                                <td width='120' class="text-center pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.cms.forum.index.edit', $item->id) }}"
                                           class="btn btn-outline-primary" title="수정">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.cms.forum.index.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('정말 삭제하시겠습니까?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="삭제">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square fs-1 d-block mb-2"></i>
                                    <p class="mb-0">등록된 포럼 글이 없습니다.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($rows, 'links'))
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            총 {{ $rows->total() }}개의 포럼 글이 있습니다.
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
/* 테이블 행 호버 효과 */
.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.02);
    transition: background-color 0.15s ease-in-out;
}

/* 카드 헤더 스타일 */
.card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

/* 검색 입력 필드 스타일 */
.input-group-sm .form-control {
    border-radius: 0.375rem 0 0 0.375rem;
}

.input-group-sm .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

/* 배지 스타일 개선 */
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

/* 액션 버튼 개선 */
.btn-group-sm .btn {
    padding: 0.375rem 0.5rem;
}

/* 드롭다운 메뉴 */
.dropdown-menu {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.375rem;
}

/* 페이지네이션 스타일 */
.card-footer {
    padding: 1rem 1.5rem;
}
</style>
@endpush
@endsection