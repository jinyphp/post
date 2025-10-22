@extends('jiny-site::layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">포럼 글 수정</h2>
                    <p class="text-muted mb-0">글을 수정해보세요</p>
                </div>
                <div>
                    <a href="{{ route('forum.show', $forum->id) }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-eye"></i> 글 보기
                    </a>
                    <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
                </div>
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
                    수정하신 글은 관리자 승인 후 게시됩니다.
                </div>
            @endif

            <!-- 글 수정 폼 -->
            <div class="card">
                <div class="card-body">
                    <form id="forumEditForm" action="{{ route('forum.update', $forum->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- 제목 -->
                        <div class="mb-3">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $forum->title) }}"
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
                                   value="{{ old('categories', $forum->categories) }}"
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
                                       value="{{ old('tags', $forum->tags) }}"
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
                                      required>{{ old('content', $forum->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이미지 관리 (파일 업로드가 활성화된 경우만) -->
                        @if($forumSettings['enable_file_upload'])
                            <div class="mb-3">
                                <label class="form-label">이미지 관리</label>

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
                                                            <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeExistingImageEdit({{ $image->id }}, this)">
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

                                <!-- 새 이미지 미리보기 및 관리 영역 -->
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">새로 추가할 이미지 (<span id="imageCount">0</span>/{{ $forumSettings['max_images_per_post'] }})</h6>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('images').click()">
                                            <i class="bi bi-plus-circle"></i> 이미지 추가
                                        </button>
                                    </div>
                                    <div id="imageList" class="row g-3">
                                        <!-- 새 이미지 미리보기들이 여기에 표시됩니다 -->
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 글 정보 -->
                        <div class="bg-light p-3 rounded mb-3">
                            <small class="text-muted">
                                <strong>작성 정보:</strong><br>
                                작성자: {{ $forum->name ?? 'Unknown' }}<br>
                                작성일: {{ $forum->created_at }}<br>
                                @if($forum->updated_at && $forum->updated_at != $forum->created_at)
                                    최종 수정: {{ $forum->updated_at }}<br>
                                @endif
                                조회수: {{ $forum->click ?? 0 }}<br>
                                좋아요: {{ $forum->like ?? 0 }}
                            </small>
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('forum.show', $forum->id) }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> 취소
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-danger me-2" onclick="confirmDelete()">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 수정 완료
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- 삭제 폼 (숨김) -->
                    <form id="deleteForm" action="{{ route('forum.destroy', $forum->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // 텍스트 에디터 및 이미지 업로드 기능
    document.addEventListener('DOMContentLoaded', function() {
        const contentTextarea = document.getElementById('content');

        // 자동 높이 조절
        contentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // 초기 높이 설정
        contentTextarea.style.height = 'auto';
        contentTextarea.style.height = (contentTextarea.scrollHeight) + 'px';

        @if($forumSettings['enable_file_upload'])
        initImageUploadEdit();
        @endif
    });

    @if($forumSettings['enable_file_upload'])
    // 다중 이미지 업로드 관련 변수 (수정 페이지용)
    let selectedFilesEdit = [];
    let removedImageIdsEdit = [];
    const maxFilesEdit = {{ $forumSettings['max_images_per_post'] }};
    const maxFileSizeEdit = {{ $forumSettings['max_file_size_mb'] }} * 1024 * 1024;

    function initImageUploadEdit() {
        const dropZone = document.getElementById('dropZone');
        const imageInput = document.getElementById('images');
        const imagePreview = document.getElementById('imagePreview');
        const imageList = document.getElementById('imageList');
        const imageCount = document.getElementById('imageCount');

        if (!dropZone || !imageInput) return;

        // 드래그 앤 드롭 이벤트
        dropZone.addEventListener('dragover', handleDragOverEdit);
        dropZone.addEventListener('dragleave', handleDragLeaveEdit);
        dropZone.addEventListener('drop', handleDropEdit);

        // 파일 입력 이벤트
        imageInput.addEventListener('change', handleFileSelectEdit);

        // 클립보드 붙여넣기 이벤트 (전역)
        document.addEventListener('paste', handlePasteEdit);

        function handleDragOverEdit(e) {
            e.preventDefault();
            dropZone.classList.add('border-primary', 'bg-light');
            dropZone.style.borderWidth = '2px';
        }

        function handleDragLeaveEdit(e) {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-light');
            dropZone.style.borderWidth = '1px';
        }

        function handleDropEdit(e) {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-light');
            dropZone.style.borderWidth = '1px';

            const files = Array.from(e.dataTransfer.files);
            addFilesEdit(files);
        }

        function handleFileSelectEdit(e) {
            console.log('🔍 Site Forum Edit - File selection triggered');
            console.log('Files selected from input:', e.target.files.length);

            const files = Array.from(e.target.files);

            // 선택된 파일들 로그
            files.forEach((file, index) => {
                console.log(`  Selected file ${index + 1}:`, file.name, `(${(file.size / 1024).toFixed(1)} KB)`);
            });

            addFilesEdit(files);
            // 파일 입력 필드 초기화 (같은 파일 재선택 가능하도록)
            e.target.value = '';
        }

        function handlePasteEdit(e) {
            const items = e.clipboardData.items;
            for (let item of items) {
                if (item.type.indexOf('image') !== -1) {
                    e.preventDefault();
                    const file = item.getAsFile();
                    if (file) {
                        // 클립보드 이미지에 이름 생성
                        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                        const newFile = new File([file], `clipboard-${timestamp}.png`, { type: file.type });
                        addFilesEdit([newFile]);
                    }
                    break;
                }
            }
        }
    }

    function addFilesEdit(files) {
        console.log('📁 Site Forum Edit - Adding files:', files.length);

        for (let file of files) {
            // 파일 개수 제한 확인
            if (selectedFilesEdit.length >= maxFilesEdit) {
                alert(`최대 ${maxFilesEdit}개의 이미지만 업로드할 수 있습니다.`);
                break;
            }

            // 이미지 파일인지 확인
            if (!file.type.startsWith('image/')) {
                alert(`"${file.name}"은 이미지 파일이 아닙니다.`);
                continue;
            }

            // 파일 크기 확인
            if (file.size > maxFileSizeEdit) {
                alert(`"${file.name}"이 {{ $forumSettings['max_file_size_mb'] }}MB를 초과합니다.`);
                continue;
            }

            // 중복 파일 확인 (이름과 크기로)
            const isDuplicate = selectedFilesEdit.some(f => f.name === file.name && f.size === file.size);
            if (isDuplicate) {
                alert(`"${file.name}"은 이미 추가된 파일입니다.`);
                continue;
            }

            // 파일 추가
            selectedFilesEdit.push(file);
            console.log(`📎 Site Forum Edit - File added:`, file.name, `(${(file.size / 1024).toFixed(1)} KB)`);
        }

        console.log('📋 Site Forum Edit - Total selected files:', selectedFilesEdit.length);

        updateImagePreviewEdit();
        updateFileInputEdit();
    }

    function updateImagePreviewEdit() {
        const imagePreview = document.getElementById('imagePreview');
        const imageList = document.getElementById('imageList');
        const imageCount = document.getElementById('imageCount');

        if (selectedFilesEdit.length === 0) {
            imagePreview.style.display = 'none';
            return;
        }

        imagePreview.style.display = 'block';
        imageCount.textContent = selectedFilesEdit.length;
        imageList.innerHTML = '';

        selectedFilesEdit.forEach((file, index) => {
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
                            <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeImageByIndexEdit(${index})">
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

    function updateFileInputEdit() {
        const imageInput = document.getElementById('images');
        if (!imageInput) return;

        // FileList 객체 생성
        const dt = new DataTransfer();
        selectedFilesEdit.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;

        console.log(`🔄 Site Forum Edit - File input updated with ${imageInput.files.length} files`);
    }

    function removeImageByIndexEdit(index) {
        selectedFilesEdit.splice(index, 1);
        updateImagePreviewEdit();
        updateFileInputEdit();
    }

    // 기존 이미지 제거 함수
    function removeExistingImageEdit(imageId, button) {
        if (confirm('이 이미지를 삭제하시겠습니까?')) {
            // 이미지 카드 제거
            const imageCard = button.closest('[data-image-id]');
            imageCard.remove();

            // 삭제할 이미지 ID 목록에 추가
            removedImageIdsEdit.push(imageId);
            document.getElementById('removeImageIds').value = removedImageIdsEdit.join(',');

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
    @endif

    // 삭제 확인
    function confirmDelete() {
        if (confirm('정말로 이 글을 삭제하시겠습니까?\n삭제된 글은 복구할 수 없습니다.')) {
            document.getElementById('deleteForm').submit();
        }
    }

    // 폼 제출 시 콘솔 로그 기록 (디버깅용)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('forumEditForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('=== 포럼 수정 폼 제출 데이터 ===');

                // FormData 객체 생성하여 모든 데이터 확인
                const formData = new FormData(form);

                // 텍스트 필드 데이터 로그
                console.log('텍스트 필드:');
                for (let [key, value] of formData.entries()) {
                    if (key !== 'images[]') {
                        console.log(`  ${key}: ${value}`);
                    }
                }

                // 이미지 파일 데이터 로그
                const imageFiles = formData.getAll('images[]');
                console.log('이미지 파일:');
                console.log(`  총 ${imageFiles.length}개 파일`);
                imageFiles.forEach((file, index) => {
                    if (file instanceof File && file.name) {
                        console.log(`  ${index + 1}. ${file.name} (${(file.size / 1024).toFixed(1)} KB, ${file.type})`);
                    }
                });

                @if($forumSettings['enable_file_upload'])
                // 선택된 파일 배열 정보 확인
                if (window.selectedFilesEdit) {
                    console.log('선택된 파일 배열 (selectedFilesEdit):');
                    console.log(`  총 ${selectedFilesEdit.length}개 파일`);
                    selectedFilesEdit.forEach((file, index) => {
                        console.log(`  ${index + 1}. ${file.name} (${(file.size / 1024).toFixed(1)} KB, ${file.type})`);
                    });
                }
                @endif

                // 삭제할 이미지 ID 확인
                const removeIds = formData.get('remove_image_ids');
                if (removeIds) {
                    console.log('삭제할 이미지 ID:', removeIds.split(','));
                }

                // 요청 타입 확인
                console.log('요청 정보:');
                console.log('  Form Method:', form.method);
                console.log('  Form Action:', form.action);
                console.log('  Form enctype:', form.enctype);
                console.log('  AJAX 요청 여부: false (일반 form 제출)');

                console.log('=== 폼 제출 완료 ===');
                // e.preventDefault(); // 실제 제출을 막으려면 주석 해제
            });
        }
    });
</script>
@endpush
@endsection