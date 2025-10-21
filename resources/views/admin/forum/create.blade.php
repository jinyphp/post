@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '포럼 글 등록')

@section('content')
<div class="container-fluid">

    <x-heading
        title="포럼 글 등록"
        :subtitle="$config['subtitle'] ?? ''">
        <x-btn-back-to-list :route="route('admin.cms.forum')">
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

    <form action="{{ route('admin.cms.forum.create') }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-chat-square-text me-2 text-primary"></i>
                    포럼 글 등록
                </h5>
            </div>

            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-tabs-custom" id="forumTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab"
                                data-bs-target="#basic" type="button" role="tab">
                            기본정보
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="author-tab" data-bs-toggle="tab"
                                data-bs-target="#author" type="button" role="tab">
                            작성자
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="manage-tab" data-bs-toggle="tab"
                                data-bs-target="#manage" type="button" role="tab">
                            관리
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="forumTabContent">
                    <!-- 기본정보 탭 -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="p-4">
                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">제목 <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="title" required
                                           value="{{ old('title') }}" placeholder="포럼 글 제목">
                                    @error('title')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">카테고리</label>
                                <div class="col-sm-10">
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <select class="form-select" name="categories" id="categorySelect">
                                                <option value="">카테고리 선택</option>
                                                @foreach($categories ?? [] as $category)
                                                    <option value="{{ $category->slug }}"
                                                            data-color="{{ $category->color }}"
                                                            data-icon="{{ $category->icon }}"
                                                            {{ old('categories') == $category->slug ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('admin.cms.forum.category') }}"
                                               class="btn btn-outline-secondary"
                                               title="카테고리 관리">
                                                <i class="bi bi-gear"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        카테고리가 없으면
                                        <a href="{{ route('admin.cms.forum.category.create') }}" target="_blank">여기서 생성</a>하세요.
                                    </div>
                                    <!-- 선택된 카테고리 미리보기 -->
                                    <div id="categoryPreview" class="mt-2" style="display: none;">
                                        <span class="badge" id="previewCategoryBadge">
                                            <i id="previewCategoryIcon"></i>
                                            <span id="previewCategoryName"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">키워드</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="keyword"
                                           value="{{ old('keyword') }}" placeholder="관련 키워드 (쉼표로 구분)">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">태그</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="tags"
                                           value="{{ old('tags') }}" placeholder="태그 (쉼표로 구분)">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">내용</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="content" rows="12">{{ old('content') }}</textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">대표 이미지</label>
                                <div class="col-sm-10">
                                    <input type="url" class="form-control" name="image"
                                           value="{{ old('image') }}" placeholder="이미지 URL">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 작성자 탭 -->
                    <div class="tab-pane fade" id="author" role="tabpanel">
                        <div class="p-4">
                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">이름</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="name"
                                           value="{{ old('name') }}" placeholder="작성자 이름">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">이메일</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" name="email"
                                           value="{{ old('email') }}" placeholder="작성자 이메일">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">비밀번호</label>
                                <div class="col-sm-10">
                                    <input type="password" class="form-control" name="password"
                                           placeholder="비회원일 경우 비밀번호">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">코드</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="code"
                                           value="{{ old('code') }}" placeholder="분류 코드">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">슬러그</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="slug"
                                           value="{{ old('slug') }}" placeholder="URL 슬러그">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 관리 탭 -->
                    <div class="tab-pane fade" id="manage" role="tabpanel">
                        <div class="p-4">
                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">조회수</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="click"
                                           value="{{ old('click', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">좋아요</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="like"
                                           value="{{ old('like', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">랭크</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="rank"
                                           value="{{ old('rank', 0) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('admin.cms.forum') }}" class="btn btn-secondary">취소</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> 등록
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
/* 탭 스타일 */
.nav-tabs-custom {
    background: transparent;
    border-bottom: 1px solid #dee2e6;
    padding: 0;
}

.nav-tabs-custom .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    background: transparent;
    color: #6c757d;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.nav-tabs-custom .nav-link:hover {
    border-color: transparent;
    background: rgba(0, 123, 255, 0.05);
    color: #0d6efd;
}

.nav-tabs-custom .nav-link.active {
    background: transparent;
    border-color: transparent transparent #0d6efd transparent;
    color: #0d6efd;
}

/* 탭 컨텐츠 */
.tab-content {
    background: white;
}

/* 카드 헤더 스타일 */
.card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

/* 폼 스타일 */
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* 카테고리 미리보기 */
#categoryPreview .badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    const categoryPreview = document.getElementById('categoryPreview');
    const previewCategoryName = document.getElementById('previewCategoryName');
    const previewCategoryBadge = document.getElementById('previewCategoryBadge');
    const previewCategoryIcon = document.getElementById('previewCategoryIcon');

    // 카테고리 선택 시 미리보기 업데이트
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const categoryName = selectedOption.text;
            const categoryColor = selectedOption.dataset.color;
            const categoryIcon = selectedOption.dataset.icon;

            if (selectedOption.value) {
                // 미리보기 표시
                if (previewCategoryName) previewCategoryName.textContent = categoryName;
                if (previewCategoryBadge) {
                    previewCategoryBadge.style.backgroundColor = categoryColor;
                    previewCategoryBadge.style.color = 'white';
                }

                if (previewCategoryIcon) {
                    if (categoryIcon) {
                        previewCategoryIcon.className = categoryIcon + ' me-1';
                    } else {
                        previewCategoryIcon.className = 'bi-tag me-1';
                    }
                }

                if (categoryPreview) categoryPreview.style.display = 'block';
            } else {
                // 미리보기 숨기기
                if (categoryPreview) categoryPreview.style.display = 'none';
            }
        });

        // 페이지 로드 시 선택된 카테고리가 있으면 미리보기 표시
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush
@endsection