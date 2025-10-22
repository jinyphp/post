@extends('jiny-site::layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">포럼 글쓰기</h2>
                    <p class="text-muted mb-0">새로운 글을 작성해보세요</p>
                </div>
                <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

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

            @if(isset($requires_approval) && $requires_approval)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    작성하신 글은 관리자 승인 후 게시됩니다.
                </div>
            @endif

            <!-- 글쓰기 폼 -->
            <div class="card">
                <div class="card-body">
                    <form id="forumCreateForm" action="{{ route('forum.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- 제목 -->
                        <div class="mb-3">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 카테고리 -->
                        <div class="mb-3">
                            <label for="categories" class="form-label">카테고리</label>
                            <input type="text"
                                   class="form-control @error('categories') is-invalid @enderror"
                                   id="categories"
                                   name="categories"
                                   value="{{ old('categories') }}"
                                   placeholder="예: 공지사항, 자유게시판"
                                   maxlength="255">
                            <small class="form-text text-muted">쉼표(,)로 구분하여 여러 카테고리를 입력할 수 있습니다.</small>
                            @error('categories')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 태그 (설정에서 활성화된 경우만) -->
                        @if($forumSettings['enable_tags'])
                            <div class="mb-3">
                                <label for="tags" class="form-label">태그</label>
                                <input type="text"
                                       class="form-control @error('tags') is-invalid @enderror"
                                       id="tags"
                                       name="tags"
                                       value="{{ old('tags') }}"
                                       placeholder="예: #질문, #팁, #공유"
                                       maxlength="255">
                                <small class="form-text text-muted">
                                    쉼표(,)로 구분하여 최대 {{ $forumSettings['max_tags_per_post'] }}개의 태그를 입력할 수 있습니다.
                                </small>
                                @error('tags')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- 내용 -->
                        <div class="mb-3">
                            <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content"
                                      name="content"
                                      rows="10"
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이미지 업로드 (설정에서 활성화된 경우만) -->
                        @if($forumSettings['enable_file_upload'])
                            <div class="mb-3">
                                <label for="images" class="form-label">이미지 첨부</label>

                                <!-- 드래그 앤 드롭 영역 -->
                                <div id="dropZone" class="border border-dashed border-secondary rounded p-4 text-center mb-3"
                                     style="min-height: 150px; transition: all 0.3s ease;">
                                    <div id="dropZoneContent">
                                        <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
                                        <p class="text-muted mb-2">
                                            <strong>드래그 앤 드롭</strong>으로 이미지를 업로드하거나<br>
                                            <strong>Ctrl+V</strong>로 클립보드 이미지를 붙여넣기하세요
                                        </p>
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('images').click()">
                                            <i class="bi bi-folder2-open"></i> 파일 선택
                                        </button>
                                        <p class="small text-muted mt-2 mb-0">
                                            최대 {{ $forumSettings['max_images_per_post'] }}개 · JPG, PNG, GIF, WebP · 각 파일 최대 {{ $forumSettings['max_file_size_mb'] }}MB
                                        </p>
                                    </div>
                                </div>

                                <!-- 숨겨진 파일 입력 -->
                                <input type="file"
                                       class="d-none @error('images.*') is-invalid @enderror"
                                       id="images"
                                       name="images[]"
                                       multiple
                                       accept="image/*">

                                @error('images.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <!-- 이미지 미리보기 및 관리 영역 -->
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">선택된 이미지 (<span id="imageCount">0</span>/{{ $forumSettings['max_images_per_post'] }})</h6>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('images').click()">
                                            <i class="bi bi-plus-circle"></i> 이미지 추가
                                        </button>
                                    </div>
                                    <div id="imageList" class="row g-3">
                                        <!-- 미리보기 이미지들이 여기에 표시됩니다 -->
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 게스트 사용자 추가 정보 -->
                        @if(!auth()->check())
                            <hr>
                            <h6>작성자 정보</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name"
                                               name="name"
                                               value="{{ old('name') }}"
                                               required
                                               maxlength="100">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">이메일</label>
                                        <input type="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               maxlength="255">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 버튼 -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('forum.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> 취소
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> 글 작성
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 텍스트 에디터 및 이미지 업로드 스크립트 --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contentTextarea = document.getElementById('content');

        // 자동 높이 조절
        contentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        @if($forumSettings['enable_file_upload'])
        initImageUpload();
        @endif

        // 폼 제출 시 디버깅
        const form = document.getElementById('forumCreateForm');
        form.addEventListener('submit', function(e) {
            console.log('🔍 Site Forum Create Form Submission Debug:');
            const formData = new FormData(this);

            // 폼 데이터 로깅
            for (let [key, value] of formData.entries()) {
                if (key === 'images[]') {
                    console.log(`   ${key}:`, value.name, `(${(value.size / 1024).toFixed(1)} KB)`);
                } else {
                    console.log(`   ${key}:`, value);
                }
            }

            // 선택된 파일 정보
            const imageInput = document.getElementById('images');
            if (imageInput && imageInput.files) {
                console.log('📎 Files from input element:', imageInput.files.length);
                for (let i = 0; i < imageInput.files.length; i++) {
                    const file = imageInput.files[i];
                    console.log(`   File ${i + 1}:`, file.name, `(${(file.size / 1024).toFixed(1)} KB)`);
                }
            }

            @if($forumSettings['enable_file_upload'])
            // 선택된 파일 배열 정보
            if (window.selectedFiles) {
                console.log('📋 Selected files array:', window.selectedFiles.length);
                window.selectedFiles.forEach((file, index) => {
                    console.log(`   Selected ${index + 1}:`, file.name, `(${(file.size / 1024).toFixed(1)} KB)`);
                });
            }
            @endif

            console.log('✅ Form will be submitted normally (not AJAX)');
        });
    });

    @if($forumSettings['enable_file_upload'])
    // 이미지 업로드 관련 변수
    window.selectedFiles = [];
    const maxFiles = {{ $forumSettings['max_images_per_post'] }};
    const maxFileSize = {{ $forumSettings['max_file_size_mb'] }} * 1024 * 1024;

    function initImageUpload() {
        const dropZone = document.getElementById('dropZone');
        const imageInput = document.getElementById('images');
        const imagePreview = document.getElementById('imagePreview');
        const imageList = document.getElementById('imageList');
        const imageCount = document.getElementById('imageCount');

        // 드래그 앤 드롭 이벤트
        dropZone.addEventListener('dragover', handleDragOver);
        dropZone.addEventListener('dragleave', handleDragLeave);
        dropZone.addEventListener('drop', handleDrop);

        // 파일 입력 이벤트
        imageInput.addEventListener('change', handleFileSelect);

        // 클립보드 붙여넣기 이벤트 (전역)
        document.addEventListener('paste', handlePaste);

        function handleDragOver(e) {
            e.preventDefault();
            dropZone.classList.add('border-primary', 'bg-light');
            dropZone.style.borderWidth = '2px';
        }

        function handleDragLeave(e) {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-light');
            dropZone.style.borderWidth = '1px';
        }

        function handleDrop(e) {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-light');
            dropZone.style.borderWidth = '1px';

            const files = Array.from(e.dataTransfer.files);
            addFiles(files);
        }

        function handleFileSelect(e) {
            console.log('🔍 Site Forum Create - File selection triggered');
            console.log('Files selected from input:', e.target.files.length);

            const files = Array.from(e.target.files);

            // 선택된 파일들 로그
            files.forEach((file, index) => {
                console.log(`  Selected file ${index + 1}:`, file.name, `(${(file.size / 1024).toFixed(1)} KB)`);
            });

            addFiles(files);
            // 파일 입력 필드 초기화 (같은 파일 재선택 가능하도록)
            e.target.value = '';
        }

        function handlePaste(e) {
            const items = e.clipboardData.items;
            for (let item of items) {
                if (item.type.indexOf('image') !== -1) {
                    e.preventDefault();
                    const file = item.getAsFile();
                    if (file) {
                        // 클립보드 이미지에 이름 생성
                        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                        const newFile = new File([file], `clipboard-${timestamp}.png`, { type: file.type });
                        addFiles([newFile]);
                    }
                    break;
                }
            }
        }

        function addFiles(files) {
            console.log('📁 Site Forum Create - Adding files:', files.length);

            for (let file of files) {
                // 파일 개수 제한 확인
                if (window.selectedFiles.length >= maxFiles) {
                    alert(`최대 ${maxFiles}개의 이미지만 업로드할 수 있습니다.`);
                    break;
                }

                // 이미지 파일인지 확인
                if (!file.type.startsWith('image/')) {
                    alert(`"${file.name}"은 이미지 파일이 아닙니다.`);
                    continue;
                }

                // 파일 크기 확인
                if (file.size > maxFileSize) {
                    alert(`"${file.name}"이 {{ $forumSettings['max_file_size_mb'] }}MB를 초과합니다.`);
                    continue;
                }

                // 중복 파일 확인 (이름과 크기로)
                const isDuplicate = window.selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (isDuplicate) {
                    alert(`"${file.name}"은 이미 추가된 파일입니다.`);
                    continue;
                }

                // 파일 추가
                window.selectedFiles.push(file);
                console.log(`📎 Site Forum Create - File added:`, file.name, `(${(file.size / 1024).toFixed(1)} KB)`);
            }

            console.log('📋 Site Forum Create - Total selected files:', window.selectedFiles.length);

            updateImagePreview();
            updateFileInput();
        }

        function updateImagePreview() {
            if (window.selectedFiles.length === 0) {
                imagePreview.style.display = 'none';
                return;
            }

            imagePreview.style.display = 'block';
            imageCount.textContent = window.selectedFiles.length;
            imageList.innerHTML = '';

            window.selectedFiles.forEach((file, index) => {
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
                                <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeImageByIndex(${index})">
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

        function updateFileInput() {
            // FileList 객체 생성
            const dt = new DataTransfer();
            window.selectedFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        }
    }

    function removeImageByIndex(index) {
        window.selectedFiles.splice(index, 1);
        updateImagePreview();
        updateFileInput();
    }

    // 전역 함수들 (HTML에서 호출)
    function updateImagePreview() {
        const imagePreview = document.getElementById('imagePreview');
        const imageList = document.getElementById('imageList');
        const imageCount = document.getElementById('imageCount');

        if (window.selectedFiles.length === 0) {
            imagePreview.style.display = 'none';
            return;
        }

        imagePreview.style.display = 'block';
        imageCount.textContent = window.selectedFiles.length;
        imageList.innerHTML = '';

        window.selectedFiles.forEach((file, index) => {
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
                            <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeImageByIndex(${index})">
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

    function updateFileInput() {
        const imageInput = document.getElementById('images');
        // FileList 객체 생성
        const dt = new DataTransfer();
        window.selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;

        console.log(`🔄 Site Forum Create - File input updated with ${imageInput.files.length} files`);
    }
    @endif

</script>
@endpush
@endsection