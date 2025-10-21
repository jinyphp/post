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
                {{-- <a href="#" class="btn btn-primary">
                    <i class="bi bi-pencil-square"></i> 글쓰기
                </a> --}}
            </div>

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
