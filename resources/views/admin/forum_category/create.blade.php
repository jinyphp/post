@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '포럼 카테고리 등록')

@section('content')
<div class="container-fluid">

    <x-heading
        title="포럼 카테고리 등록"
        :subtitle="$config['subtitle'] ?? ''">
        <x-btn-back-to-list :route="route('admin.cms.forum.category')">
            목록
        </x-btn-back-to-list>
    </x-heading>

    <!-- 알림 메시지 -->
    <x-alert-success>
        {{ session('success') }}
    </x-alert-success>

    <x-alert-danger>
        {{ session('error') }}
    </x-alert-danger>

    <x-content>
        <x-content-main>
            <form action="{{ route('admin.cms.forum.category.store') }}" method="POST">
                @csrf

                <!-- 기본 정보 카드 -->
                <x-card class="mb-4">
                    <x-card-header>
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            기본 정보
                        </h5>
                    </x-card-header>
                    <x-card-body>
                        <x-input-text
                            name="name"
                            label="카테고리명"
                            required
                            placeholder="카테고리 이름을 입력하세요">
                        </x-input-text>

                        <x-input-text
                            name="slug"
                            label="슬러그"
                            placeholder="URL에 사용될 슬러그 (자동생성)">
                            비워두면 카테고리명에서 자동 생성됩니다.
                        </x-input-text>

                        <x-textarea
                            name="description"
                            label="설명"
                            placeholder="카테고리에 대한 설명을 입력하세요"
                            rows="3">
                            카테고리에 대한 설명을 입력하세요.
                        </x-textarea>
                    </x-card-body>
                </x-card>

                <!-- 디자인 설정 카드 -->
                <x-card class="mb-4">
                    <x-card-header>
                        <h5 class="mb-0">
                            <i class="bi bi-palette me-2 text-primary"></i>
                            디자인 설정
                        </h5>
                    </x-card-header>
                    <x-card-body>
                        <div class="mb-3">
                            <label class="form-label">색상 <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-auto">
                                    <input type="color" class="form-control form-control-color"
                                           name="color" required
                                           value="{{ old('color', '#6c757d') }}"
                                           style="width: 60px; height: 38px;">
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" id="colorHex"
                                           value="{{ old('color', '#6c757d') }}"
                                           placeholder="#6c757d" readonly>
                                </div>
                            </div>
                            @error('color')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <x-input-text
                            name="icon"
                            label="아이콘"
                            placeholder="예: bi-chat, bi-question-circle">
                            Bootstrap Icons 클래스명을 입력하세요.
                            <a href="https://icons.getbootstrap.com/" target="_blank">아이콘 보기</a>
                        </x-input-text>
                    </x-card-body>
                </x-card>

                <!-- 관리 설정 카드 -->
                <x-card class="mb-4">
                    <x-card-header>
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2 text-primary"></i>
                            관리 설정
                        </h5>
                    </x-card-header>
                    <x-card-body>
                        <div class="mb-3">
                            <label class="form-label">정렬 순서</label>
                            <input type="number" class="form-control" name="sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0" max="999">
                            <div class="form-text">숫자가 작을수록 먼저 표시됩니다.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">상태</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    활성화
                                </label>
                            </div>
                            <div class="form-text">비활성화하면 사용자에게 표시되지 않습니다.</div>
                        </div>
                    </x-card-body>
                </x-card>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.cms.forum.category') }}" class="btn btn-secondary">취소</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> 등록
                    </button>
                </div>
            </form>
        </x-content-main>

        <x-content-side>
            <!-- 미리보기 -->
            <x-card class="mb-4">
                <x-card-header>
                    <h6 class="mb-0">
                        <i class="bi bi-eye me-2 text-primary"></i>
                        미리보기
                    </h6>
                </x-card-header>
                <x-card-body>
                    <div class="preview-category mb-3">
                        <span class="badge" id="previewBadge" style="background-color: #6c757d; color: white;">
                            <i id="previewIcon" class="bi-tag"></i>
                            <span id="previewName">카테고리명</span>
                        </span>
                    </div>
                    <div class="text-muted small" id="previewDescription">
                        카테고리 설명이 여기에 표시됩니다.
                    </div>
                </x-card-body>
            </x-card>

            <!-- 도움말 -->
            <x-help title="카테고리 설정 도움말" icon="bi-question-circle" iconColor="text-info">
                <x-help-title icon="bi-tag" iconColor="text-primary">
                    카테고리명
                </x-help-title>
                <ul class="small text-muted mb-3">
                    <li>명확하고 간결한 이름 사용</li>
                    <li>중복되지 않는 고유한 이름</li>
                    <li>사용자가 이해하기 쉬운 이름</li>
                </ul>

                <x-help-title icon="bi-palette" iconColor="text-warning">
                    색상 선택
                </x-help-title>
                <ul class="small text-muted mb-3">
                    <li>카테고리별 구분이 쉬운 색상</li>
                    <li>사이트 디자인과 조화로운 색상</li>
                    <li>접근성을 고려한 명도 대비</li>
                </ul>

                <x-help-title icon="bi-sort-numeric-up" iconColor="text-success" marginBottom="mb-0">
                    정렬 순서
                </x-help-title>
                <ul class="small text-muted mb-0">
                    <li>중요한 카테고리는 낮은 숫자</li>
                    <li>일반적으로 10 단위로 설정</li>
                    <li>나중에 중간 삽입이 가능하도록</li>
                </ul>
            </x-help>
        </x-content-side>
    </x-content>
</div>

@once
    @push('styles')
    <style>
    /* 색상 입력 필드 */
    .form-control-color {
        border: 2px solid #dee2e6;
    }

    .form-control-color:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* 미리보기 배지 */
    .preview-category .badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    </style>
    @endpush
@endonce

@push('scripts')
<script>
$(document).ready(function() {
    // 색상 변경 시 미리보기 업데이트
    $('input[name="color"]').on('input', function() {
        var color = $(this).val();
        $('#colorHex').val(color);
        $('#previewBadge').css('background-color', color);
    });

    // 색상 텍스트 입력 시 색상 피커 업데이트
    $('#colorHex').on('input', function() {
        var color = $(this).val();
        if (color.match(/^#[0-9A-F]{6}$/i)) {
            $('input[name="color"]').val(color);
            $('#previewBadge').css('background-color', color);
        }
    });

    // 이름 변경 시 미리보기 업데이트
    $('input[name="name"]').on('input', function() {
        var name = $(this).val() || '카테고리명';
        $('#previewName').text(name);
    });

    // 설명 변경 시 미리보기 업데이트
    $('textarea[name="description"]').on('input', function() {
        var description = $(this).val() || '카테고리 설명이 여기에 표시됩니다.';
        $('#previewDescription').text(description);
    });

    // 아이콘 변경 시 미리보기 업데이트
    $('input[name="icon"]').on('input', function() {
        var icon = $(this).val() || 'bi-tag';
        $('#previewIcon').attr('class', icon);
    });
});
</script>
@endpush
@endsection
