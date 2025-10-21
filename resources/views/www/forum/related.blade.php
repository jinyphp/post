<div class="row">
    <div class="col-12" style="padding-left: 0; padding-right: 0;">

        <!-- 관련 글 -->
        @if ($relatedPosts->count() > 0)
            <section class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-collection"></i> 관련 글</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach ($relatedPosts as $relatedPost)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <a href="{{ route('forum.show', $relatedPost->id) }}"
                                            class="text-decoration-none">
                                            {{ $relatedPost->title }}
                                        </a>
                                        <div class="small text-muted mt-1">
                                            {{ $relatedPost->name ?? '익명' }} |
                                            {{ \Carbon\Carbon::parse($relatedPost->created_at)->format('Y-m-d') }}
                                        </div>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-eye"></i> {{ $relatedPost->click ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- 하단 버튼 -->
            <div class="text-center">
                <a href="{{ route('forum.index') }}" class="btn btn-primary">
                    <i class="bi bi-list"></i> 목록으로 돌아가기
                </a>
            </div>

        @endif

    </div>
</div>
