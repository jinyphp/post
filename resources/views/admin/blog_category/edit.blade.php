@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 카테고리 수정')

@section('content')
    <div class="container-fluid">

        <x-heading title="블로그 카테고리 수정" :subtitle="$category->name . ' 카테고리를 수정합니다.'">
            <div class="d-flex gap-2">
                <x-btn-back-to-list :route="route('admin.cms.blog.category')">
                    목록
                </x-btn-back-to-list>

            </div>
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
                <form action="{{ route('admin.cms.blog.category.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- 기본 정보 카드 -->
                    <x-card class="mb-4">
                        <x-card-header>
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                기본 정보
                            </h5>
                        </x-card-header>
                        <x-card-body>
                            <x-input-text name="name" label="카테고리명" :value="$category->name" required
                                placeholder="카테고리 이름을 입력하세요">
                            </x-input-text>

                            <x-input-text name="slug" label="슬러그" :value="$category->slug" placeholder="URL에 사용될 슬러그 (자동생성)">
                                비워두면 카테고리명에서 자동 생성됩니다.
                            </x-input-text>

                            <x-textarea name="description" label="설명" :value="$category->description" placeholder="카테고리에 대한 설명을 입력하세요"
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
                                        <input type="color" class="form-control form-control-color" name="color"
                                            required value="{{ old('color', $category->color) }}"
                                            style="width: 60px; height: 38px;">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control" id="colorHex"
                                            value="{{ old('color', $category->color) }}" placeholder="#6c757d" readonly>
                                    </div>
                                </div>
                                @error('color')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <x-input-text name="icon" label="아이콘" :value="$category->icon"
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
                                    value="{{ old('sort_order', $category->sort_order) }}" min="0" max="999">
                                <div class="form-text">숫자가 작을수록 먼저 표시됩니다.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">상태</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                        {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        활성화
                                    </label>
                                </div>
                                <div class="form-text">비활성화하면 사용자에게 표시되지 않습니다.</div>
                            </div>
                        </x-card-body>
                    </x-card>

                    {{-- 제어버튼 --}}
                    <div class="d-flex justify-content-between gap-2 mb-4">
                        <div>
                            <x-btn-delete :route="route('admin.cms.blog.category.destroy', $category->id)" :confirm-message="'정말로 \'' . $category->name . '\' 카테고리를 삭제하시겠습니까?'">
                            삭제
                            </x-btn-delete>

                        </div>
                        <div>
                            <x-btn-reset :route="route('admin.cms.blog.category')">
                                취소
                            </x-btn-reset>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> 수정
                            </button>
                        </div>

                    </div>
                </form>

                {{-- 삭제 버튼은 폼 밖에서 별도로 --}}
                {{-- <div class="d-flex justify-content-start mb-4">

                </div> --}}
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
                            <span class="badge" id="previewBadge"
                                style="background-color: {{ $category->color }}; color: white;">
                                <i id="previewIcon" class="{{ $category->icon ?: 'bi-tag' }}"></i>
                                <span id="previewName">{{ $category->name }}</span>
                            </span>
                        </div>
                        <div class="text-muted small" id="previewDescription">
                            {{ $category->description ?: '카테고리 설명이 여기에 표시됩니다.' }}
                        </div>
                    </x-card-body>
                </x-card>

                <!-- 통계 정보 -->
                <x-card class="mb-4">
                    <x-card-header>
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            통계 정보
                        </h6>
                    </x-card-header>
                    <x-card-body>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 mb-0 text-primary">{{ number_format($category->post_count ?? 0) }}</div>
                                <div class="text-muted small">게시글</div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0 text-success">{{ $category->is_active ? '활성' : '비활성' }}</div>
                                <div class="text-muted small">상태</div>
                            </div>
                        </div>
                        @if (isset($category->last_post_at))
                            <hr>
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                마지막 게시글: {{ $category->last_post_at }}
                            </div>
                        @endif
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
            document.addEventListener('DOMContentLoaded', function() {
                const colorInput = document.querySelector('input[name="color"]');
                const colorHexInput = document.getElementById('colorHex');
                const previewBadge = document.getElementById('previewBadge');
                const nameInput = document.querySelector('input[name="name"]');
                const previewName = document.getElementById('previewName');
                const descriptionInput = document.querySelector('textarea[name="description"]');
                const previewDescription = document.getElementById('previewDescription');
                const iconInput = document.querySelector('input[name="icon"]');
                const previewIcon = document.getElementById('previewIcon');

                // 색상 변경 시 미리보기 업데이트
                if (colorInput) {
                    colorInput.addEventListener('input', function() {
                        const color = this.value;
                        if (colorHexInput) colorHexInput.value = color;
                        if (previewBadge) previewBadge.style.backgroundColor = color;
                    });
                }

                // 색상 텍스트 입력 시 색상 피커 업데이트
                if (colorHexInput) {
                    colorHexInput.addEventListener('input', function() {
                        const color = this.value;
                        if (color.match(/^#[0-9A-F]{6}$/i)) {
                            if (colorInput) colorInput.value = color;
                            if (previewBadge) previewBadge.style.backgroundColor = color;
                        }
                    });
                }

                // 이름 변경 시 미리보기 업데이트
                if (nameInput) {
                    nameInput.addEventListener('input', function() {
                        const name = this.value || '{{ $category->name }}';
                        if (previewName) previewName.textContent = name;
                    });
                }

                // 설명 변경 시 미리보기 업데이트
                if (descriptionInput) {
                    descriptionInput.addEventListener('input', function() {
                        const description = this.value || '카테고리 설명이 여기에 표시됩니다.';
                        if (previewDescription) previewDescription.textContent = description;
                    });
                }

                // 아이콘 변경 시 미리보기 업데이트
                if (iconInput) {
                    iconInput.addEventListener('input', function() {
                        const icon = this.value || '{{ $category->icon ?: 'bi-tag' }}';
                        if (previewIcon) previewIcon.className = icon;
                    });
                }
            });
        </script>
    @endpush
@endsection
