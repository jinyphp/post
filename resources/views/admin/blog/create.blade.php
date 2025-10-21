@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '새 블로그 글 작성')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">새 블로그 글 작성</h1>
        <a href="{{ route('admin.cms.blog') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- 메인 폼 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">글 작성</h6>
                </div>
                <div class="card-body">
                    <form id="blogForm" action="{{ route('admin.cms.blog.create') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- 제목 -->
                        <div class="mb-3">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 슬러그 -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">슬러그 (URL)</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="{{ old('slug') }}" placeholder="자동 생성됩니다">
                            <div class="form-text">비워두면 제목에서 자동 생성됩니다.</div>
                            @error('slug')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 내용 -->
                        <div class="mb-3">
                            <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="15" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 요약 -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">요약</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3">{{ old('excerpt') }}</textarea>
                            <div class="form-text">비워두면 내용에서 자동 생성됩니다.</div>
                            @error('excerpt')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>

            <!-- SEO 설정 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">SEO 설정</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">메타 제목</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                               value="{{ old('meta_title') }}" form="blogForm">
                        <div class="form-text">비워두면 제목이 사용됩니다.</div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">메타 설명</label>
                        <textarea class="form-control" id="meta_description" name="meta_description"
                                  rows="2" form="blogForm">{{ old('meta_description') }}</textarea>
                        <div class="form-text">비워두면 요약이 사용됩니다.</div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">메타 키워드</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                               value="{{ old('meta_keywords') }}" form="blogForm"
                               placeholder="키워드1, 키워드2, 키워드3">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- 발행 설정 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">발행 설정</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">상태</label>
                        <select class="form-select" id="status" name="status" form="blogForm">
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>초안</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>발행</option>
                            <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>예약 발행</option>
                        </select>
                    </div>

                    <div class="mb-3" id="scheduled_at_group" style="display: none;">
                        <label for="scheduled_at" class="form-label">예약 발행 시간</label>
                        <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                               value="{{ old('scheduled_at') }}" form="blogForm">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments"
                                   value="1" {{ old('allow_comments') ? 'checked' : '' }} form="blogForm">
                            <label class="form-check-label" for="allow_comments">
                                댓글 허용
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                   value="1" {{ old('is_featured') ? 'checked' : '' }} form="blogForm">
                            <label class="form-check-label" for="is_featured">
                                추천 글
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_sticky" name="is_sticky"
                                   value="1" {{ old('is_sticky') ? 'checked' : '' }} form="blogForm">
                            <label class="form-check-label" for="is_sticky">
                                상단 고정
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" id="publishBtn">
                            <i class="fas fa-paper-plane"></i> 발행
                        </button>
                        <button type="button" class="btn btn-secondary" id="draftBtn">
                            <i class="fas fa-save"></i> 초안 저장
                        </button>
                    </div>
                </div>
            </div>

            <!-- 카테고리 설정 -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-tag me-2 text-primary"></i>카테고리 & 태그
                        </h6>
                        <a href="{{ route('admin.cms.blog.category') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-plus-circle me-1"></i>카테고리 관리
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_slug" class="form-label">카테고리</label>
                        <select class="form-select" id="category_slug" name="category_slug" form="blogForm">
                            <option value="">카테고리 없음</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}"
                                        data-color="{{ $category->color }}"
                                        data-icon="{{ $category->icon }}"
                                        {{ old('category_slug') === $category->slug ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">카테고리를 선택하면 블로그 글이 분류됩니다.</div>
                    </div>

                    <!-- 선택된 카테고리 미리보기 -->
                    <div id="categoryPreview" class="mb-3" style="display: none;">
                        <div class="d-flex align-items-center p-2 bg-light rounded">
                            <div class="rounded me-2 d-flex align-items-center justify-content-center"
                                 style="width: 32px; height: 32px;" id="previewIcon">
                                <i class="bi bi-tag text-white"></i>
                            </div>
                            <small class="text-muted">선택된 카테고리: <span id="previewName">없음</span></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">태그</label>
                        <input type="text" class="form-control" id="tags" name="tags"
                               value="{{ old('tags') }}" form="blogForm"
                               placeholder="태그1, 태그2, 태그3">
                        <div class="form-text">쉼표로 구분하여 입력하세요. SEO에 도움이 됩니다.</div>
                    </div>
                </div>
            </div>

            <!-- 대표 이미지 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">대표 이미지</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="file" class="form-control" id="featured_image_file" name="featured_image_file"
                               accept="image/*" form="blogForm">
                        <div class="form-text">JPEG, PNG, GIF, WebP 파일만 가능 (최대 5MB)</div>
                        @error('featured_image_file')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="imagePreview" style="display: none;">
                        <img id="previewImg" class="img-fluid rounded" style="max-height: 200px;">
                    </div>

                    <div class="mb-3">
                        <label for="featured_image_alt" class="form-label">이미지 설명 (Alt 텍스트)</label>
                        <input type="text" class="form-control" id="featured_image_alt" name="featured_image_alt"
                               value="{{ old('featured_image_alt') }}" form="blogForm">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 로딩 모달 -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">처리 중...</span>
                </div>
                <p class="mt-2 mb-0">저장 중입니다...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('blogForm');
    const statusSelect = document.getElementById('status');
    const scheduledAtGroup = document.getElementById('scheduled_at_group');
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const imageInput = document.getElementById('featured_image_file');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

    // 상태 변경에 따른 예약 발행 시간 표시/숨김
    statusSelect.addEventListener('change', function() {
        if (this.value === 'scheduled') {
            scheduledAtGroup.style.display = 'block';
        } else {
            scheduledAtGroup.style.display = 'none';
        }
    });

    // 제목 입력 시 슬러그 자동 생성
    titleInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.value === '') {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9가-힣\s-]/g, '')
                .replace(/\s+/g, '-')
                .trim();
            slugInput.value = slug;
        }
    });

    // 이미지 미리보기
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // 카테고리 선택 미리보기
    const categorySelect = document.getElementById('category_slug');
    const categoryPreview = document.getElementById('categoryPreview');
    const previewIcon = document.getElementById('previewIcon');
    const previewName = document.getElementById('previewName');

    categorySelect.addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];

        if (this.value) {
            const color = selectedOption.dataset.color || '#6c757d';
            const icon = selectedOption.dataset.icon || 'bi bi-tag';
            const name = selectedOption.text;

            previewIcon.style.backgroundColor = color;
            previewIcon.querySelector('i').className = icon + ' text-white';
            previewName.textContent = name;
            categoryPreview.style.display = 'block';
        } else {
            categoryPreview.style.display = 'none';
        }
    });

    // 초기 카테고리 미리보기 설정
    if (categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }

    // 발행 버튼
    document.getElementById('publishBtn').addEventListener('click', function() {
        statusSelect.value = 'published';
        submitForm('publish');
    });

    // 초안 저장 버튼
    document.getElementById('draftBtn').addEventListener('click', function() {
        statusSelect.value = 'draft';
        submitForm('draft');
    });

    function submitForm(action) {
        const formData = new FormData(form);
        formData.append('action', action);

        loadingModal.show();

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingModal.hide();

            if (data.success) {
                // 성공 시 목록 페이지로 이동
                window.location.href = data.redirect || '{{ route("admin.cms.blog") }}';
            } else {
                // 오류 처리
                if (data.errors) {
                    showValidationErrors(data.errors);
                } else {
                    alert(data.message || '저장에 실패했습니다.');
                }
            }
        })
        .catch(error => {
            loadingModal.hide();
            console.error('Submit error:', error);
            alert('서버 오류가 발생했습니다.');
        });
    }

    function showValidationErrors(errors) {
        // 기존 오류 메시지 제거
        document.querySelectorAll('.text-danger.small').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // 새 오류 메시지 표시
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger small';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    // 초기 상태 설정
    if (statusSelect.value === 'scheduled') {
        scheduledAtGroup.style.display = 'block';
    }
});
</script>
@endpush
