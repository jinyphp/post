@extends('jiny-site::layouts.app')

@section('content')
<div class="container py-5">

    <div class="row">
        <div class="col-12">

            <!-- 포럼 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">포럼</h2>
                    <p class="text-muted mb-0">자유롭게 의견을 나누어 보세요</p>
                </div>

                @if(isset($writePermission) && $writePermission['allowed'])
                    @php
                        $userType = $writePermission['user_type'] ?? 'guest';
                        $buttonClass = match($userType) {
                            'admin' => 'btn-danger',     // 관리자: 빨간색
                            'user' => 'btn-primary',     // 일반회원: 파란색
                            'guest' => 'btn-warning',    // 비회원: 노란색
                            default => 'btn-secondary'
                        };
                        $buttonText = match($userType) {
                            'admin' => '관리자 글쓰기',
                            'user' => '회원 글쓰기',
                            'guest' => '비회원 글쓰기',
                            default => '글쓰기'
                        };
                    @endphp
                    <a href="{{ route('forum.create') }}" class="btn {{ $buttonClass }}">
                        <i class="bi bi-pencil-square"></i> {{ $buttonText }}
                    </a>
                @elseif(isset($writePermission))
                    @php
                        $userType = $writePermission['user_type'] ?? 'guest';
                        $buttonText = match($userType) {
                            'admin' => '관리자 글쓰기',
                            'user' => '회원 글쓰기',
                            'guest' => '비회원 글쓰기',
                            default => '글쓰기'
                        };
                    @endphp
                    <button type="button" class="btn btn-secondary" disabled title="{{ $writePermission['message'] }}">
                        <i class="bi bi-pencil-square"></i> {{ $buttonText }}
                    </button>
                @endif
            </div>

            {{-- 디버그 정보 (개발 환경에서만 표시) --}}
            @if(config('app.debug') && isset($currentUser))
                <div class="alert alert-info">
                    <small>
                        <strong>디버그 정보:</strong><br>
                        사용자 타입: {{ $writePermission['user_type'] ?? 'unknown' }}<br>
                        인증 방식: {{ $currentUser->auth_type ?? 'unknown' }}<br>
                        사용자 ID: {{ $currentUser->id ?? 'N/A' }}<br>
                        사용자 이름: {{ $currentUser->name ?? 'N/A' }}<br>
                        관리자 여부: {{ isset($currentUser->isAdmin) ? ($currentUser->isAdmin ? 'Yes' : 'No') : 'N/A' }}<br>
                        권한 상태: {{ $writePermission['allowed'] ? 'Allowed' : 'Denied' }}<br>
                        권한 이유: {{ $writePermission['reason'] ?? 'N/A' }}
                    </small>
                </div>
            @elseif(config('app.debug'))
                <div class="alert alert-warning">
                    <small><strong>디버그:</strong> 인증되지 않은 사용자 (게스트)</small>
                </div>
            @endif

            {{-- 검색 --}}
            @includeIf("jiny-post::www.forum.search")


            {{-- 알림 메시지 --}}
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


            {{-- 포품 글 목록 --}}
            @includeIf("jiny-post::www.forum.table")

        </div>
    </div>

</div>
@endsection
