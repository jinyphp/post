@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 글 수정')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">블로그 글 수정</h1>
        <div>
            <a href="{{ route('admin.cms.blog.show', $blog->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> 미리보기
            </a>
            <a href="{{ route('admin.cms.blog') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- 메인 폼 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">글 수정</h6>
                </div>
                <div class="card-body">
                    <form id="blogForm" action="{{ route('admin.cms.blog.edit', $blog->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- 제목 -->
                        <div class="mb-3">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="{{ old('title', $blog->title) }}" required>
                            @error('title')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 슬러그 -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">슬러그 (URL)</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="{{ old('slug', $blog->slug) }}" placeholder="자동 생성됩니다">
                            <div class="form-text">비워두면 제목에서 자동 생성됩니다.</div>
                            @error('slug')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 내용 -->
                        <div class="mb-3">
                            <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="15" required>{{ old('content', $blog->content) }}</textarea>
                            @error('content')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 요약 -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">요약</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $blog->excerpt) }}</textarea>
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
                               value="{{ old('meta_title', $blog->meta_title) }}" form="blogForm">
                        <div class="form-text">비워두면 제목이 사용됩니다.</div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">메타 설명</label>
                        <textarea class="form-control" id="meta_description" name="meta_description"
                                  rows="2" form="blogForm">{{ old('meta_description', $blog->meta_description) }}</textarea>
                        <div class="form-text">비워두면 요약이 사용됩니다.</div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">메타 키워드</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                               value="{{ old('meta_keywords', $blog->meta_keywords) }}" form="blogForm"
                               placeholder="키워드1, 키워드2, 키워드3">
                    </div>
                </div>
            </div>

            <!-- 통계 정보 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">통계 정보</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-gray-800">{{ number_format($blog->views ?? 0) }}</div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase">조회수</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-gray-800">{{ number_format($blog->likes ?? 0) }}</div>
                                <div class="text-xs font-weight-bold text-success text-uppercase">좋아요</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-gray-800">{{ number_format($blog->shares ?? 0) }}</div>
                                <div class="text-xs font-weight-bold text-info text-uppercase">공유수</div>
                            </div>
                        </div>
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
                            <option value="draft" {{ old('status', $blog->status) === 'draft' ? 'selected' : '' }}>초안</option>
                            <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>발행</option>
                            <option value="scheduled" {{ old('status', $blog->status) === 'scheduled' ? 'selected' : '' }}>예약 발행</option>
                        </select>
                    </div>

                    <div class="mb-3" id="scheduled_at_group" style="display: {{ old('status', $blog->status) === 'scheduled' ? 'block' : 'none' }};">
                        <label for="scheduled_at" class="form-label">예약 발행 시간</label>
                        <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                               value="{{ old('scheduled_at', $blog->scheduled_at ? \Carbon\Carbon::parse($blog->scheduled_at)->format('Y-m-d\TH:i') : '') }}" form="blogForm">
                    </div>

                    @if($blog->published_at)
                    <div class="mb-3">
                        <label class="form-label">발행 시간</label>
                        <div class="form-control-plaintext">
                            {{ \Carbon\Carbon::parse($blog->published_at)->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">생성 시간</label>
                        <div class="form-control-plaintext">
                            {{ \Carbon\Carbon::parse($blog->created_at)->format('Y-m-d H:i:s') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">수정 시간</label>
                        <div class="form-control-plaintext">
                            {{ \Carbon\Carbon::parse($blog->updated_at)->format('Y-m-d H:i:s') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments"
                                   value="1" {{ old('allow_comments', $blog->allow_comments) ? 'checked' : '' }} form="blogForm">
                            <label class="form-check-label" for="allow_comments">
                                댓글 허용
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                   value="1" {{ old('is_featured', $blog->is_featured) ? 'checked' : '' }} form="blogForm">
                            <label class="form-check-label" for="is_featured">
                                추천 글
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_sticky" name="is_sticky"
                                   value="1" {{ old('is_sticky', $blog->is_sticky) ? 'checked' : '' }} form="blogForm">
                            <label class="form-check-label" for="is_sticky">
                                상단 고정
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" id="updateBtn">
                            <i class="fas fa-save"></i> 수정 저장
                        </button>
                        <button type="button" class="btn btn-secondary" id="draftBtn">
                            <i class="fas fa-save"></i> 초안으로 저장
                        </button>
                    </div>
                </div>
            </div>

            <!-- 카테고리 설정 -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-tag me-2"></i>카테고리
                        </h6>
                        <a href="{{ route('admin.cms.blog.category') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-gear me-1"></i>관리
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_slug" class="form-label">카테고리 선택</label>
                        <select class="form-select" id="category_slug" name="category_slug" form="blogForm">
                            <option value="" data-name="" data-color="#6c757d" data-icon="bi bi-tag">카테고리 없음</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}"
                                        data-name="{{ $category->name }}"
                                        data-color="{{ $category->color }}"
                                        data-icon="{{ $category->icon ?: 'bi bi-tag' }}"
                                        {{ old('category_slug', $blog->category_slug) === $category->slug ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 카테고리 미리보기 -->
                    <div class="mb-3" id="categoryPreviewCard" style="{{ old('category_slug', $blog->category_slug) ? 'display: block;' : 'display: none;' }}">
                        <label class="form-label">선택된 카테고리</label>
                        <div class="d-flex align-items-center p-3 bg-light rounded border" id="categoryPreview">
                            <div class="rounded me-3 d-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px; background-color: {{ old('category_slug', $blog->category_slug) ? ($categories->where('slug', old('category_slug', $blog->category_slug))->first()->color ?? '#6c757d') : '#6c757d' }};" id="previewIcon">
                                <i class="{{ old('category_slug', $blog->category_slug) ? ($categories->where('slug', old('category_slug', $blog->category_slug))->first()->icon ?? 'bi bi-tag') : 'bi bi-tag' }} text-white"></i>
                            </div>
                            <div>
                                <strong class="text-dark" id="previewName">
                                    {{ old('category_slug', $blog->category_slug) ? ($categories->where('slug', old('category_slug', $blog->category_slug))->first()->name ?? '카테고리 없음') : '카테고리 없음' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">태그</label>
                        <input type="text" class="form-control" id="tags" name="tags"
                               value="{{ old('tags', $blog->tags) }}" form="blogForm"
                               placeholder="태그1, 태그2, 태그3">
                        <div class="form-text">쉼표로 구분하여 입력하세요.</div>
                    </div>
                </div>
            </div>

            <!-- 대표 이미지 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">대표 이미지</h6>
                </div>
                <div class="card-body">
                    @if($blog->featured_image)
                    <div class="mb-3">
                        <label class="form-label">현재 이미지</label>
                        <div>
                            <img src="{{ $blog->featured_image }}" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="featured_image_file" class="form-label">새 이미지 업로드</label>
                        <input type="file" class="form-control" id="featured_image_file" name="featured_image_file"
                               accept="image/*" form="blogForm">
                        <div class="form-text">JPEG, PNG, GIF, WebP 파일만 가능 (최대 5MB)</div>
                        @error('featured_image_file')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="imagePreview" style="display: none;">
                        <label class="form-label">미리보기</label>
                        <div>
                            <img id="previewImg" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="featured_image_alt" class="form-label">이미지 설명 (Alt 텍스트)</label>
                        <input type="text" class="form-control" id="featured_image_alt" name="featured_image_alt"
                               value="{{ old('featured_image_alt', $blog->featured_image_alt) }}" form="blogForm">
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
console.log('Script loaded!');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired!');
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

    // 제목 입력 시 슬러그 자동 생성 (기존 슬러그가 비어있을 때만)
    let originalSlug = slugInput.value;
    titleInput.addEventListener('input', function() {
        if (!originalSlug || slugInput.value === originalSlug) {
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

    // 카테고리 선택 시 미리보기 업데이트
    const categorySelect = document.getElementById('category_slug');
    const categoryPreviewCard = document.getElementById('categoryPreviewCard');
    const categoryPreview = document.getElementById('categoryPreview');
    const previewIcon = document.getElementById('previewIcon');
    const previewName = document.getElementById('previewName');

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const name = selectedOption.dataset.name || '카테고리 없음';
            const color = selectedOption.dataset.color || '#6c757d';
            const icon = selectedOption.dataset.icon || 'bi bi-tag';

            if (this.value) {
                categoryPreviewCard.style.display = 'block';
                previewName.textContent = name;
                previewIcon.style.backgroundColor = color;
                previewIcon.querySelector('i').className = icon + ' text-white';
            } else {
                categoryPreviewCard.style.display = 'none';
            }
        });
    }

    // 수정 저장 버튼
    const updateBtn = document.getElementById('updateBtn');
    console.log('updateBtn found:', updateBtn);
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            console.log('Update button clicked');
            submitForm('update');
        });
    } else {
        console.error('updateBtn not found!');
    }

    // 초안 저장 버튼
    document.getElementById('draftBtn').addEventListener('click', function() {
        statusSelect.value = 'draft';
        submitForm('draft');
    });

    function submitForm(action) {
        console.log('submitForm called with action:', action);
        const formData = new FormData(form);
        formData.append('action', action);

        const actionUrl = form.getAttribute('action');
        console.log('Form action URL:', actionUrl);

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
            alert('서버 오류가 발생했습니다: ' + error.message);
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
});
</script>
@endpush
