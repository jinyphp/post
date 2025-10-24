@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '게시판 등록')

@section('content')
<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-plus-circle me-2 text-primary"></i>
                게시판 등록
            </h3>
            <p class="text-muted mb-0">{{ $config['subtitle'] ?? '새로운 게시판을 등록합니다.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cms.board.list.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i> 목록
            </a>
            <button type="submit" form="boardForm" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> 등록
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
                    <button class="nav-link" id="permission-tab" data-bs-toggle="tab"
                            data-bs-target="#permission" type="button" role="tab">
                        <i class="bi bi-shield-check me-2"></i>권한관리
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
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="files-tab" data-bs-toggle="tab"
                            data-bs-target="#files" type="button" role="tab">
                        <i class="bi bi-file-earmark me-2"></i>파일관리
                    </button>
                </li>
            </ul>
        </div>

        <form id="boardForm" action="{{ route('admin.cms.board.list.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf

        <div class="card-body">
            <div class="tab-content" id="boardTabContent">
                <!-- 기본정보 탭 -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">활성화</label>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="enable"
                                       value="1" checked>
                                <label class="form-check-label">게시판 활성화</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">코드 <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="code" required
                                   placeholder="게시판 고유 코드 (영문소문자, 숫자, 언더스코어만 사용)">
                            <small class="text-muted">게시판을 식별하는 고유한 코드입니다. 생성 후 변경할 수 없습니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">Slug</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="slug"
                                   placeholder="URL 주소 (예: /board/notice)">
                            <small class="text-muted">URL에 표시될 주소입니다. 비워두면 코드로 자동 생성됩니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">제목 <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="title" required
                                   placeholder="게시판 제목">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">이미지</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">게시판 대표 이미지를 업로드할 수 있습니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">부제목</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="subtitle" rows="3"
                                      placeholder="게시판에 대한 간단한 설명"></textarea>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">카테고리</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="category"
                                   placeholder="게시판 카테고리">
                            <small class="text-muted">게시판의 카테고리를 설정합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">정렬 순서</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="sort_order"
                                   value="0" min="0">
                            <small class="text-muted">게시판 목록에서의 정렬 순서입니다. 숫자가 낮을수록 먼저 표시됩니다.</small>
                        </div>
                    </div>
                </div>

                <!-- 권한관리 탭 -->
                <div class="tab-pane fade" id="permission" role="tabpanel">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>권한 관리 시스템</strong><br>
                        게시판별로 글쓰기, 읽기, 댓글 권한을 세밀하게 설정할 수 있습니다.
                    </div>

                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="bi bi-shield-check text-primary me-2"></i>접근 권한 설정
                        </h5>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">글쓰기 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="write_permission">
                                    @foreach($permissionOptions ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ $key == 'guest_allowed' ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                    @if(empty($permissionOptions))
                                        <option value="admin_only">관리자만 글쓰기 허용</option>
                                        <option value="member_only">회원만 글쓰기 허용</option>
                                        <option value="guest_allowed" selected>비회원 글쓰기 허용</option>
                                    @endif
                                </select>
                                <small class="text-muted">새 게시글을 작성할 수 있는 권한을 설정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">읽기 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="read_permission">
                                    @foreach($permissionOptions ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ $key == 'guest_allowed' ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                    @if(empty($permissionOptions))
                                        <option value="admin_only">관리자만 읽기 허용</option>
                                        <option value="member_only">회원만 읽기 허용</option>
                                        <option value="guest_allowed" selected>비회원 읽기 허용</option>
                                    @endif
                                </select>
                                <small class="text-muted">게시글을 읽을 수 있는 권한을 설정합니다.</small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">댓글 권한</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="comment_permission">
                                    @foreach($permissionOptions ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ $key == 'guest_allowed' ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                    @if(empty($permissionOptions))
                                        <option value="admin_only">관리자만 댓글 허용</option>
                                        <option value="member_only">회원만 댓글 허용</option>
                                        <option value="guest_allowed" selected>비회원 댓글 허용</option>
                                    @endif
                                </select>
                                <small class="text-muted">댓글을 작성할 수 있는 권한을 설정합니다.</small>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <h6 class="mb-2">
                            <i class="bi bi-lightbulb text-warning me-2"></i>권한 설정 가이드
                        </h6>
                        <ul class="mb-0 small text-muted">
                            <li><strong>관리자만 허용:</strong> 시스템 관리자만 접근 가능한 중요한 게시판</li>
                            <li><strong>회원만 허용:</strong> 로그인한 회원만 접근 가능한 일반 게시판</li>
                            <li><strong>비회원 허용:</strong> 누구나 접근 가능한 공개 게시판</li>
                        </ul>
                    </div>
                </div>

                <!-- 게시물목록 탭 -->
                <div class="tab-pane fade" id="list" role="tabpanel">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">페이지당 게시물 수</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="per_page">
                                <option value="5">5개씩 보기</option>
                                <option value="10" selected>10개씩 보기 (기본값)</option>
                                <option value="20">20개씩 보기</option>
                                <option value="50">50개씩 보기</option>
                                <option value="100">100개씩 보기</option>
                            </select>
                            <small class="text-muted">게시판 목록에서 페이지당 표시할 기본 게시물 수를 설정합니다.</small>
                        </div>
                    </div>

                    <h5 class="mb-3">권한 설정</h5>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">글보기 권한</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="permit_read">
                                <option value="public" selected>모든 사용자</option>
                                <option value="member">로그인 사용자만</option>
                                <option value="grade">특정 회원 등급</option>
                            </select>
                            <small class="text-muted">게시글을 볼 수 있는 권한을 설정합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">글쓰기 권한</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="permit_create">
                                <option value="public">모든 사용자</option>
                                <option value="member" selected>로그인 사용자만</option>
                                <option value="grade">특정 회원 등급</option>
                                <option value="none">허용안함</option>
                            </select>
                            <small class="text-muted">새 글을 작성할 수 있는 권한을 설정합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">글수정 권한</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="permit_edit">
                                <option value="owner" selected>작성자만</option>
                                <option value="member">로그인 사용자</option>
                                <option value="grade">특정 회원 등급</option>
                                <option value="admin">관리자만</option>
                            </select>
                            <small class="text-muted">게시글을 수정할 수 있는 권한을 설정합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">글삭제 권한</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="permit_delete">
                                <option value="owner" selected>작성자만</option>
                                <option value="member">로그인 사용자</option>
                                <option value="grade">특정 회원 등급</option>
                                <option value="admin">관리자만</option>
                            </select>
                            <small class="text-muted">게시글을 삭제할 수 있는 권한을 설정합니다. Admin/Super 회원은 항상 삭제 가능합니다.</small>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4">기능 설정</h5>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">댓글 사용</label>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="use_comment"
                                       value="1" checked>
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
                                       value="1">
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
                                       value="1" checked>
                                <label class="form-check-label">좋아요 기능 사용</label>
                            </div>
                            <small class="text-muted">게시글에 좋아요를 누를 수 있도록 합니다.</small>
                        </div>
                    </div>
                </div>

                <!-- 파일관리 탭 -->
                <div class="tab-pane fade" id="files" role="tabpanel">
                    <div class="alert alert-warning">
                        <i class="bi bi-shield-exclamation me-2"></i>
                        <strong>보안 경고</strong><br>
                        파일 업로드 기능은 보안상 위험할 수 있습니다. 허용하는 파일 형식을 신중하게 설정하세요.
                    </div>

                    <h5 class="mb-3">파일 업로드 설정</h5>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">파일 업로드 허용</label>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="allow_file_upload"
                                       value="1" checked>
                                <label class="form-check-label">첨부파일 업로드 허용</label>
                            </div>
                            <small class="text-muted">게시글에 파일을 첨부할 수 있도록 합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">이미지 업로드 허용</label>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="allow_image_upload"
                                       value="1" checked>
                                <label class="form-check-label">다중 이미지 업로드 허용</label>
                            </div>
                            <small class="text-muted">게시글에 여러 이미지를 업로드할 수 있도록 합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">최대 파일 크기</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="number" class="form-control" name="max_file_size"
                                       value="5120" min="0">
                                <span class="input-group-text">KB</span>
                            </div>
                            <small class="text-muted">첨부파일의 최대 크기를 KB 단위로 설정합니다. (1MB = 1024KB)</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">최대 파일 개수</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="max_file_count"
                                   value="5" min="1" max="20">
                            <small class="text-muted">게시글당 업로드 가능한 최대 파일 개수를 설정합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">허용 확장자</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="allowed_extensions"
                                   value="jpg,jpeg,png,gif,pdf,doc,docx,hwp,txt"
                                   placeholder="jpg,png,pdf,doc">
                            <small class="text-muted">업로드를 허용할 파일 확장자를 쉼표로 구분하여 입력합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">금지 확장자</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="blocked_extensions"
                                   value="exe,bat,cmd,com,pif,scr,vbs,js,jar,php,asp,jsp"
                                   placeholder="exe,bat,php,asp">
                            <small class="text-muted">보안상 위험한 파일 확장자를 쉼표로 구분하여 입력합니다.</small>
                        </div>
                    </div>

                    <div class="border border-danger p-3 rounded">
                        <h6 class="text-danger mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>보안 가이드
                        </h6>
                        <ul class="mb-0 small text-danger">
                            <li><strong>실행 파일 금지:</strong> exe, bat, cmd, com, pif, scr 등</li>
                            <li><strong>스크립트 파일 금지:</strong> php, asp, jsp, js, vbs 등</li>
                            <li><strong>압축 파일 주의:</strong> zip, rar 파일 내부에 위험한 파일이 있을 수 있음</li>
                            <li><strong>파일 크기 제한:</strong> 서버 부하 방지를 위해 적절한 크기로 제한</li>
                        </ul>
                    </div>
                </div>

                <!-- 화면 탭 -->
                <div class="tab-pane fade" id="display" role="tabpanel">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">레이아웃</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_layout"
                                   placeholder="jiny-site::layouts.www">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">글작성</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_create"
                                   placeholder="jiny-site::board.create">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">글보기</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_detail"
                                   placeholder="jiny-site::board.detail">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">수정</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_edit"
                                   placeholder="jiny-site::board.edit">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">테이블 Blade</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_table"
                                   placeholder="jiny-site::board.table">
                            <small class="text-muted">게시물의 테이블 목록을 수정합니다.</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">리스트 Blade</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_list"
                                   placeholder="jiny-site::board.list">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">필터 Blade</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="view_filter"
                                   placeholder="jiny-site::board.filter">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">Header</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="header" rows="3"
                                      placeholder="게시판 상단에 표시될 HTML 코드"></textarea>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">Footer</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="footer" rows="3"
                                      placeholder="게시판 하단에 표시될 HTML 코드"></textarea>
                        </div>
                    </div>
                </div>

                <!-- 관리 탭 -->
                <div class="tab-pane fade" id="manage" role="tabpanel">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">담당자</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="manager"
                                   placeholder="담당자 이름">
                            <small class="text-muted">게시판을 관리하는 담당자 정보입니다.</small>
                        </div>
                    </div>
                </div>

                <!-- 메모 탭 -->
                <div class="tab-pane fade" id="memo" role="tabpanel">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">설명</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="description" rows="5"
                                      placeholder="게시판에 대한 자세한 설명"></textarea>
                            <small class="text-muted">관리자를 위한 게시판 설명입니다. 사용자에게는 표시되지 않습니다.</small>
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

/* 알림 박스 개선 */
.alert {
    border-radius: 0.5rem;
    border: none;
    margin-bottom: 1.5rem;
}

/* 필수 필드 표시 */
.text-danger {
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 코드 필드 자동 slug 생성
    const codeInput = document.querySelector('input[name="code"]');
    const slugInput = document.querySelector('input[name="slug"]');

    if (codeInput && slugInput) {
        codeInput.addEventListener('input', function() {
            if (!slugInput.value) {
                slugInput.value = '/board/' + this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
            }
        });
    }

    // 폼 제출 전 검증
    const form = document.getElementById('boardForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const code = document.querySelector('input[name="code"]').value;
            const title = document.querySelector('input[name="title"]').value;

            if (!code || !title) {
                e.preventDefault();
                alert('코드와 제목은 필수 입력 항목입니다.');
                return false;
            }

            // 코드 유효성 검사
            if (!/^[a-z0-9_]+$/.test(code)) {
                e.preventDefault();
                alert('코드는 영문 소문자, 숫자, 언더스코어만 사용할 수 있습니다.');
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection