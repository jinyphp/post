@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '포럼 글 수정')

@section('content')
    <div class="container-fluid">

        <x-heading title="포럼 글 수정" :subtitle="$item->title . ' 글을 수정합니다.'">
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
                <form id="forumEditForm" action="{{ route('admin.cms.forum.index.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

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
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">제목 <span class="text-danger">*</span></label>
                                        <div class="col-sm-10">
                                            <x-input-text name="title" :value="old('title', $item->title)" required placeholder="포럼 글 제목" />
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
                                                                {{ old('categories', $item->categories) == $category->slug ? 'selected' : '' }}>
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
                                                <a href="{{ route('admin.cms.forum.index.category.create') }}" target="_blank">여기서</a>
                                                신규로 작성하세요.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">키워드</label>
                                        <div class="col-sm-10">
                                            <x-input-text name="keyword" :value="old('keyword', $item->keyword ?? '')" placeholder="관련 키워드 (쉼표로 구분)" />
                                        </div>
                                    </div>

                                    <!-- 다중 이미지 업로드 섹션 -->
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">이미지 첨부</label>
                                        <div class="col-sm-10">

                                            <!-- 기존 이미지들 표시 -->
                                            @if(!empty($forumImages) && count($forumImages) > 0)
                                                <div class="mb-4">
                                                    <h6 class="text-muted mb-3">
                                                        <i class="bi bi-images me-1"></i>등록된 이미지 (<span id="existingImageCount">{{ count($forumImages) }}</span>개)
                                                    </h6>
                                                    <div class="row g-3" id="existingImagesContainer">
                                                        @foreach($forumImages as $image)
                                                            <div class="col-md-3 col-sm-4 col-6" data-image-id="{{ $image->id }}">
                                                                <div class="card">
                                                                    <img src="{{ $image->url }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                                                    <div class="card-body p-2">
                                                                        <small class="text-muted d-block text-truncate" title="{{ $image->original_name }}">{{ $image->original_name }}</small>
                                                                        <small class="text-muted">{{ number_format($image->size / 1024, 1) }} KB</small>
                                                                        <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeExistingImage({{ $image->id }}, this)">
                                                                            <i class="bi bi-x"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <!-- 삭제할 이미지 ID들을 저장하는 숨겨진 필드 -->
                                                    <input type="hidden" name="remove_image_ids" id="removeImageIds" value="">
                                                </div>
                                            @endif

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
                                                        최대 {{ $forumSettings['max_images_per_post'] ?? 10 }}개 · JPG, PNG, GIF, WebP · 각 파일 최대 {{ $forumSettings['max_file_size_mb'] ?? 5 }}MB
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- 숨겨진 파일 입력 -->
                                            <input type="file"
                                                   class="d-none"
                                                   id="imagesAdmin"
                                                   name="images[]"
                                                   multiple
                                                   accept="image/*">

                                            <!-- 새 이미지 미리보기 및 관리 영역 -->
                                            <div id="newImagePreviewAdmin" class="mt-3" style="display: none;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">새로 추가할 이미지 (<span id="newImageCountAdmin">0</span>/{{ $forumSettings['max_images_per_post'] ?? 10 }})</h6>
                                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('imagesAdmin').click()">
                                                        <i class="bi bi-plus-circle"></i> 이미지 추가
                                                    </button>
                                                </div>
                                                <div id="newImageListAdmin" class="row g-3">
                                                    <!-- 새 이미지 미리보기들이 여기에 표시됩니다 -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">내용 <span class="text-danger">*</span></label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" name="content" rows="10" required placeholder="포럼 글 내용을 입력하세요">{{ old('content', $item->content) }}</textarea>
                                            @error('content')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- 작성자 탭 -->
                                <div class="tab-pane fade" id="author" role="tabpanel">
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">작성자</label>
                                        <div class="col-sm-10">
                                            <x-input-text name="name" :value="old('name', $item->name)" placeholder="작성자명" />
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">이메일</label>
                                        <div class="col-sm-10">
                                            <x-input-text type="email" name="email" :value="old('email', $item->email)" placeholder="작성자 이메일" />
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">비밀번호</label>
                                        <div class="col-sm-10">
                                            <x-input-text type="password" name="password" placeholder="비회원 글 수정 시 비밀번호" />
                                            <div class="form-text">
                                                비회원 글의 경우 비밀번호를 입력하세요. 회원 글의 경우 입력하지 않아도 됩니다.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 관리 탭 -->
                                <div class="tab-pane fade" id="manage" role="tabpanel">
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">조회수</label>
                                        <div class="col-sm-10">
                                            <x-input-number name="click" :value="old('click', $item->click ?? 0)" />
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">좋아요</label>
                                        <div class="col-sm-10">
                                            <x-input-number name="like" :value="old('like', $item->like ?? 0)" />
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">순위</label>
                                        <div class="col-sm-10">
                                            <x-input-number name="rank" :value="old('rank', $item->rank ?? 0)" />
                                            <div class="form-text">
                                                숫자가 높을수록 상위에 노출됩니다.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light d-flex justify-content-end gap-2">
                            <x-btn-reset>취소</x-btn-reset>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle me-1"></i>
                                수정
                            </button>
                            <button type="button" class="btn btn-primary d-none" id="loadingBtn" disabled>
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                수정 중...
                            </button>
                        </div>
                    </div>
                </form>

            </x-content-main>

            <x-content-side>
                <x-help title="포럼 글 수정 가이드" icon="bi-book" icon-color="text-info">
                    <x-help-title icon="bi-check-circle">필수 입력 사항</x-help-title>
                    <p class="small text-muted mb-3">제목과 내용은 반드시 입력해야 합니다.</p>

                    <x-help-title icon="bi-tags">카테고리 설정</x-help-title>
                    <p class="small text-muted mb-3">적절한 카테고리를 선택하면 사용자들이 글을 쉽게 찾을 수 있습니다.</p>

                    <x-help-title icon="bi-image">이미지 수정</x-help-title>
                    <p class="small text-muted mb-3">기존 이미지를 유지하거나 새 이미지로 교체할 수 있습니다.</p>

                    <x-help-title icon="bi-file-earmark-text">내용 수정 팁</x-help-title>
                    <p class="small text-muted mb-0">명확하고 구체적인 내용으로 수정하여 더 나은 정보를 제공하세요.</p>
                </x-help>

                <x-help title="이미지 업로드" icon="bi-image" icon-color="text-success">
                    <x-help-title icon="bi-upload">지원 형식</x-help-title>
                    <p class="small text-muted mb-3">JPG, JPEG, PNG, GIF, WebP 형식을 지원합니다.</p>

                    <x-help-title icon="bi-hdd">파일 크기</x-help-title>
                    <p class="small text-muted mb-3">최대 10MB까지 업로드 가능합니다.</p>

                    <x-help-title icon="bi-eye">미리보기</x-help-title>
                    <p class="small text-muted mb-0">업로드 전에 이미지를 미리 확인할 수 있습니다.</p>
                </x-help>

                <x-help title="작성자 정보" icon="bi-person" icon-color="text-primary">
                    <x-help-title icon="bi-person">기존 정보 유지</x-help-title>
                    <p class="small text-muted mb-3">기존 작성자 정보가 자동으로 채워집니다.</p>

                    <x-help-title icon="bi-lock">비밀번호 변경</x-help-title>
                    <p class="small text-muted mb-0">비회원 글의 경우 비밀번호 변경이 가능합니다.</p>
                </x-help>

                <x-help title="관리 옵션" icon="bi-gear" icon-color="text-secondary">
                    <x-help-title icon="bi-bar-chart">조회수/좋아요</x-help-title>
                    <p class="small text-muted mb-3">기존 통계 값을 조정할 수 있습니다.</p>

                    <x-help-title icon="bi-sort-numeric-down">순위 설정</x-help-title>
                    <p class="small text-muted mb-0">글의 순위를 변경하여 노출 순서를 조정할 수 있습니다.</p>
                </x-help>
            </x-content-side>
        </x-content>


    </div>

    <style>
        .nav-tabs-custom {
            border-bottom: 1px solid #dee2e6;
            margin: 0;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1rem;
        }
        .nav-tabs-custom .nav-link:hover {
            background-color: #f8f9fa;
            color: #495057;
            border-bottom-color: #dee2e6;
        }
        .nav-tabs-custom .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background: none;
        }
        .tab-content {
            padding: 1.5rem 0;
        }
    </style>

    <script>
        // 다중 이미지 업로드 관련 변수
        let selectedFilesAdmin = [];
        let removedImageIds = [];
        const maxFilesAdmin = {{ $forumSettings['max_images_per_post'] ?? 10 }};
        const maxFileSizeAdmin = {{ $forumSettings['max_file_size_mb'] ?? 5 }} * 1024 * 1024;

        document.addEventListener('DOMContentLoaded', function() {
            const forumEditForm = document.getElementById('forumEditForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingBtn = document.getElementById('loadingBtn');

            // 다중 이미지 업로드 초기화
            initAdminImageUpload();

            // 폼 제출 처리
            if (forumEditForm) {
                forumEditForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // 폼 제출 전 디버깅 로그
                    const imageInput = document.getElementById('imagesAdmin');
                    console.log('=== Admin 폼 제출 디버깅 ===');
                    console.log('selectedFilesAdmin:', selectedFilesAdmin);
                    console.log('imageInput.files:', imageInput.files);
                    console.log('imageInput.files.length:', imageInput.files.length);

                    // 버튼 상태 변경
                    submitBtn.classList.add('d-none');
                    loadingBtn.classList.remove('d-none');

                    // FormData 생성
                    const formData = new FormData(this);

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

                    // Laravel method spoofing for PUT request
                    formData.append('_method', 'PUT');

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 성공 시 바로 리다이렉트
                            window.location.href = '{{ route("admin.cms.forum.index") }}';
                        } else {
                            throw new Error(data.message || '수정 중 오류가 발생했습니다.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('수정 중 오류가 발생했습니다: ' + error.message);

                        // 버튼 상태 복원
                        submitBtn.classList.remove('d-none');
                        loadingBtn.classList.add('d-none');
                    });
                });
            }
        });

        function initAdminImageUpload() {
            const dropZone = document.getElementById('dropZoneAdmin');
            const imageInput = document.getElementById('imagesAdmin');
            const imagePreview = document.getElementById('newImagePreviewAdmin');
            const imageList = document.getElementById('newImageListAdmin');
            const imageCount = document.getElementById('newImageCountAdmin');

            if (!dropZone || !imageInput) return;

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
                console.log('Admin - File select event:', e.target.files);
                const files = Array.from(e.target.files);
                console.log('Admin - Selected files:', files.map(f => ({name: f.name, size: f.size})));
                addFilesAdmin(files);
                // 파일 입력 필드 초기화 (같은 파일 재선택 가능하도록)
                e.target.value = '';
            }

            function handlePasteAdmin(e) {
                const items = e.clipboardData.items;
                for (let item of items) {
                    if (item.type.indexOf('image') !== -1) {
                        e.preventDefault();
                        const file = item.getAsFile();
                        if (file) {
                            // 클립보드 이미지에 이름 생성
                            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                            const newFile = new File([file], `clipboard-${timestamp}.png`, { type: file.type });
                            addFilesAdmin([newFile]);
                        }
                        break;
                    }
                }
            }
        }

        function addFilesAdmin(files) {
            for (let file of files) {
                // 파일 개수 제한 확인
                if (selectedFilesAdmin.length >= maxFilesAdmin) {
                    alert(`최대 ${maxFilesAdmin}개의 이미지만 업로드할 수 있습니다.`);
                    break;
                }

                // 이미지 파일인지 확인
                if (!file.type.startsWith('image/')) {
                    alert(`"${file.name}"은 이미지 파일이 아닙니다.`);
                    continue;
                }

                // 파일 크기 확인
                if (file.size > maxFileSizeAdmin) {
                    alert(`"${file.name}"이 {{ $forumSettings['max_file_size_mb'] ?? 5 }}MB를 초과합니다.`);
                    continue;
                }

                // 중복 파일 확인 (이름과 크기로)
                const isDuplicate = selectedFilesAdmin.some(f => f.name === file.name && f.size === file.size);
                if (isDuplicate) {
                    alert(`"${file.name}"은 이미 추가된 파일입니다.`);
                    continue;
                }

                // 파일 추가
                selectedFilesAdmin.push(file);
            }

            updateNewImagePreviewAdmin();
            updateFileInputAdmin();
        }

        function updateNewImagePreviewAdmin() {
            const imagePreview = document.getElementById('newImagePreviewAdmin');
            const imageList = document.getElementById('newImageListAdmin');
            const imageCount = document.getElementById('newImageCountAdmin');

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
                                <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeNewImageByIndexAdmin(${index})">
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

            // DataTransfer를 사용하여 FileList 객체 생성
            try {
                const dt = new DataTransfer();
                selectedFilesAdmin.forEach(file => {
                    dt.items.add(file);
                });
                imageInput.files = dt.files;

                // 디버깅 로그 추가
                console.log('Admin - Updated file input:', {
                    selectedFilesCount: selectedFilesAdmin.length,
                    inputFilesCount: imageInput.files.length,
                    fileNames: Array.from(imageInput.files).map(f => f.name || 'Unknown'),
                    files: Array.from(imageInput.files)
                });
            } catch (error) {
                console.error('Admin - Error updating file input:', error);
            }
        }

        function removeNewImageByIndexAdmin(index) {
            selectedFilesAdmin.splice(index, 1);
            updateNewImagePreviewAdmin();
            updateFileInputAdmin();
        }

        // 기존 이미지 제거 함수
        function removeExistingImage(imageId, button) {
            if (confirm('이 이미지를 삭제하시겠습니까?')) {
                // 이미지 카드 제거
                const imageCard = button.closest('[data-image-id]');
                imageCard.remove();

                // 삭제할 이미지 ID 목록에 추가
                removedImageIds.push(imageId);
                document.getElementById('removeImageIds').value = removedImageIds.join(',');

                // 기존 이미지 개수 업데이트
                const existingCount = document.querySelectorAll('#existingImagesContainer [data-image-id]').length;
                const existingImageCountEl = document.getElementById('existingImageCount');
                if (existingImageCountEl) {
                    existingImageCountEl.textContent = existingCount;
                }

                // 기존 이미지가 모두 삭제되면 컨테이너 숨기기
                if (existingCount === 0) {
                    const existingImagesContainer = document.querySelector('#existingImagesContainer').closest('.mb-4');
                    if (existingImagesContainer) {
                        existingImagesContainer.style.display = 'none';
                    }
                }
            }
        }
    </script>
@endsection