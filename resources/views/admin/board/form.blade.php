@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', isset($item) ? '게시판 수정' : '게시판 등록')

@section('content')
<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-pencil-square me-2 text-primary"></i>
                {{ isset($item) ? '게시판 수정' : '게시판 등록' }}
            </h3>
            <p class="text-muted mb-0">{{ $config['subtitle'] ?? '복수의 게시판을 관리합니다.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cms.board.list') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i> 목록
            </a>
            <button type="submit" form="boardForm" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> {{ isset($item) ? '수정' : '등록' }}
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="border-bottom">
            <ul class="nav nav-tabs nav-tabs-custom border-0 mb-0" id="boardTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab"
                            data-bs-target="#basic" type="button" role="tab">
                        <i class="bi bi-info-circle me-2"></i>기본정보
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="list-tab" data-bs-toggle="tab"
                            data-bs-target="#list" type="button" role="tab">
                        <i class="bi bi-list-ul me-2"></i>게시물목록
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="display-tab" data-bs-toggle="tab"
                            data-bs-target="#display" type="button" role="tab">
                        <i class="bi bi-palette me-2"></i>화면
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="manage-tab" data-bs-toggle="tab"
                            data-bs-target="#manage" type="button" role="tab">
                        <i class="bi bi-gear me-2"></i>관리
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="memo-tab" data-bs-toggle="tab"
                            data-bs-target="#memo" type="button" role="tab">
                        <i class="bi bi-journal-text me-2"></i>메모
                    </button>
                </li>
            </ul>
        </div>

        <form id="boardForm" action="{{ isset($item) ? route('admin.cms.board.list.update', $item->id) : route('admin.cms.board.list.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

        <div class="card-body">
            <div class="tab-content" id="boardTabContent">
                <!-- 기본정보 탭 -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">활성화</label>
                            <div class="col-sm-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="enable"
                                           value="1" {{ (isset($item) && $item->enable) ? 'checked' : '' }}>
                                    <label class="form-check-label">게시판 활성화</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Slug</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="slug"
                                       value="{{ $item->slug ?? '' }}" placeholder="URL 주소">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">제목 <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="title" required
                                       value="{{ $item->title ?? '' }}" placeholder="게시판 제목">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">이미지</label>
                            <div class="col-sm-10">
                                <input type="file" class="form-control" name="image" accept="image/*">
                                @if(isset($item->image))
                                    <div class="mt-2">
                                        <small class="text-muted">현재 이미지: {{ $item->image }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">부제목</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" name="subtitle" rows="3">{{ $item->subtitle ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">카테고리</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="category"
                                       value="{{ $item->category ?? '' }}" placeholder="게시판 카테고리">
                                <small class="text-muted">게시판의 카테고리를 설정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">정렬 순서</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="sort_order"
                                       value="{{ $item->sort_order ?? 0 }}" min="0">
                                <small class="text-muted">게시판 목록에서의 정렬 순서입니다. 숫자가 낮을수록 먼저 표시됩니다.</small>
                            </div>
                        </div>
                </div>

                <!-- 게시물목록 탭 -->
                <div class="tab-pane fade" id="list" role="tabpanel">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">페이지당 게시물 수</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="per_page">
                                    <option value="5" {{ (isset($item) && $item->per_page == 5) ? 'selected' : '' }}>5개씩 보기</option>
                                    <option value="10" {{ (!isset($item) || $item->per_page == 10) ? 'selected' : '' }}>10개씩 보기 (기본값)</option>
                                    <option value="20" {{ (isset($item) && $item->per_page == 20) ? 'selected' : '' }}>20개씩 보기</option>
                                    <option value="50" {{ (isset($item) && $item->per_page == 50) ? 'selected' : '' }}>50개씩 보기</option>
                                    <option value="100" {{ (isset($item) && $item->per_page == 100) ? 'selected' : '' }}>100개씩 보기</option>
                                </select>
                                <small class="text-muted">게시판 목록에서 페이지당 표시할 기본 게시물 수를 설정합니다.</small>
                            </div>
                        </div>

                        <h5 class="mb-3">권한 설정</h5>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글보기 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="permit_read">
                                    <option value="public" {{ (!isset($item) || $item->permit_read == 'public') ? 'selected' : '' }}>모든 사용자</option>
                                    <option value="member" {{ (isset($item) && $item->permit_read == 'member') ? 'selected' : '' }}>로그인 사용자만</option>
                                    <option value="grade" {{ (isset($item) && $item->permit_read == 'grade') ? 'selected' : '' }}>특정 회원 등급</option>
                                </select>
                                <small class="text-muted">게시글을 볼 수 있는 권한을 설정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글쓰기 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="permit_create">
                                    <option value="public" {{ (isset($item) && $item->permit_create == 'public') ? 'selected' : '' }}>모든 사용자</option>
                                    <option value="member" {{ (!isset($item) || $item->permit_create == 'member') ? 'selected' : '' }}>로그인 사용자만</option>
                                    <option value="grade" {{ (isset($item) && $item->permit_create == 'grade') ? 'selected' : '' }}>특정 회원 등급</option>
                                    <option value="none" {{ (isset($item) && $item->permit_create == 'none') ? 'selected' : '' }}>허용안함</option>
                                </select>
                                <small class="text-muted">새 글을 작성할 수 있는 권한을 설정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글수정 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="permit_edit">
                                    <option value="owner" {{ (!isset($item) || $item->permit_edit == 'owner') ? 'selected' : '' }}>작성자만</option>
                                    <option value="member" {{ (isset($item) && $item->permit_edit == 'member') ? 'selected' : '' }}>로그인 사용자</option>
                                    <option value="grade" {{ (isset($item) && $item->permit_edit == 'grade') ? 'selected' : '' }}>특정 회원 등급</option>
                                    <option value="admin" {{ (isset($item) && $item->permit_edit == 'admin') ? 'selected' : '' }}>관리자만</option>
                                </select>
                                <small class="text-muted">게시글을 수정할 수 있는 권한을 설정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글삭제 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="permit_delete">
                                    <option value="owner" {{ (!isset($item) || $item->permit_delete == 'owner') ? 'selected' : '' }}>작성자만</option>
                                    <option value="member" {{ (isset($item) && $item->permit_delete == 'member') ? 'selected' : '' }}>로그인 사용자</option>
                                    <option value="grade" {{ (isset($item) && $item->permit_delete == 'grade') ? 'selected' : '' }}>특정 회원 등급</option>
                                    <option value="admin" {{ (isset($item) && $item->permit_delete == 'admin') ? 'selected' : '' }}>관리자만</option>
                                </select>
                                <small class="text-muted">게시글을 삭제할 수 있는 권한을 설정합니다. Admin/Super 회원은 항상 삭제 가능합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">테이블 Blade</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_table"
                                       value="{{ $item->view_table ?? '' }}" placeholder="예: jiny-site::board.table">
                                <small class="text-muted">계시물의 테이블 목록을 수정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">리스트 Blade</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_list"
                                       value="{{ $item->view_list ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">필터 Blade</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_filter"
                                       value="{{ $item->view_filter ?? '' }}">
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">기능 설정</h5>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">댓글 사용</label>
                            <div class="col-sm-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="use_comment"
                                           value="1" {{ (!isset($item) || $item->use_comment) ? 'checked' : '' }}>
                                    <label class="form-check-label">댓글 기능 사용</label>
                                </div>
                                <small class="text-muted">게시글에 댓글을 작성할 수 있도록 합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">평가 사용</label>
                            <div class="col-sm-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="use_rating"
                                           value="1" {{ (isset($item) && $item->use_rating) ? 'checked' : '' }}>
                                    <label class="form-check-label">평가(별점) 기능 사용</label>
                                </div>
                                <small class="text-muted">게시글에 별점 평가를 할 수 있도록 합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">좋아요 사용</label>
                            <div class="col-sm-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="use_like"
                                           value="1" {{ (!isset($item) || $item->use_like) ? 'checked' : '' }}>
                                    <label class="form-check-label">좋아요 기능 사용</label>
                                </div>
                                <small class="text-muted">게시글에 좋아요를 누를 수 있도록 합니다.</small>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">파일 업로드 설정</h5>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">최대 파일 크기</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="max_file_size"
                                           value="{{ $item->max_file_size ?? 5120 }}" min="0">
                                    <span class="input-group-text">KB</span>
                                </div>
                                <small class="text-muted">첨부파일의 최대 크기를 KB 단위로 설정합니다. (1MB = 1024KB)</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">허용 확장자</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="allowed_extensions"
                                       value="{{ $item->allowed_extensions ?? 'jpg,jpeg,png,gif,pdf,doc,docx' }}"
                                       placeholder="jpg,png,pdf,doc">
                                <small class="text-muted">업로드를 허용할 파일 확장자를 쉼표로 구분하여 입력합니다.</small>
                            </div>
                        </div>
                </div>

                <!-- 화면 탭 -->
                <div class="tab-pane fade" id="display" role="tabpanel">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">레이아웃</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_layout"
                                       value="{{ $item->view_layout ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글작성</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_create"
                                       value="{{ $item->view_create ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글보기</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_detail"
                                       value="{{ $item->view_detail ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">수정</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_edit"
                                       value="{{ $item->view_edit ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">폼양식</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="view_form"
                                       value="{{ $item->view_form ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Header</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" name="header" rows="3">{{ $item->header ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Footer</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" name="footer" rows="3">{{ $item->footer ?? '' }}</textarea>
                            </div>
                        </div>
                </div>

                <!-- 관리 탭 -->
                <div class="tab-pane fade" id="manage" role="tabpanel">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">담당자</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="manager"
                                       value="{{ $item->manager ?? '' }}" placeholder="담당자 이름">
                            </div>
                        </div>
                </div>

                <!-- 메모 탭 -->
                <div class="tab-pane fade" id="memo" role="tabpanel">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">설명</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" name="description" rows="5">{{ $item->description ?? '' }}</textarea>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* 페이지 헤더 스타일 */
.container-fluid h3 {
    color: #2d3748;
    font-weight: 700;
}

.container-fluid h3 i {
    font-size: 1.2rem;
}

/* 커스텀 탭 스타일 */
.nav-tabs-custom {
    border-bottom: 1px solid #dee2e6;
    background-color: transparent;
    margin: 0;
    padding: 0;
}

.nav-tabs-custom .nav-link {
    border: 1px solid transparent;
    background: transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 12px 16px;
    margin-bottom: -1px;
    margin-right: 2px;
    border-radius: 0;
    transition: all 0.3s ease;
    position: relative;
}

.nav-tabs-custom .nav-link:hover {
    background-color: rgba(0, 123, 255, 0.05);
    color: #007bff;
    border-color: #dee2e6 #dee2e6 transparent #dee2e6;
}

.nav-tabs-custom .nav-link.active {
    background-color: #ffffff;
    color: #007bff;
    border-color: #dee2e6 #dee2e6 #ffffff #dee2e6;
    font-weight: 600;
    z-index: 10;
}

.nav-tabs-custom .nav-link.active:focus,
.nav-tabs-custom .nav-link.active:hover {
    background-color: #ffffff;
    border-color: #dee2e6 #dee2e6 #ffffff #dee2e6;
    color: #007bff;
}

/* 카드 헤더 개선 */
.card-header {
    background-color: transparent;
    border-bottom: 1px solid #dee2e6;
    padding: 0;
    border-radius: 0.375rem 0.375rem 0 0;
}

/* 탭 콘텐츠 영역 */
.tab-content {
    background: #ffffff;
    padding: 0;
}

.tab-pane {
    padding: 2rem;
    background: #ffffff;
    border-top: none;
}

/* 카드 전체 스타일 */
.card {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}

.card-body {
    padding: 0;
    background: #ffffff;
    border-radius: 0 0 0.375rem 0.375rem;
}

/* 폼 레이블 개선 */
.col-form-label {
    font-weight: 600;
    color: #495057;
}

/* 체크박스 및 라디오 개선 */
.form-check-label {
    font-weight: 500;
    color: #6c757d;
}

/* 섹션 제목 */
h5 {
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

/* 입력 필드 포커스 효과 */
.form-control:focus,
.form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* 작은 도움말 텍스트 */
.text-muted small {
    font-size: 0.875rem;
}

/* 버튼 개선 */
.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-color: #007bff;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}
</style>
@endpush
@endsection
