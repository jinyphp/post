@extends('jiny-site::layouts.admin.sidebar')

@section('title', $config['title'] ?? 'Edit Post')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3>{{ $config['title'] ?? 'Edit Post' }}</h3>
            <p class="text-muted mb-0">{{ $config['subtitle'] ?? 'Modify post content and settings' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/admin/cms/board/posts/' . $code) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Posts
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i> Delete Post
            </button>
        </div>
    </div>

    <hr>

    <!-- Edit Form -->
    <form action="{{ url('/admin/cms/board/posts/' . $code . '/' . $post->id . '/edit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Content Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text me-2"></i>Post Content
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $post->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="12"
                                      placeholder="Enter post content...">{{ old('content', $post->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Categories -->
                        <div class="mb-3">
                            <label for="categories" class="form-label">Categories</label>
                            <input type="text" class="form-control @error('categories') is-invalid @enderror"
                                   id="categories" name="categories" value="{{ old('categories', $post->categories) }}"
                                   placeholder="e.g. News, Technology, Sports">
                            @error('categories')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Separate multiple categories with commas</div>
                        </div>
                    </div>
                </div>

                <!-- File Management -->
                @if($board->allow_file_upload || $board->allow_image_upload)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-paperclip me-2"></i>첨부파일 관리
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($board->allow_image_upload)
                        <!-- Existing Images -->
                        @if(count($images) > 0)
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-images me-1"></i>등록된 이미지 (<span id="existingImageCount">{{ count($images) }}</span>개)
                            </h6>
                            <div class="row g-2" id="existingImages">
                                @foreach($images as $image)
                                <div class="col-6 col-md-3" data-image-id="{{ $image->id }}">
                                    <div class="position-relative">
                                        <img src="{{ Storage::url($image->file_path) }}"
                                             class="img-fluid rounded shadow-sm"
                                             style="height: 120px; object-fit: cover; width: 100%;"
                                             alt="{{ $image->alt_text ?? $image->original_name }}">
                                        <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1"
                                                onclick="removeExistingImage({{ $image->id }}, this)"
                                                style="width: 24px; height: 24px; font-size: 10px;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                        <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-1 rounded-bottom">
                                            <small class="text-truncate d-block">{{ $image->original_name }}</small>
                                            <small class="text-muted">{{ number_format($image->file_size / 1024, 1) }}KB</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- New Image Upload -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-images me-1"></i>새 이미지 업로드
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
                        <!-- Existing Attachments -->
                        @if(count($attachments) > 0)
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-paperclip me-1"></i>등록된 첨부파일 (<span id="existingAttachmentCount">{{ count($attachments) }}</span>개)
                            </h6>
                            <div id="existingAttachments">
                                @foreach($attachments as $attachment)
                                <div class="border rounded p-3 mb-2 bg-light" data-attachment-id="{{ $attachment->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark fs-4 text-primary me-2"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $attachment->original_name }}</div>
                                                <small class="text-muted">{{ number_format($attachment->file_size / 1024, 1) }}KB • {{ strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION)) }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ Storage::url($attachment->file_path) }}"
                                               target="_blank"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="removeExistingAttachment({{ $attachment->id }}, this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- New File Upload -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-file-earmark me-1"></i>새 파일 첨부
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

                        <!-- Hidden inputs for deleted files -->
                        <input type="hidden" id="deletedImages" name="remove_image_ids" value="">
                        <input type="hidden" id="deletedAttachments" name="remove_attachment_ids" value="">

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

                <!-- Statistics Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-bar-chart me-2"></i>Post Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="click" class="form-label">Views</label>
                                <input type="number" class="form-control @error('click') is-invalid @enderror"
                                       id="click" name="click" value="{{ old('click', $post->click ?? 0) }}" min="0">
                                @error('click')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="like" class="form-label">Likes</label>
                                <input type="number" class="form-control @error('like') is-invalid @enderror"
                                       id="like" name="like" value="{{ old('like', $post->like ?? 0) }}" min="0">
                                @error('like')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="rank" class="form-label">Rank</label>
                                <input type="number" class="form-control @error('rank') is-invalid @enderror"
                                       id="rank" name="rank" value="{{ old('rank', $post->rank ?? 0) }}" min="0">
                                @error('rank')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Post Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Post Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Post ID:</strong><br>
                                <span class="text-muted">#{{ $post->id }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Created:</strong><br>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                        @if($post->updated_at && $post->updated_at !== $post->created_at)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>Last Updated:</strong><br>
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($post->updated_at)->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        @endif
                        @if($post->parent_id)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>Reply Level:</strong><br>
                                    <span class="badge bg-success">Level {{ $post->level ?? 1 }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Author Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person me-2"></i>Author Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Author Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Author Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $post->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $post->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Media & SEO -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-image me-2"></i>Media & SEO
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Featured Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Featured Image URL</label>
                            <input type="url" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" value="{{ old('image', $post->image) }}"
                                   placeholder="https://example.com/image.jpg">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($post->image)
                                <div class="mt-2">
                                    <img src="{{ $post->image }}" alt="Current image"
                                         class="img-thumbnail" style="max-width: 150px; max-height: 100px;">
                                </div>
                            @endif
                        </div>

                        <!-- Keywords -->
                        <div class="mb-3">
                            <label for="keyword" class="form-label">Keywords</label>
                            <input type="text" class="form-control @error('keyword') is-invalid @enderror"
                                   id="keyword" name="keyword" value="{{ old('keyword', $post->keyword) }}"
                                   placeholder="keyword1, keyword2, keyword3">
                            @error('keyword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror"
                                   id="tags" name="tags" value="{{ old('tags', $post->tags) }}"
                                   placeholder="tag1, tag2, tag3">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Post
                            </button>
                            <a href="{{ url('/admin/cms/board/posts/' . $code) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            @if(!$post->parent_id)
                                <a href="{{ url('/admin/cms/board/posts/' . $code . '/create?parent=' . $post->id) }}"
                                   class="btn btn-outline-success">
                                    <i class="bi bi-reply me-2"></i>Add Reply
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Delete Post
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this post?</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action cannot be undone.
                    @if(isset($post->parent_id) && !$post->parent_id)
                        If this post has replies, you must delete all replies first.
                    @endif
                </div>
                <div class="bg-light p-3 rounded">
                    <strong>Post Title:</strong> {{ $post->title }}<br>
                    <strong>Created:</strong> {{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y H:i') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ url('/admin/cms/board/posts/' . $code . '/' . $post->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete Post
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Form and card styling */
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    border-radius: 0.75rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.btn {
    border-radius: 0.5rem;
    font-weight: 500;
}

.modal-content {
    border-radius: 0.75rem;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.text-danger {
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-lg-4 .card {
        margin-bottom: 1rem;
    }
    .row .col-md-4 {
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// File deletion tracking
let removedImageIds = [];
let removedAttachmentIds = [];

/**
 * Remove existing image from the edit form
 */
function removeExistingImage(imageId, button) {
    if (confirm('이 이미지를 삭제하시겠습니까?')) {
        const imageCard = button.closest('[data-image-id]');
        imageCard.remove();

        removedImageIds.push(imageId);
        document.getElementById('deletedImages').value = removedImageIds.join(',');

        updateImageCount();
    }
}

/**
 * Remove existing attachment from the edit form
 */
function removeExistingAttachment(attachmentId, button) {
    if (confirm('이 파일을 삭제하시겠습니까?')) {
        const attachmentCard = button.closest('[data-attachment-id]');
        attachmentCard.remove();

        removedAttachmentIds.push(attachmentId);
        document.getElementById('deletedAttachments').value = removedAttachmentIds.join(',');

        updateAttachmentCount();
    }
}

/**
 * Update image count display and hide container if empty
 */
function updateImageCount() {
    const existingCount = document.querySelectorAll('#existingImages [data-image-id]').length;
    const countEl = document.getElementById('existingImageCount');

    if (countEl) {
        countEl.textContent = existingCount;
    }

    if (existingCount === 0) {
        const container = document.querySelector('#existingImages').closest('.mb-4');
        if (container) {
            container.style.display = 'none';
        }
    }
}

/**
 * Update attachment count display and hide container if empty
 */
function updateAttachmentCount() {
    const existingCount = document.querySelectorAll('#existingAttachments [data-attachment-id]').length;
    const countEl = document.getElementById('existingAttachmentCount');

    if (countEl) {
        countEl.textContent = existingCount;
    }

    if (existingCount === 0) {
        const container = document.querySelector('#existingAttachments').closest('.mb-4');
        if (container) {
            container.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form enhancements
    initializeTextareaResize();
    initializeTitleCounter();
    initializeUnsavedChangesWarning();
    initializeFileUploads();

    /**
     * Auto-resize content textarea
     */
    function initializeTextareaResize() {
        const textarea = document.getElementById('content');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }
    }

    /**
     * Add character counter to title field
     */
    function initializeTitleCounter() {
        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                const maxLength = 255;
                const currentLength = this.value.length;
                const remaining = maxLength - currentLength;

                let counter = this.parentNode.querySelector('.char-counter');
                if (!counter) {
                    counter = document.createElement('div');
                    counter.className = 'char-counter form-text';
                    this.parentNode.appendChild(counter);
                }

                counter.textContent = `${currentLength}/${maxLength} characters`;
                counter.style.color = remaining < 20 ? '#dc3545' : '#6c757d';
            });

            titleInput.dispatchEvent(new Event('input'));
        }
    }

    /**
     * Warn user about unsaved changes
     */
    function initializeUnsavedChangesWarning() {
        let formChanged = false;
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('change', () => { formChanged = true; });
        });

        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        form.addEventListener('submit', () => { formChanged = false; });
    }

    /**
     * Initialize file upload functionality
     */
    function initializeFileUploads() {
        const maxFiles = {{ $board->max_file_count ?? 5 }};
        const blockedExtensions = @json(explode(',', $board->blocked_extensions ?? ''));
        let uploadedImages = [];
        let uploadedFiles = [];

        // Initialize image upload
        initializeImageUpload();
        // Initialize file upload
        initializeFileUpload();

        /**
         * Setup image upload functionality
         */
        function initializeImageUpload() {
            const imageDropZone = document.getElementById('imageDropZone');
            const imageInput = document.getElementById('imageInput');

            if (imageDropZone && imageInput) {
                setupDropZone(imageDropZone, imageInput, 'border-primary', uploadedImages, maxFiles, handleImageFiles);
                imageInput.addEventListener('change', (e) => handleImageFiles(e.target.files));
            }
        }

        /**
         * Setup file attachment upload functionality
         */
        function initializeFileUpload() {
            const fileDropZone = document.getElementById('fileDropZone');
            const fileInput = document.getElementById('fileInput');

            if (fileDropZone && fileInput) {
                setupDropZone(fileDropZone, fileInput, 'border-success', uploadedFiles, maxFiles, handleAttachmentFiles);
                fileInput.addEventListener('change', (e) => handleAttachmentFiles(e.target.files));
            }
        }

        /**
         * Generic drop zone setup
         */
        function setupDropZone(dropZone, input, borderClass, fileArray, maxFiles, handler) {
            dropZone.addEventListener('click', () => {
                if (fileArray.length < maxFiles) input.click();
            });

            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add(borderClass, 'bg-light');
            });

            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropZone.classList.remove(borderClass, 'bg-light');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove(borderClass, 'bg-light');
                handler(e.dataTransfer.files);
            });
        }

        /**
         * Handle image file validation and processing
         */
        function handleImageFiles(files) {
            Array.from(files).forEach(file => {
                if (uploadedImages.length >= maxFiles) {
                    showAlert(`최대 ${maxFiles}개의 이미지만 업로드할 수 있습니다.`, 'warning');
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    showAlert('이미지 파일만 업로드할 수 있습니다.', 'danger');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showAlert('이미지 파일 크기는 5MB 이하여야 합니다.', 'danger');
                    return;
                }

                if (isBlockedExtension(file.name)) {
                    showAlert(`업로드할 수 없는 파일 형식입니다: ${getFileExtension(file.name)}`, 'danger');
                    return;
                }

                uploadedImages.push(file);
                displayImagePreview(file, uploadedImages.length - 1);
            });

            updateImageInput();
            toggleImagePreview();
        }

        /**
         * Handle attachment file validation and processing
         */
        function handleAttachmentFiles(files) {
            Array.from(files).forEach(file => {
                if (uploadedFiles.length >= maxFiles) {
                    showAlert(`최대 ${maxFiles}개의 파일만 업로드할 수 있습니다.`, 'warning');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showAlert('파일 크기는 5MB 이하여야 합니다.', 'danger');
                    return;
                }

                if (isBlockedExtension(file.name)) {
                    showAlert(`업로드할 수 없는 파일 형식입니다: ${getFileExtension(file.name)}`, 'danger');
                    return;
                }

                uploadedFiles.push(file);
                displayFileItem(file, uploadedFiles.length - 1);
            });

            updateFileInput();
            toggleFileList();
        }

        /**
         * Display image preview in the upload grid
         */
        function displayImagePreview(file, index) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageGrid = document.getElementById('imageGrid');
                const imageItem = document.createElement('div');
                imageItem.className = 'col-6 col-md-4';
                imageItem.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded shadow-sm" style="height: 120px; object-fit: cover; width: 100%;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1"
                                onclick="removeNewImage(${index})" style="width: 24px; height: 24px; font-size: 10px;">
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

        /**
         * Display file attachment item in the upload list
         */
        function displayFileItem(file, index) {
            const fileList = document.getElementById('fileList');
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
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeNewFile(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            fileList.appendChild(fileItem);
        }

        // Global functions for removing new files
        window.removeNewImage = function(index) {
            uploadedImages.splice(index, 1);
            updateImageInput();
            refreshImagePreview();
        };

        window.removeNewFile = function(index) {
            uploadedFiles.splice(index, 1);
            updateFileInput();
            refreshFileList();
        };

        // Helper functions for file management
        function refreshImagePreview() {
            const imageGrid = document.getElementById('imageGrid');
            imageGrid.innerHTML = '';
            uploadedImages.forEach((file, index) => {
                displayImagePreview(file, index);
            });
            toggleImagePreview();
        }

        function refreshFileList() {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            uploadedFiles.forEach((file, index) => {
                displayFileItem(file, index);
            });
            toggleFileList();
        }

        function updateImageInput() {
            const imageInput = document.getElementById('imageInput');
            const dt = new DataTransfer();
            uploadedImages.forEach(file => dt.items.add(file));
            if (imageInput) imageInput.files = dt.files;
        }

        function updateFileInput() {
            const fileInput = document.getElementById('fileInput');
            const dt = new DataTransfer();
            uploadedFiles.forEach(file => dt.items.add(file));
            if (fileInput) fileInput.files = dt.files;
        }

        function toggleImagePreview() {
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                imagePreview.classList.toggle('d-none', uploadedImages.length === 0);
            }
        }

        function toggleFileList() {
            const fileList = document.getElementById('fileList');
            if (fileList) {
                fileList.classList.toggle('d-none', uploadedFiles.length === 0);
            }
        }

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

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
});
</script>
@endpush
@endsection