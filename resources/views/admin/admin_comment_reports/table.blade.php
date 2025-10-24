<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;">
                    <input type="checkbox" class="form-check-input" wire:model.live="selectAll">
                </th>
                <th style="width: 80px;">ID</th>
                <th style="width: 100px;">게시판</th>
                <th>댓글 내용</th>
                <th style="width: 120px;">댓글 작성자</th>
                <th style="width: 120px;">신고자</th>
                <th style="width: 120px;">신고사유</th>
                <th style="width: 100px;">상태</th>
                <th style="width: 150px;">신고일시</th>
                <th style="width: 200px;">작업</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr wire:key="report-{{ $row->id }}">
                <td>
                    <input type="checkbox" class="form-check-input"
                           wire:model.live="selectedIds" value="{{ $row->id }}">
                </td>
                <td>{{ $row->id }}</td>
                <td>
                    <span class="badge bg-primary">{{ $row->board_code }}</span>
                </td>
                <td>
                    <div style="max-width: 300px;">
                        @if(isset($row->comment_content))
                            <p class="mb-1 text-truncate">{{ Str::limit($row->comment_content, 100) }}</p>
                            <small class="text-muted">작성자: {{ $row->comment_author ?? 'Unknown' }}</small>
                        @else
                            <span class="text-muted">댓글을 찾을 수 없음</span>
                        @endif
                    </div>
                </td>
                <td>{{ $row->comment_author ?? 'Unknown' }}</td>
                <td>
                    <div>
                        <div class="fw-bold">{{ $row->reporter_name }}</div>
                        <small class="text-muted">{{ $row->reporter_email }}</small>
                    </div>
                </td>
                <td>
                    @php
                        $reasonLabels = [
                            'spam' => ['label' => '스팸', 'class' => 'warning'],
                            'inappropriate' => ['label' => '부적절한 내용', 'class' => 'danger'],
                            'abuse' => ['label' => '욕설/비방', 'class' => 'danger'],
                            'copyright' => ['label' => '저작권 침해', 'class' => 'info'],
                            'other' => ['label' => '기타', 'class' => 'secondary']
                        ];
                        $reason = $reasonLabels[$row->reason] ?? ['label' => $row->reason, 'class' => 'secondary'];
                    @endphp
                    <span class="badge bg-{{ $reason['class'] }}">{{ $reason['label'] }}</span>
                </td>
                <td>
                    @php
                        $statusLabels = [
                            'pending' => ['label' => '대기중', 'class' => 'warning'],
                            'approved' => ['label' => '승인됨', 'class' => 'success'],
                            'rejected' => ['label' => '거부됨', 'class' => 'secondary']
                        ];
                        $status = $statusLabels[$row->status] ?? ['label' => $row->status, 'class' => 'secondary'];
                    @endphp
                    <span class="badge bg-{{ $status['class'] }}">{{ $status['label'] }}</span>
                </td>
                <td>
                    <div>
                        <div>{{ Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}</div>
                        <small class="text-muted">{{ Carbon\Carbon::parse($row->created_at)->format('H:i') }}</small>
                    </div>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <!-- 상세보기 -->
                        <a href="{{ route('admin.cms.board.comment.reports.show', $row->id) }}"
                           class="btn btn-sm btn-outline-info">
                            <i class="bi bi-eye"></i>
                        </a>

                        @if($row->status === 'pending')
                            <!-- 승인 -->
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    wire:click="approveReport({{ $row->id }})"
                                    wire:confirm="이 신고를 승인하고 해당 댓글을 숨김 처리하시겠습니까?"
                                    wire:loading.attr="disabled">
                                <i class="bi bi-check"></i>
                            </button>

                            <!-- 거부 -->
                            <button type="button" class="btn btn-sm btn-outline-warning"
                                    wire:click="rejectReport({{ $row->id }})"
                                    wire:confirm="이 신고를 거부하시겠습니까?"
                                    wire:loading.attr="disabled">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif

                        <!-- 삭제 -->
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                wire:click="deleteReport({{ $row->id }})"
                                wire:confirm="이 신고를 삭제하시겠습니까?"
                                wire:loading.attr="disabled">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p>신고된 댓글이 없습니다.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- 페이지네이션 -->
@if(isset($rows) && method_exists($rows, 'links'))
<div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
    <div>
        <small class="text-muted">
            총 {{ $rows->total() }}개 중 {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }}개 표시
        </small>
    </div>
    <div>
        {{ $rows->links() }}
    </div>
</div>
@endif

<!-- 대량 작업 -->
@if(!empty($selectedIds))
<div class="position-fixed bottom-0 start-50 translate-middle-x mb-3">
    <div class="card shadow">
        <div class="card-body py-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">{{ count($selectedIds) }}개 선택됨</span>
                <button type="button" class="btn btn-sm btn-danger"
                        wire:click="bulkDelete"
                        wire:confirm="선택한 {{ count($selectedIds) }}개의 신고를 삭제하시겠습니까?"
                        wire:loading.attr="disabled">
                    <i class="bi bi-trash me-1"></i>삭제
                </button>
                <button type="button" class="btn btn-sm btn-secondary"
                        wire:click="clearSelection">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endif