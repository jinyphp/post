@extends($layout ?? 'jiny-site::layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- 게시판 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">{{ $board->title }}</h2>
                    @if($board->subtitle)
                        <p class="text-muted mb-0">{{ $board->subtitle }}</p>
                    @endif
                </div>
                @if($canCreate)
                    <a href="{{ route('board.create', $code) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> 글쓰기
                    </a>
                @endif
            </div>

            <!-- 검색 폼 -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('board.index', $code) }}" class="row g-3" id="searchForm">
                        <div class="col-md-2">
                            <select name="perPage" class="form-select" id="perPageSelect">
                                <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5개씩 보기</option>
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10개씩 보기</option>
                                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20개씩 보기</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50개씩 보기</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100개씩 보기</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control"
                                   placeholder="제목, 내용, 작성자 검색"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search"></i> 검색
                            </button>
                        </div>
                        <div class="col-md-2">
                            @if(request('search'))
                                <a href="{{ route('board.index', $code) }}?perPage={{ $perPage }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-x-circle"></i> 초기화
                                </a>
                            @else
                                <button type="reset" class="btn btn-outline-secondary w-100" disabled>
                                    <i class="bi bi-x-circle"></i> 초기화
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // perPage 선택 시 자동 제출
                document.getElementById('perPageSelect').addEventListener('change', function() {
                    document.getElementById('searchForm').submit();
                });
            </script>

            <!-- 알림 메시지 -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- 게시글 목록 -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">번호</th>
                                    <th>제목</th>
                                    <th style="width: 120px;">작성자</th>
                                    <th style="width: 100px;">조회수</th>
                                    <th style="width: 140px;">평가</th>
                                    <th style="width: 150px;">작성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="text-center">
                                            @if(isset($row->level) && $row->level > 0)
                                                <small class="text-muted">{{ $row->id }}</small>
                                            @else
                                                {{ $row->id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($row->level) && $row->level > 0)
                                                <div style="margin-left: {{ $row->level * 20 }}px;">
                                                    <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                                    <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                       class="text-decoration-none text-secondary">
                                                        {{ $row->title }}
                                                    </a>
                                                    <span class="badge bg-success ms-1">
                                                        하위글 Lv{{ $row->level }}
                                                    </span>
                                                    @if(isset($imageCounts[$row->id]) && $imageCounts[$row->id] > 0)
                                                        <span class="badge bg-info ms-1" title="이미지 {{ $imageCounts[$row->id] }}개">🖼️ {{ $imageCounts[$row->id] }}</span>
                                                    @endif
                                                    @if(isset($attachmentCounts[$row->id]) && $attachmentCounts[$row->id] > 0)
                                                        <span class="badge bg-warning ms-1" title="첨부파일 {{ $attachmentCounts[$row->id] }}개">📎 {{ $attachmentCounts[$row->id] }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                   class="text-decoration-none text-dark">
                                                    {{ $row->title }}
                                                </a>
                                                @if(isset($childCounts[$row->id]) && $childCounts[$row->id] > 0)
                                                    <span class="badge bg-primary ms-1" title="답글">{{ $childCounts[$row->id] }}</span>
                                                @endif
                                                @if(isset($commentCounts[$row->id]) && $commentCounts[$row->id] > 0)
                                                    <span class="badge bg-success ms-1" title="코멘트">💬 {{ $commentCounts[$row->id] }}</span>
                                                @endif
                                                @if(isset($imageCounts[$row->id]) && $imageCounts[$row->id] > 0)
                                                    <span class="badge bg-info ms-1" title="이미지 {{ $imageCounts[$row->id] }}개">🖼️ {{ $imageCounts[$row->id] }}</span>
                                                @endif
                                                @if(isset($attachmentCounts[$row->id]) && $attachmentCounts[$row->id] > 0)
                                                    <span class="badge bg-warning ms-1" title="첨부파일 {{ $attachmentCounts[$row->id] }}개">📎 {{ $attachmentCounts[$row->id] }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $row->name ?? '익명' }}</td>
                                        <td class="text-center">{{ $row->click ?? 0 }}</td>
                                        <td class="text-center">
                                            <!-- 모든 게시글(원본글/하위글)에 대해 평가 표시 -->
                                            @if(isset($ratingCounts[$row->id]))
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <!-- 좋아요 -->
                                                    @if($ratingCounts[$row->id]['like_count'] > 0)
                                                        <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                           class="text-decoration-none text-danger"
                                                           title="좋아요 {{ $ratingCounts[$row->id]['like_count'] }}개">
                                                            <i class="bi bi-heart-fill"></i>
                                                            <small>{{ $ratingCounts[$row->id]['like_count'] }}</small>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                           class="text-decoration-none text-muted"
                                                           title="좋아요 0개">
                                                            <i class="bi bi-heart"></i>
                                                            <small>0</small>
                                                        </a>
                                                    @endif

                                                    <!-- 별점 -->
                                                    @if($ratingCounts[$row->id]['rating_average'] > 0)
                                                        <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                           class="text-decoration-none text-warning"
                                                           title="평균 별점 {{ $ratingCounts[$row->id]['rating_average'] }}점 ({{ $ratingCounts[$row->id]['rating_count'] }}명 평가)">
                                                            <i class="bi bi-star-fill"></i>
                                                            <small>{{ $ratingCounts[$row->id]['rating_average'] }}</small>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                           class="text-decoration-none text-muted"
                                                           title="평점 없음">
                                                            <i class="bi bi-star"></i>
                                                            <small>0</small>
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                       class="text-decoration-none text-muted"
                                                       title="좋아요 0개">
                                                        <i class="bi bi-heart"></i>
                                                        <small>0</small>
                                                    </a>
                                                    <a href="{{ route('board.show', [$code, $row->id]) }}"
                                                       class="text-decoration-none text-muted"
                                                       title="평점 없음">
                                                        <i class="bi bi-star"></i>
                                                        <small>0</small>
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            게시글이 없습니다.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 페이지네이션 -->
            <div class="mt-4">
                {{ $rows->links() }}
            </div>

            <!-- 게시판 정보 -->
            <div class="mt-4 text-muted small">
                <p class="mb-1">
                    <strong>총 게시글:</strong> {{ $board->post ?? 0 }}개 |
                    <strong>전체 조회수:</strong> {{ number_format($board->total_views ?? 0) }}회
                </p>
                @if($board->description)
                    <p class="mb-0">{{ $board->description }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
