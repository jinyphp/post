<!-- 검색 및 필터 폼 (검색 기능이 활성화된 경우만) -->
@if($forumSettings['enable_search'])
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}" class="row g-3" id="searchForm">
            <div class="col-md-2">
                <select name="perPage" class="form-select" id="perPageSelect">
                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5개씩 보기</option>
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10개씩 보기</option>
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20개씩 보기</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50개씩 보기</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100개씩 보기</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">전체 카테고리</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ $currentCategory == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="제목, 내용, 작성자 검색"
                       value="{{ $currentSearch }}">
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select">
                    <option value="created_at" {{ $currentSort == 'created_at' ? 'selected' : '' }}>최신순</option>
                    <option value="click" {{ $currentSort == 'click' ? 'selected' : '' }}>조회순</option>
                    @if($forumSettings['enable_voting'])
                        <option value="like" {{ $currentSort == 'like' ? 'selected' : '' }}>좋아요순</option>
                    @endif
                    <option value="title" {{ $currentSort == 'title' ? 'selected' : '' }}>제목순</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> 검색
                </button>
            </div>
        </form>
        @if($currentSearch || $currentCategory)
            <div class="mt-3">
                <a href="{{ url()->current() }}?perPage={{ $perPage }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> 필터 초기화
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    // 옵션 변경 시 자동 제출
    document.getElementById('perPageSelect').addEventListener('change', function() {
        document.getElementById('searchForm').submit();
    });
    document.querySelector('select[name="sort"]').addEventListener('change', function() {
        document.getElementById('searchForm').submit();
    });
    document.querySelector('select[name="category"]').addEventListener('change', function() {
        document.getElementById('searchForm').submit();
    });
</script>
@endif
