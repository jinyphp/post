<div>
    <!-- 페이지 헤더 -->
    @if(isset($jsonData['index']['heading']))
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $jsonData['index']['heading']['title'] ?? '댓글 신고 관리' }}</h1>
            @if(isset($jsonData['index']['heading']['description']))
            <p class="text-muted">{{ $jsonData['index']['heading']['description'] }}</p>
            @endif
        </div>
    </div>
    @endif

    <!-- 통계 카드 -->
    @if(isset($jsonData['index']['stats']['enabled']) && $jsonData['index']['stats']['enabled'])
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-exclamation-triangle text-warning fs-3 me-2"></i>
                        <h4 class="card-title mb-0 text-warning">{{ $pendingCount ?? 0 }}</h4>
                    </div>
                    <p class="card-text">대기중인 신고</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-check-circle text-success fs-3 me-2"></i>
                        <h4 class="card-title mb-0 text-success">{{ $approvedCount ?? 0 }}</h4>
                    </div>
                    <p class="card-text">승인된 신고</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-x-circle text-secondary fs-3 me-2"></i>
                        <h4 class="card-title mb-0 text-secondary">{{ $rejectedCount ?? 0 }}</h4>
                    </div>
                    <p class="card-text">거부된 신고</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-calendar text-info fs-3 me-2"></i>
                        <h4 class="card-title mb-0 text-info">{{ $todayCount ?? 0 }}</h4>
                    </div>
                    <p class="card-text">오늘 신고</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 필터 및 검색 -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-flag me-2"></i>댓글 신고 관리
                    </h5>
                </div>
                <div class="col-auto">
                    <!-- 검색 -->
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="신고자명, 이메일, 사유 검색..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- 필터 -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">처리상태</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">전체</option>
                        <option value="pending">대기중</option>
                        <option value="approved">승인됨</option>
                        <option value="rejected">거부됨</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">신고사유</label>
                    <select class="form-select" wire:model.live="reasonFilter">
                        <option value="">전체</option>
                        <option value="spam">스팸</option>
                        <option value="inappropriate">부적절한 내용</option>
                        <option value="abuse">욕설/비방</option>
                        <option value="copyright">저작권 침해</option>
                        <option value="other">기타</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">게시판</label>
                    <input type="text" class="form-control" placeholder="게시판 코드"
                           wire:model.live.debounce.300ms="boardFilter">
                </div>
                <div class="col-md-3">
                    <label class="form-label">페이지당 항목</label>
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10개</option>
                        <option value="25">25개</option>
                        <option value="50">50개</option>
                        <option value="100">100개</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- 테이블 -->
    <div class="card">
        <div class="card-body p-0">
            @include('jiny-post::admin.admin_comment_reports.table')
        </div>
    </div>
</div>