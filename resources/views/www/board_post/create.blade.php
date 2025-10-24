@extends($layout ?? 'jiny-site::layouts.app')

@section('title', '새 글 작성 - ' . $board->title)

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $board->title }}</h2>
            <p class="text-muted mb-0">{{ $parent ? '답글 작성' : '새 글 작성' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('board.index', $code) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> 목록으로
            </a>
        </div>
    </div>

    <hr>

    @if($parent)
        <!-- Parent Post Info -->
        <div class="alert alert-info">
            <h6 class="alert-heading">
                <i class="bi bi-reply me-2"></i>Replying to:
            </h6>
            <p class="mb-0"><strong>{{ $parent->title }}</strong></p>
            @if($parent->content)
                <small class="text-muted">{{ Str::limit(strip_tags($parent->content), 150) }}</small>
            @endif
        </div>
    @endif

    <!-- Create Form -->
    <form action="{{ route('board.store', $code) }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if($parent)
            <input type="hidden" name="parent_id" value="{{ $parent->id }}">
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Content Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text me-2"></i>게시글 내용
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required
                                   placeholder="게시글 제목을 입력하세요">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="12" required
                                      placeholder="게시글 내용을 입력하세요...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- File Uploads Section -->
                @if($board->allow_file_upload || $board->allow_image_upload)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-paperclip me-2"></i>파일 첨부
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($board->allow_image_upload)
                        <!-- Image Upload Section -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-images me-1"></i>이미지 업로드
                            </label>
                            <div class="image-upload-container">
                                <div class="image-drop-zone border-2 border-dashed border-primary rounded p-4 text-center"
                                     id="imageDropZone">
                                    <i class="bi bi-cloud-upload fs-1 text-primary d-block mb-2"></i>
                                    <p class="mb-2">이미지를 드래그해서 놓거나 <span class="text-primary">파일 선택</span></p>
                                    <small class="text-muted">
                                        최대 {{ $board->max_file_count }}개 파일 | JPG, PNG, GIF | 파일당 5MB 이하
                                    </small>
                                    <input type="file" id="imageInput" name="images[]" multiple
                                           accept="image/*" class="d-none">
                                </div>
                                <div id="imagePreview" class="mt-3 d-none">
                                    <div class="row g-2" id="imageGrid"></div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($board->allow_file_upload)
                        <!-- File Upload Section -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-file-earmark me-1"></i>파일 첨부
                            </label>
                            <div class="file-upload-container">
                                <div class="file-drop-zone border-2 border-dashed border-success rounded p-4 text-center"
                                     id="fileDropZone">
                                    <i class="bi bi-file-earmark-arrow-up fs-1 text-success d-block mb-2"></i>
                                    <p class="mb-2">파일을 드래그해서 놓거나 <span class="text-success">파일 선택</span></p>
                                    <small class="text-muted">
                                        최대 {{ $board->max_file_count }}개 파일 |
                                        @if($board->blocked_extensions)
                                            금지된 확장자: {{ $board->blocked_extensions }}
                                        @else
                                            대부분의 파일 형식 허용
                                        @endif
                                    </small>
                                    <input type="file" id="fileInput" name="attachments[]" multiple class="d-none">
                                </div>
                                <div id="fileList" class="mt-3 d-none"></div>
                            </div>
                        </div>
                        @endif

                        <!-- Upload Progress -->
                        <div id="uploadProgress" class="d-none">
                            <div class="progress mb-2">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted" id="uploadStatus">업로드 준비 중...</small>
                        </div>

                        <!-- Upload Guidelines -->
                        <div class="border border-info p-3 rounded">
                            <h6 class="text-info mb-2">
                                <i class="bi bi-info-circle me-1"></i>업로드 가이드라인
                            </h6>
                            <ul class="mb-0 small">
                                <li>게시물당 최대 {{ $board->max_file_count }}개 파일</li>
                                <li>파일당 최대 5MB</li>
                                @if($board->blocked_extensions)
                                <li class="text-danger">금지된 파일 형식: {{ $board->blocked_extensions }}</li>
                                @endif
                                <li>파일은 자동으로 보안 검사됩니다</li>
                                <li>이미지는 웹 표시용으로 최적화됩니다</li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                @if(!$isAuthenticated)
                <!-- Guest Author Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person me-2"></i>작성자 정보
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Author Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required
                                   placeholder="작성자 이름">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="example@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">답글 알림을 받을 이메일 (선택사항)</div>
                        </div>

                        <!-- Password (for guest posts) -->
                        <div class="mb-3">
                            <label for="password" class="form-label">비밀번호</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="게시글 수정/삭제용 비밀번호">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">게시글 수정/삭제 시 필요 (선택사항)</div>
                        </div>
                    </div>
                </div>
                @else
                <!-- Logged-in User Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person-check me-2"></i>작성자 정보
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle fs-3 text-primary me-3"></i>
                            <div>
                                <div class="fw-semibold">{{ $user->name ?? '사용자' }}</div>
                                <small class="text-muted">{{ $user->email ?? '' }}</small>
                            </div>
                        </div>
                        <div class="form-text mt-2">로그인된 사용자로 게시글이 작성됩니다.</div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <!-- Alert Messages -->
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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ $parent ? '답글 작성' : '게시글 작성' }}
                            </button>
                            <a href="{{ route('board.index', $code) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>취소
                            </a>
                        </div>

                        @if($parent)
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    "{{ $parent->title }}"에 대한 답글로 작성됩니다.
                                </small>
                            </div>
                        @endif

                        <!-- Board Info -->
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>게시판 정보
                            </h6>
                            <ul class="list-unstyled small text-muted mb-0">
                                @if($board->allow_image_upload)
                                    <li><i class="bi bi-check-circle-fill text-success me-1"></i>이미지 업로드 가능</li>
                                @endif
                                @if($board->allow_file_upload)
                                    <li><i class="bi bi-check-circle-fill text-success me-1"></i>파일 첨부 가능</li>
                                @endif
                                <li><i class="bi bi-info-circle me-1"></i>파일당 최대 5MB</li>
                                @if($board->blocked_extensions)
                                    <li class="text-danger"><i class="bi bi-x-circle me-1"></i>금지 확장자: {{ $board->blocked_extensions }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
/* Form styling */
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Card styling */
.card {
    border-radius: 0.75rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

/* Required field indicator */
.text-danger {
    font-weight: bold;
}

/* Alert styling */
.alert {
    border-radius: 0.5rem;
    border-left: 4px solid #0dcaf0;
}

/* Button styling */
.btn {
    border-radius: 0.5rem;
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-lg-4 .card {
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.getElementById('content');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Character counter for title
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            const maxLength = 255;
            const currentLength = this.value.length;
            const remaining = maxLength - currentLength;

            // Create or update character counter
            let counter = this.parentNode.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('div');
                counter.className = 'char-counter form-text';
                this.parentNode.appendChild(counter);
            }

            counter.textContent = `${currentLength}/${maxLength} characters`;
            counter.style.color = remaining < 20 ? '#dc3545' : '#6c757d';
        });
    }

    // File Upload Management
    const maxFiles = {{ $board->max_file_count ?? 5 }};
    const blockedExtensions = @json(explode(',', $board->blocked_extensions ?? ''));
    let uploadedImages = [];
    let uploadedFiles = [];

    // Image Upload Functionality
    const imageDropZone = document.getElementById('imageDropZone');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const imageGrid = document.getElementById('imageGrid');

    if (imageDropZone && imageInput) {
        // Click to browse
        imageDropZone.addEventListener('click', () => {
            if (uploadedImages.length < maxFiles) {
                imageInput.click();
            }
        });

        // Drag and drop events
        imageDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageDropZone.classList.add('border-primary', 'bg-light');
        });

        imageDropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            imageDropZone.classList.remove('border-primary', 'bg-light');
        });

        imageDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageDropZone.classList.remove('border-primary', 'bg-light');
            handleImageFiles(e.dataTransfer.files);
        });

        // File input change
        imageInput.addEventListener('change', (e) => {
            handleImageFiles(e.target.files);
        });
    }

    // File Upload Functionality
    const fileDropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    if (fileDropZone && fileInput) {
        // Click to browse
        fileDropZone.addEventListener('click', () => {
            if (uploadedFiles.length < maxFiles) {
                fileInput.click();
            }
        });

        // Drag and drop events
        fileDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileDropZone.classList.add('border-success', 'bg-light');
        });

        fileDropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            fileDropZone.classList.remove('border-success', 'bg-light');
        });

        fileDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            fileDropZone.classList.remove('border-success', 'bg-light');
            handleAttachmentFiles(e.dataTransfer.files);
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            handleAttachmentFiles(e.target.files);
        });
    }

    // Handle image files
    function handleImageFiles(files) {
        Array.from(files).forEach(file => {
            if (uploadedImages.length >= maxFiles) {
                showAlert('Maximum ' + maxFiles + ' images allowed', 'warning');
                return;
            }

            if (!file.type.startsWith('image/')) {
                showAlert('Only image files are allowed', 'danger');
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                showAlert('Image file size must be under 5MB', 'danger');
                return;
            }

            if (isBlockedExtension(file.name)) {
                showAlert('File type not allowed: ' + getFileExtension(file.name), 'danger');
                return;
            }

            uploadedImages.push(file);
            displayImagePreview(file, uploadedImages.length - 1);
        });

        updateImageInput();
        toggleImagePreview();
    }

    // Handle attachment files
    function handleAttachmentFiles(files) {
        Array.from(files).forEach(file => {
            if (uploadedFiles.length >= maxFiles) {
                showAlert('Maximum ' + maxFiles + ' files allowed', 'warning');
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                showAlert('File size must be under 5MB', 'danger');
                return;
            }

            if (isBlockedExtension(file.name)) {
                showAlert('File type not allowed: ' + getFileExtension(file.name), 'danger');
                return;
            }

            uploadedFiles.push(file);
            displayFileItem(file, uploadedFiles.length - 1);
        });

        updateFileInput();
        toggleFileList();
    }

    // Display image preview
    function displayImagePreview(file, index) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageItem = document.createElement('div');
            imageItem.className = 'col-6 col-md-4';
            imageItem.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="img-fluid rounded shadow-sm" style="height: 120px; object-fit: cover; width: 100%;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1"
                            onclick="removeImage(${index})" style="width: 24px; height: 24px; font-size: 10px;">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-1 rounded-bottom">
                        <small class="text-truncate d-block">${file.name}</small>
                        <small class="text-muted">${formatFileSize(file.size)}</small>
                    </div>
                </div>
            `;
            imageGrid.appendChild(imageItem);
        };
        reader.readAsDataURL(file);
    }

    // Display file item
    function displayFileItem(file, index) {
        const fileItem = document.createElement('div');
        fileItem.className = 'border rounded p-3 mb-2 bg-light';
        fileItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark fs-4 text-primary me-2"></i>
                    <div>
                        <div class="fw-semibold">${file.name}</div>
                        <small class="text-muted">${formatFileSize(file.size)} • ${getFileExtension(file.name).toUpperCase()}</small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFile(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        fileList.appendChild(fileItem);
    }

    // Remove image
    window.removeImage = function(index) {
        uploadedImages.splice(index, 1);
        updateImageInput();
        refreshImagePreview();
    };

    // Remove file
    window.removeFile = function(index) {
        uploadedFiles.splice(index, 1);
        updateFileInput();
        refreshFileList();
    };

    // Refresh image preview
    function refreshImagePreview() {
        imageGrid.innerHTML = '';
        uploadedImages.forEach((file, index) => {
            displayImagePreview(file, index);
        });
        toggleImagePreview();
    }

    // Refresh file list
    function refreshFileList() {
        fileList.innerHTML = '';
        uploadedFiles.forEach((file, index) => {
            displayFileItem(file, index);
        });
        toggleFileList();
    }

    // Update image input
    function updateImageInput() {
        console.log('🔧 Board Create Images - Updating image input with', uploadedImages.length, 'files');
        const dt = new DataTransfer();
        uploadedImages.forEach(file => {
            dt.items.add(file);
        });
        if (imageInput) {
            imageInput.files = dt.files;
            console.log('✅ Board Create Images - Image input updated, files:', imageInput.files.length);
        }
    }

    // Update file input
    function updateFileInput() {
        const dt = new DataTransfer();
        uploadedFiles.forEach(file => {
            dt.items.add(file);
        });
        if (fileInput) {
            fileInput.files = dt.files;
        }
    }

    // Toggle image preview visibility
    function toggleImagePreview() {
        if (imagePreview) {
            imagePreview.classList.toggle('d-none', uploadedImages.length === 0);
        }
    }

    // Toggle file list visibility
    function toggleFileList() {
        if (fileList) {
            fileList.classList.toggle('d-none', uploadedFiles.length === 0);
        }
    }

    // Utility functions
    function isBlockedExtension(filename) {
        const ext = getFileExtension(filename).toLowerCase();
        return blockedExtensions.map(e => e.trim().toLowerCase()).includes(ext);
    }

    function getFileExtension(filename) {
        return filename.split('.').pop() || '';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endpush
@endsection
