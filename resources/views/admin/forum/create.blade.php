@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '포럼 글 등록')

@section('content')
    <div class="container-fluid">

        <x-heading title="포럼 글 등록" :subtitle="$config['subtitle'] ?? ''">
            <x-btn-back-to-list :route="route('admin.cms.forum.index')">
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
                <form id="forumCreateForm" action="{{ route('admin.cms.forum.index.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card border-0 shadow-sm">
                        <ul class="nav nav-tabs nav-tabs-custom" id="forumTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab"
                                        data-bs-target="#basic" type="button" role="tab">
                                        기본정보
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="author-tab" data-bs-toggle="tab" data-bs-target="#author"
                                        type="button" role="tab">
                                        작성자
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage"
                                        type="button" role="tab">
                                        관리
                                    </button>
                                </li>
                            </ul>

                        <div class="card-body">
                            <div class="tab-content" id="forumTabContent">
                                <!-- 기본정보 탭 -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                    <div class="p-4">
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">제목 <span
                                                    class="text-danger">*</span></label>
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
                                                            @foreach ($categories ?? [] as $category)
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
                                                        <a href="{{ route('admin.cms.forum.index.category') }}"
                                                            class="btn btn-outline-secondary" title="카테고리 관리">
                                                            <i class="bi bi-gear"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="form-text">
                                                    카테고리가 없으면
                                                    <a href="{{ route('admin.cms.forum.index.category.create') }}"
                                                        target="_blank">여기서 생성</a>하세요.
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
                                            <label class="col-sm-2 col-form-label">이미지 첨부</label>
                                            <div class="col-sm-10">
                                                <!-- 드래그 앤 드롭 영역 -->
                                                <div id="dropZoneAdmin" class="border border-dashed border-secondary rounded p-4 text-center mb-3"
                                                     style="min-height: 150px; transition: all 0.3s ease;">
                                                    <div id="dropZoneContentAdmin">
                                                        <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
                                                        <p class="text-muted mb-2">
                                                            <strong>드래그 앤 드롭</strong>으로 이미지를 업로드하거나<br>
                                                            <strong>Ctrl+V</strong>로 클립보드 이미지를 붙여넣기하세요
                                                        </p>
                                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('imagesAdmin').click()">
                                                            <i class="bi bi-folder2-open"></i> 파일 선택
                                                        </button>
                                                        <p class="small text-muted mt-2 mb-0">
                                                            최대 10개 · JPG, PNG, GIF, WebP · 각 파일 최대 5MB
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- 숨겨진 파일 입력 -->
                                                <input type="file"
                                                       class="d-none @error('images.*') is-invalid @enderror"
                                                       id="imagesAdmin"
                                                       name="images[]"
                                                       multiple
                                                       accept="image/*">

                                                @error('images.*')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror

                                                <!-- 이미지 미리보기 및 관리 영역 -->
                                                <div id="imagePreviewAdmin" class="mt-3" style="display: none;">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0">선택된 이미지 (<span id="imageCountAdmin">0</span>/10)</h6>
                                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('imagesAdmin').click()">
                                                            <i class="bi bi-plus-circle"></i> 이미지 추가
                                                        </button>
                                                    </div>
                                                    <div id="imageListAdmin" class="row g-3">
                                                        <!-- 미리보기 이미지들이 여기에 표시됩니다 -->
                                                    </div>
                                                </div>
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
                        <a href="{{ route('admin.cms.forum.index') }}" class="btn btn-secondary">취소</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle me-1"></i> 등록
                        </button>
                        <button type="button" class="btn btn-primary d-none" id="loadingBtn" disabled>
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            등록 중...
                        </button>
                    </div>
                </form>

            </x-content-main>

            <x-content-side>

                @includeIf("jiny-post::admin.forum.help")

            </x-content-side>
        </x-content>


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
                padding: 0.75rem 1rem;
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

            /* 이미지 탭 스타일 */
            .nav-pills .nav-link {
                border-radius: 0.375rem;
            }

            .nav-pills .nav-link.active {
                background-color: #0d6efd;
            }

            /* 이미지 미리보기 스타일 */
            #uploadImagePreview,
            #urlImagePreview {
                transition: all 0.3s ease;
            }

            #uploadPreviewImg,
            #urlPreviewImg {
                transition: transform 0.2s ease;
            }

            #uploadPreviewImg:hover,
            #urlPreviewImg:hover {
                transform: scale(1.05);
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

                // 폼 및 버튼 요소
                const forumCreateForm = document.getElementById('forumCreateForm');
                const submitBtn = document.getElementById('submitBtn');
                const loadingBtn = document.getElementById('loadingBtn');

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

                /**
                 * AJAX 폼 제출 처리
                 */
                if (forumCreateForm) {
                    forumCreateForm.addEventListener('submit', function(e) {
                        e.preventDefault(); // 기본 폼 제출 방지

                        // 유효성 검사
                        if (!validateForm()) {
                            return;
                        }

                        // 버튼 상태 변경 (로딩 상태)
                        showLoadingState();

                        // 기존 오류 메시지 제거
                        clearValidationErrors();

                        // FormData 생성
                        const formData = new FormData(forumCreateForm);

                        // 폼 제출 전 디버깅 로그
                        const imageInput = document.getElementById('imagesAdmin');
                        console.log('=== Admin 포럼 생성 폼 제출 디버깅 ===');
                        console.log('selectedFilesAdmin:', selectedFilesAdmin);
                        console.log('imageInput.files:', imageInput.files);
                        console.log('imageInput.files.length:', imageInput.files.length);

                        // 선택된 파일들을 직접 FormData에 추가 (보험용)
                        if (selectedFilesAdmin.length > 0) {
                            console.log('Admin - Adding files directly to FormData:', selectedFilesAdmin.length);
                            // 기존 images[] 필드 제거
                            formData.delete('images[]');
                            // 새로운 파일들 추가
                            selectedFilesAdmin.forEach(file => {
                                formData.append('images[]', file);
                            });
                        }

                        // FormData 내용 확인
                        console.log('FormData 최종 내용:');
                        for (let [key, value] of formData.entries()) {
                            if (key === 'images[]') {
                                console.log(`  ${key}:`, value.name, value.size, 'bytes');
                            } else {
                                console.log(`  ${key}:`, value);
                            }
                        }

                        // AJAX 요청
                        fetch(forumCreateForm.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        ?.getAttribute('content') ||
                                        document.querySelector('input[name="_token"]')?.value
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // 성공 시 바로 목록 페이지로 이동
                                    window.location.href = '{{ route('admin.cms.forum.index') }}';
                                } else {
                                    // 실패 시 오류 메시지 표시
                                    showErrorMessage(data.message);

                                    // 유효성 검사 오류가 있으면 표시
                                    if (data.errors) {
                                        showValidationErrors(data.errors);
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showErrorMessage('서버와의 통신 중 오류가 발생했습니다.');
                            })
                            .finally(() => {
                                // 버튼 상태 복원
                                hideLoadingState();
                            });
                    });
                }

                /**
                 * 폼 유효성 검사
                 */
                function validateForm() {
                    const title = forumCreateForm.querySelector('input[name="title"]');
                    const content = forumCreateForm.querySelector('textarea[name="content"]');

                    if (!title.value.trim()) {
                        showErrorMessage('제목을 입력해주세요.');
                        title.focus();
                        return false;
                    }

                    if (!content.value.trim()) {
                        showErrorMessage('내용을 입력해주세요.');
                        content.focus();
                        return false;
                    }

                    return true;
                }

                /**
                 * 로딩 상태 표시
                 */
                function showLoadingState() {
                    if (submitBtn) {
                        submitBtn.classList.add('d-none');
                    }
                    if (loadingBtn) {
                        loadingBtn.classList.remove('d-none');
                    }
                }

                /**
                 * 로딩 상태 해제
                 */
                function hideLoadingState() {
                    if (submitBtn) {
                        submitBtn.classList.remove('d-none');
                    }
                    if (loadingBtn) {
                        loadingBtn.classList.add('d-none');
                    }
                }

                /**
                 * 오류 메시지 표시
                 */
                function showErrorMessage(message) {
                    const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

                    const container = document.querySelector('.container-fluid');
                    const heading = container.querySelector('x-heading, .heading, h1');
                    if (heading && heading.nextElementSibling) {
                        heading.nextElementSibling.insertAdjacentHTML('afterend', alertHtml);
                    } else {
                        container.insertAdjacentHTML('afterbegin', alertHtml);
                    }
                }

                /**
                 * 유효성 검사 오류 표시
                 */
                function showValidationErrors(errors) {
                    Object.keys(errors).forEach(fieldName => {
                        const field = forumCreateForm.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            // 기존 오류 메시지 제거
                            const existingError = field.parentNode.querySelector('.text-danger.small');
                            if (existingError) {
                                existingError.remove();
                            }

                            // 새 오류 메시지 추가
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'text-danger small mt-1';
                            errorDiv.textContent = errors[fieldName][0];
                            field.parentNode.appendChild(errorDiv);

                            // 필드에 오류 스타일 추가
                            field.classList.add('is-invalid');
                        }
                    });
                }

                /**
                 * 유효성 검사 오류 제거
                 */
                function clearValidationErrors() {
                    // 모든 오류 메시지 제거
                    const errorMessages = forumCreateForm.querySelectorAll('.text-danger.small');
                    errorMessages.forEach(msg => msg.remove());

                    // 모든 필드에서 오류 스타일 제거
                    const invalidFields = forumCreateForm.querySelectorAll('.is-invalid');
                    invalidFields.forEach(field => field.classList.remove('is-invalid'));
                }

                /**
                 * 다중 이미지 업로드 관리
                 */
                let selectedFilesAdmin = [];
                const maxFilesAdmin = 10;
                const maxFileSizeAdmin = 5 * 1024 * 1024; // 5MB

                function initAdminImageUpload() {
                    const dropZone = document.getElementById('dropZoneAdmin');
                    const imageInput = document.getElementById('imagesAdmin');

                    // 드래그 앤 드롭 이벤트
                    dropZone.addEventListener('dragover', handleDragOverAdmin);
                    dropZone.addEventListener('dragleave', handleDragLeaveAdmin);
                    dropZone.addEventListener('drop', handleDropAdmin);

                    // 파일 입력 이벤트
                    imageInput.addEventListener('change', handleFileSelectAdmin);

                    // 클립보드 붙여넣기 이벤트 (전역)
                    document.addEventListener('paste', handlePasteAdmin);

                    function handleDragOverAdmin(e) {
                        e.preventDefault();
                        dropZone.classList.add('border-primary', 'bg-light');
                        dropZone.style.borderWidth = '2px';
                    }

                    function handleDragLeaveAdmin(e) {
                        e.preventDefault();
                        dropZone.classList.remove('border-primary', 'bg-light');
                        dropZone.style.borderWidth = '1px';
                    }

                    function handleDropAdmin(e) {
                        e.preventDefault();
                        dropZone.classList.remove('border-primary', 'bg-light');
                        dropZone.style.borderWidth = '1px';

                        const files = Array.from(e.dataTransfer.files);
                        addFilesAdmin(files);
                    }

                    function handleFileSelectAdmin(e) {
                        console.log('Admin Create - File select event:', e.target.files);
                        const files = Array.from(e.target.files);
                        console.log('Admin Create - Selected files:', files.map(f => ({name: f.name, size: f.size})));
                        addFilesAdmin(files);
                        e.target.value = '';
                    }

                    function handlePasteAdmin(e) {
                        const items = e.clipboardData.items;
                        for (let item of items) {
                            if (item.type.indexOf('image') !== -1) {
                                e.preventDefault();
                                const file = item.getAsFile();
                                if (file) {
                                    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                                    const newFile = new File([file], `clipboard-${timestamp}.png`, { type: file.type });
                                    addFilesAdmin([newFile]);
                                }
                                break;
                            }
                        }
                    }

                    function addFilesAdmin(files) {
                        for (let file of files) {
                            if (selectedFilesAdmin.length >= maxFilesAdmin) {
                                alert(`최대 ${maxFilesAdmin}개의 이미지만 업로드할 수 있습니다.`);
                                break;
                            }

                            if (!file.type.startsWith('image/')) {
                                alert(`"${file.name}"은 이미지 파일이 아닙니다.`);
                                continue;
                            }

                            if (file.size > maxFileSizeAdmin) {
                                alert(`"${file.name}"이 5MB를 초과합니다.`);
                                continue;
                            }

                            const isDuplicate = selectedFilesAdmin.some(f => f.name === file.name && f.size === file.size);
                            if (isDuplicate) {
                                alert(`"${file.name}"은 이미 추가된 파일입니다.`);
                                continue;
                            }

                            selectedFilesAdmin.push(file);
                        }

                        updateImagePreviewAdmin();
                        updateFileInputAdmin();
                    }
                }

                function updateImagePreviewAdmin() {
                    const imagePreview = document.getElementById('imagePreviewAdmin');
                    const imageList = document.getElementById('imageListAdmin');
                    const imageCount = document.getElementById('imageCountAdmin');

                    if (selectedFilesAdmin.length === 0) {
                        imagePreview.style.display = 'none';
                        return;
                    }

                    imagePreview.style.display = 'block';
                    imageCount.textContent = selectedFilesAdmin.length;
                    imageList.innerHTML = '';

                    selectedFilesAdmin.forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 col-sm-4 col-6';
                            col.innerHTML = `
                                <div class="card">
                                    <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <small class="text-muted d-block text-truncate" title="${file.name}">${file.name}</small>
                                        <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                                        <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeImageByIndexAdmin(${index})">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            imageList.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    });
                }

                function updateFileInputAdmin() {
                    const imageInput = document.getElementById('imagesAdmin');
                    if (!imageInput) return;

                    try {
                        const dt = new DataTransfer();
                        selectedFilesAdmin.forEach(file => {
                            dt.items.add(file);
                        });
                        imageInput.files = dt.files;

                        // 디버깅 로그 추가
                        console.log('Admin Create - Updated file input:', {
                            selectedFilesCount: selectedFilesAdmin.length,
                            inputFilesCount: imageInput.files.length,
                            fileNames: Array.from(imageInput.files).map(f => f.name || 'Unknown'),
                            files: Array.from(imageInput.files)
                        });
                    } catch (error) {
                        console.error('Admin Create - Error updating file input:', error);
                    }
                }

                window.removeImageByIndexAdmin = function(index) {
                    selectedFilesAdmin.splice(index, 1);
                    updateImagePreviewAdmin();
                    updateFileInputAdmin();
                };

                // 이미지 업로드 초기화
                initAdminImageUpload();
            });
        </script>
    @endpush
@endsection
