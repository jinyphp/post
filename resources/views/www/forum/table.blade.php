<!-- 포럼 글 목록 -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">

            @includeIf("jiny-post::www.forum.list")

        </div>
    </div>
</div>

<!-- 페이지네이션 -->
<div class="mt-4">
    {{ $rows->links() }}
</div>

<!-- 포럼 통계 -->
<div class="mt-4 text-muted small">
    <p class="mb-1">
        <strong>총 글 수:</strong> {{ $rows->total() }}개 |
        <strong>현재 페이지:</strong> {{ $rows->currentPage() }} / {{ $rows->lastPage() }}
    </p>
</div>
