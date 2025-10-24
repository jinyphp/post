<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">검색어</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control"
                   placeholder="신고자명, 이메일, 사유 검색..."
                   wire:model.live.debounce.300ms="search">
        </div>
    </div>

    <div class="col-md-2">
        <label class="form-label">상태</label>
        <select class="form-select" wire:model.live="statusFilter">
            <option value="">전체</option>
            <option value="pending">대기중</option>
            <option value="approved">승인됨</option>
            <option value="rejected">거부됨</option>
        </select>
    </div>

    <div class="col-md-2">
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

    <div class="col-md-2">
        <label class="form-label">게시판</label>
        <input type="text" class="form-control"
               placeholder="게시판 코드"
               wire:model.live.debounce.300ms="boardFilter">
    </div>

    <div class="col-md-2">
        <label class="form-label">표시 수</label>
        <select class="form-select" wire:model.live="perPage">
            <option value="10">10개</option>
            <option value="25">25개</option>
            <option value="50">50개</option>
            <option value="100">100개</option>
        </select>
    </div>
</div>