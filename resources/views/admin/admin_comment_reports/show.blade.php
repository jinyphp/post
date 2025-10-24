@extends('jiny-site::layouts.admin.sidebar')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>댓글 신고 상세</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.cms.board.comment.reports.index') }}">댓글 신고 관리</a>
                            </li>
                            <li class="breadcrumb-item active">신고 #{{ $row->id }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.cms.board.comment.reports.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>목록으로
                    </a>
                </div>
            </div>

            <!-- 신고 정보 -->
            <div class="row">
                <div class="col-md-8">
                    <!-- 신고된 댓글 내용 -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-chat-square-text me-2"></i>신고된 댓글
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($comment) && $comment)
                                <div class="border-start border-primary border-3 ps-3 mb-3">
                                    <p class="mb-2">{{ $comment->content }}</p>
                                    <small class="text-muted">
                                        작성자: {{ $comment->name }} |
                                        작성일: {{ Carbon\Carbon::parse($comment->created_at)->format('Y-m-d H:i') }}
                                    </small>
                                </div>

                                @if(isset($post) && $post)
                                <div class="mt-3 pt-3 border-top">
                                    <h6>게시글 정보</h6>
                                    <p class="mb-1"><strong>제목:</strong> {{ $post->title }}</p>
                                    <p class="mb-0"><small class="text-muted">게시판: {{ $row->board_code }}</small></p>
                                </div>
                                @endif
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    해당 댓글을 찾을 수 없습니다. (이미 삭제되었을 수 있습니다)
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 신고 상세 내용 -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-flag me-2"></i>신고 내용
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>신고 사유</h6>
                                    @php
                                        $reasonLabels = [
                                            'spam' => '스팸',
                                            'inappropriate' => '부적절한 내용',
                                            'abuse' => '욕설/비방',
                                            'copyright' => '저작권 침해',
                                            'other' => '기타'
                                        ];
                                    @endphp
                                    <span class="badge bg-warning fs-6">{{ $reasonLabels[$row->reason] ?? $row->reason }}</span>
                                </div>
                                <div class="col-md-6">
                                    <h6>현재 상태</h6>
                                    @php
                                        $statusLabels = [
                                            'pending' => ['label' => '대기중', 'class' => 'warning'],
                                            'approved' => ['label' => '승인됨', 'class' => 'success'],
                                            'rejected' => ['label' => '거부됨', 'class' => 'secondary']
                                        ];
                                        $status = $statusLabels[$row->status] ?? ['label' => $row->status, 'class' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }} fs-6">{{ $status['label'] }}</span>
                                </div>
                            </div>

                            @if($row->description)
                            <div class="mt-3">
                                <h6>상세 설명</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $row->description }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- 신고자 정보 -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-person me-2"></i>신고자 정보
                            </h6>
                        </div>
                        <div class="card-body">
                            <p><strong>이름:</strong> {{ $row->reporter_name }}</p>
                            <p><strong>이메일:</strong> {{ $row->reporter_email }}</p>
                            <p><strong>신고일시:</strong><br>
                               {{ Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>

                    <!-- 처리 정보 -->
                    @if($row->reviewed_at)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-check-circle me-2"></i>처리 정보
                            </h6>
                        </div>
                        <div class="card-body">
                            @if(isset($reviewer) && $reviewer)
                                <p><strong>처리자:</strong> {{ $reviewer->name }}</p>
                            @elseif($row->reviewed_by)
                                <p><strong>처리자 ID:</strong> {{ $row->reviewed_by }}</p>
                            @endif
                            <p><strong>처리일시:</strong><br>
                               {{ Carbon\Carbon::parse($row->reviewed_at)->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- 액션 버튼 -->
                    @if($row->status === 'pending')
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>처리 작업
                            </h6>
                        </div>
                        <div class="card-body d-grid gap-2">
                            <form method="POST" action="{{ route('admin.cms.board.comment.reports.approve', $row->id) }}"
                                  onsubmit="return confirm('이 신고를 승인하고 해당 댓글을 숨김 처리하시겠습니까?')">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle me-2"></i>신고 승인
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.cms.board.comment.reports.reject', $row->id) }}"
                                  onsubmit="return confirm('이 신고를 거부하시겠습니까?')">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-x-circle me-2"></i>신고 거부
                                </button>
                            </form>

                            <hr>

                            <form method="POST" action="{{ route('admin.cms.board.comment.reports.delete', $row->id) }}"
                                  onsubmit="return confirm('이 신고를 삭제하시겠습니까?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-trash me-2"></i>신고 삭제
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted">이미 처리된 신고입니다.</p>
                            <form method="POST" action="{{ route('admin.cms.board.comment.reports.delete', $row->id) }}"
                                  onsubmit="return confirm('이 신고를 삭제하시겠습니까?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash me-2"></i>신고 삭제
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection