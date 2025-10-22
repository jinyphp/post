@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 글 수정')

@section('content')
    <div class="container-fluid">

        <x-heading title="블로그 글 수정" :subtitle="$blog->title . ' 글을 수정합니다.'">
            <x-btn-back-to-list :route="route('admin.cms.blog')">
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
                <form id="blogEditForm" action="{{ route('admin.cms.blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card border-0 shadow-sm">
                        <ul class="nav nav-tabs nav-tabs-custom" id="blogTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab"
                                        data-bs-target="#basic" type="button" role="tab">
                                        기본정보
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content"
                                        type="button" role="tab">
                                        내용
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings"
                                        type="button" role="tab">
                                        설정
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo"
                                        type="button" role="tab">
                                        SEO
                                    </button>
                                </li>
                            </ul>

                        <div class="card-body">
                            <div class="tab-content" id="blogTabContent">
                                <!-- 기본정보 탭 -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                    <div class="p-4">
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">제목 <span class="text-danger">*</span></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="title" required
                                                    value="{{ old('title', $blog->title) }}" placeholder="블로그 글 제목">
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
                                                        <select class="form-select" name="category_slug" id="categorySelect">
                                                            <option value="">카테고리 선택</option>
                                                            @foreach ($categories ?? [] as $category)
                                                                <option value="{{ $category->slug }}"
                                                                    data-color="{{ $category->color }}"
                                                                    {{ old('category_slug', $blog->category_slug) == $category->slug ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <a href="{{ route('admin.cms.blog.category') }}"
                                                            class="btn btn-outline-secondary" title="카테고리 관리">
                                                            <i class="bi bi-gear"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="form-text">
                                                    카테고리가 없으면
                                                    <a href="{{ route('admin.cms.blog.category.create') }}"
                                                        target="_blank">여기서 생성</a>하세요.
                                                </div>
                                                <!-- 선택된 카테고리 미리보기 -->
                                                <div id="categoryPreview" class="mt-2" style="display: none;">
                                                    <span class="badge" id="previewCategoryBadge">
                                                        <span id="previewCategoryName"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">슬러그</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="slug"
                                                    value="{{ old('slug', $blog->slug) }}" placeholder="URL 슬러그">
                                                <div class="form-text">비워두면 제목에서 자동 생성됩니다.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">요약</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" name="excerpt" rows="3"
                                                    placeholder="글 요약">{{ old('excerpt', $blog->excerpt) }}</textarea>
                                                <div class="form-text">비워두면 내용에서 자동 생성됩니다.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">대표 이미지</label>
                                            <div class="col-sm-10">
                                                @if($blog->featured_image)
                                                    <div class="mb-2">
                                                        <img src="{{ $blog->featured_image }}" alt="현재 대표 이미지"
                                                             class="img-thumbnail" style="max-height: 150px;">
                                                        <div class="form-text">현재 대표 이미지</div>
                                                    </div>
                                                @endif
                                                <input type="file" class="form-control" name="featured_image_file"
                                                    accept="image/*">
                                                <div class="form-text">JPG, PNG, GIF, WebP 형식 · 최대 5MB · 새 파일 선택 시 기존 이미지가 교체됩니다</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 내용 탭 -->
                                <div class="tab-pane fade" id="content" role="tabpanel">
                                    <div class="p-4">
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">내용 <span class="text-danger">*</span></label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" name="content" rows="15" required>{{ old('content', $blog->content) }}</textarea>
                                                @error('content')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- 다중 이미지 업로드 섹션 -->
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">다중 이미지</label>
                                            <div class="col-sm-10">

                                                <!-- 기존 이미지들 표시 -->
                                                @if(!empty($blogImages) && count($blogImages) > 0)
                                                    <div class="mb-4">
                                                        <h6 class="text-muted mb-3">
                                                            <i class="bi bi-images me-1"></i>등록된 이미지 (<span id="existingImageCount">{{ count($blogImages) }}</span>개)
                                                        </h6>
                                                        <div class="row g-3" id="existingImagesContainer">
                                                            @foreach($blogImages as $image)
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
                                                            최대 10개 · JPG, PNG, GIF, WebP · 각 파일 최대 5MB
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
                                                        <h6 class="mb-0">새로 추가할 이미지 (<span id="newImageCountAdmin">0</span>/10)</h6>
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
                                    </div>
                                </div>

                                <!-- 설정 탭 -->
                                <div class="tab-pane fade" id="settings" role="tabpanel">
                                    <div class="p-4">
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">상태</label>
                                            <div class="col-sm-10">
                                                <select class="form-select" name="status">
                                                    <option value="draft" {{ old('status', $blog->status) == 'draft' ? 'selected' : '' }}>초안</option>
                                                    <option value="published" {{ old('status', $blog->status) == 'published' ? 'selected' : '' }}>발행</option>
                                                    <option value="scheduled" {{ old('status', $blog->status) == 'scheduled' ? 'selected' : '' }}>예약 발행</option>
                                                    <option value="pending" {{ old('status', $blog->status) == 'pending' ? 'selected' : '' }}>승인 대기</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">발행일시</label>
                                            <div class="col-sm-10">
                                                <input type="datetime-local" class="form-control" name="published_at"
                                                    value="{{ old('published_at', $blog->published_at ? \Carbon\Carbon::parse($blog->published_at)->format('Y-m-d\TH:i') : '') }}">
                                                <div class="form-text">발행 상태일 때 표시되는 시간입니다.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">예약 발행일시</label>
                                            <div class="col-sm-10">
                                                <input type="datetime-local" class="form-control" name="scheduled_at"
                                                    value="{{ old('scheduled_at', $blog->scheduled_at ? \Carbon\Carbon::parse($blog->scheduled_at)->format('Y-m-d\TH:i') : '') }}">
                                                <div class="form-text">예약 발행 상태일 때 자동 발행될 시간입니다.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">옵션</label>
                                            <div class="col-sm-10">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="allow_comments" id="allow_comments"
                                                        {{ old('allow_comments', $blog->allow_comments) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="allow_comments">
                                                        댓글 허용
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                                                        {{ old('is_featured', $blog->is_featured) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_featured">
                                                        추천글
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_sticky" id="is_sticky"
                                                        {{ old('is_sticky', $blog->is_sticky) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_sticky">
                                                        상단 고정
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEO 탭 -->
                                <div class="tab-pane fade" id="seo" role="tabpanel">
                                    <div class="p-4">
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">SEO 제목</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="meta_title"
                                                    value="{{ old('meta_title', $blog->meta_title) }}" placeholder="SEO용 제목">
                                                <div class="form-text">비워두면 제목을 사용합니다.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">SEO 설명</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" name="meta_description" rows="3"
                                                    placeholder="SEO용 설명">{{ old('meta_description', $blog->meta_description) }}</textarea>
                                                <div class="form-text">비워두면 요약을 사용합니다.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-2 col-form-label">키워드</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="meta_keywords"
                                                    value="{{ old('meta_keywords', $blog->meta_keywords) }}" placeholder="키워드 (쉼표로 구분)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.cms.blog') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark me-1"></i> 초안 저장
                            </button>
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
                <x-help title="블로그 글 수정 가이드" icon="bi-book" icon-color="text-info">
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
                    <p class="small text-muted mb-3">최대 5MB까지 업로드 가능합니다.</p>

                    <x-help-title icon="bi-eye">미리보기</x-help-title>
                    <p class="small text-muted mb-0">업로드 전에 이미지를 미리 확인할 수 있습니다.</p>
                </x-help>

                <x-help title="SEO 최적화" icon="bi-search" icon-color="text-primary">
                    <x-help-title icon="bi-globe">메타 정보</x-help-title>
                    <p class="small text-muted mb-3">SEO 제목과 설명을 설정하여 검색 엔진 최적화를 개선하세요.</p>

                    <x-help-title icon="bi-tags">키워드</x-help-title>
                    <p class="small text-muted mb-0">관련 키워드를 추가하여 검색 결과에 더 잘 노출되도록 하세요.</p>
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
        const maxFilesAdmin = 10;
        const maxFileSizeAdmin = 5 * 1024 * 1024;

        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('categorySelect');
            const categoryPreview = document.getElementById('categoryPreview');
            const previewCategoryName = document.getElementById('previewCategoryName');
            const previewCategoryBadge = document.getElementById('previewCategoryBadge');

            const blogEditForm = document.getElementById('blogEditForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingBtn = document.getElementById('loadingBtn');

            // 카테고리 선택 시 미리보기 업데이트
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const categoryName = selectedOption.text;
                    const categoryColor = selectedOption.dataset.color;

                    if (selectedOption.value) {
                        // 미리보기 표시
                        if (previewCategoryName) previewCategoryName.textContent = categoryName;
                        if (previewCategoryBadge) {
                            previewCategoryBadge.style.backgroundColor = categoryColor || '#6c757d';
                            previewCategoryBadge.style.color = 'white';
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

            // 다중 이미지 업로드 초기화
            initAdminImageUpload();

            // 폼 제출 처리 (AJAX 비활성화 - 일반 폼 제출 사용)
            if (blogEditForm) {
                blogEditForm.addEventListener('submit', function(e) {
                    // AJAX 대신 일반 폼 제출 사용
                    // e.preventDefault();

                    // 폼 제출 전 디버깅 로그
                    const imageInput = document.getElementById('imagesAdmin');
                    console.log('=== Admin 블로그 수정 폼 제출 디버깅 ===');
                    console.log('selectedFilesAdmin:', selectedFilesAdmin);
                    console.log('imageInput.files:', imageInput.files);
                    console.log('imageInput.files.length:', imageInput.files.length);

                    // 선택된 파일들을 input에 설정
                    if (selectedFilesAdmin.length > 0) {
                        console.log('Admin - Setting files to input:', selectedFilesAdmin.length);
                        updateFileInputAdmin();
                    }

                    // 버튼 상태 변경
                    if (submitBtn && loadingBtn) {
                        submitBtn.classList.add('d-none');
                        loadingBtn.classList.remove('d-none');
                    }

                    // 일반 폼 제출 진행
                    return true;
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
                console.log('Admin Edit - File select event:', e.target.files);
                const files = Array.from(e.target.files);
                console.log('Admin Edit - Selected files:', files.map(f => ({name: f.name, size: f.size})));
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
                    alert(`"${file.name}"이 5MB를 초과합니다.`);
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
                console.log('Admin Edit - Updated file input:', {
                    selectedFilesCount: selectedFilesAdmin.length,
                    inputFilesCount: imageInput.files.length,
                    fileNames: Array.from(imageInput.files).map(f => f.name || 'Unknown'),
                    files: Array.from(imageInput.files)
                });
            } catch (error) {
                console.error('Admin Edit - Error updating file input:', error);
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