@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '포럼 설정')

@section('content')
<div class="container-fluid">
    <!-- 알림 메시지 -->
    <x-alert-success>
        {{ session('success') }}
    </x-alert-success>

    <x-alert-danger>
        {{ session('error') }}
    </x-alert-danger>

    <!-- 헤딩 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3><i class="bi bi-chat-dots me-2 text-primary"></i>포럼 설정</h3>
            <p class="text-muted mb-0">포럼 정책과 기본 설정을 관리합니다.</p>
        </div>
        <div>
            <a href="{{ route('admin.cms.forum.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> 포럼 관리
            </a>
        </div>
    </div>

    <hr>

    <!-- 현재 상태 -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-file-text fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_posts'] }}</div>
                    <div class="text-muted small">전체 글</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['published_posts'] }}</div>
                    <div class="text-muted small">발행됨</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-clock fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['pending_posts'] }}</div>
                    <div class="text-muted small">승인 대기</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-pencil fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['draft_posts'] }}</div>
                    <div class="text-muted small">초안</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="bi bi-folder fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_categories'] }}</div>
                    <div class="text-muted small">전체 카테고리</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-folder-check fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['active_categories'] }}</div>
                    <div class="text-muted small">활성 카테고리</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 설정 폼 -->
    <x-content>
        <x-content-main>
            <x-form-put action="{{ route('admin.cms.forum.index.config.update') }}">
                <!-- 작성 권한 정책 -->
                <x-card>
                    <x-card-header>
                        <h5 class="mb-0">
                            <i class="bi bi-shield-check me-2 text-primary"></i>
                            작성 권한 정책
                        </h5>
                    </x-card-header>
                    <x-card-body>
                        <div class="row">
                            <div class="col-md-6">
                                <x-switch
                                    name="admin_write"
                                    :checked="$config['policies']['admin_write']['enabled']"
                                >
                                    <strong>관리자 작성 허용</strong>
                                    <div class="text-muted small">시스템 관리자가 포럼 글을 작성할 수 있습니다.</div>
                                </x-switch>

                                <x-switch
                                    name="user_write"
                                    :checked="$config['policies']['user_write']['enabled']"
                                >
                                    <strong>일반 사용자 작성 허용</strong>
                                    <div class="text-muted small">로그인한 일반 사용자가 포럼 글을 작성할 수 있습니다.</div>
                                </x-switch>

                                <x-switch
                                    name="guest_write"
                                    :checked="$config['policies']['guest_write']['enabled']"
                                >
                                    <strong>비회원 작성 허용</strong>
                                    <div class="text-muted small">로그인하지 않은 사용자도 포럼 글을 작성할 수 있습니다.</div>
                                </x-switch>
                            </div>

                            <div class="col-md-6">
                                <x-switch
                                    name="user_approval"
                                    :checked="$config['policies']['user_approval']['enabled']"
                                >
                                    <strong>사용자 글 승인 필요</strong>
                                    <div class="text-muted small">일반 사용자의 글은 관리자 승인 후 발행됩니다.</div>
                                </x-switch>

                                <x-switch
                                    name="auto_approve_admin"
                                    :checked="$config['policies']['auto_approve_admin']['enabled']"
                                >
                                    <strong>관리자 글 자동 승인</strong>
                                    <div class="text-muted small">관리자의 글은 승인 절차 없이 바로 발행됩니다.</div>
                                </x-switch>

                                <x-switch
                                    name="allow_anonymous"
                                    :checked="$config['policies']['allow_anonymous']['enabled']"
                                >
                                    <strong>익명 게시 허용</strong>
                                    <div class="text-muted small">작성자 정보를 숨기고 익명으로 게시할 수 있습니다.</div>
                                </x-switch>
                            </div>
                        </div>
                    </x-card-body>
                </x-card>

                <!-- 일반 설정 -->
                <x-card>
                    <x-card-header>
                        <h5 class="mb-0">
                            <i class="bi bi-sliders me-2 text-primary"></i>
                            일반 설정
                        </h5>
                    </x-card-header>
                    <x-card-body>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_status" class="form-label">
                                        <strong>기본 게시글 상태</strong>
                                    </label>
                                    <select class="form-control" id="default_status" name="default_status">
                                        <option value="published" {{ $config['settings']['default_status'] == 'published' ? 'selected' : '' }}>발행됨</option>
                                        <option value="draft" {{ $config['settings']['default_status'] == 'draft' ? 'selected' : '' }}>초안</option>
                                        <option value="pending" {{ $config['settings']['default_status'] == 'pending' ? 'selected' : '' }}>승인 대기</option>
                                    </select>
                                    <div class="text-muted small">새로 작성되는 글의 기본 상태입니다.</div>
                                </div>

                                <x-input-number
                                    name="max_images_per_post"
                                    label="글당 최대 이미지 수"
                                    :value="$config['settings']['max_images_per_post']"
                                    :min="1"
                                    :max="50"
                                >
                                    <div class="text-muted small">하나의 글에 첨부할 수 있는 최대 이미지 수입니다.</div>
                                </x-input-number>

                                <x-input-number
                                    name="auto_excerpt_length"
                                    label="자동 요약 길이"
                                    :value="$config['settings']['auto_excerpt_length']"
                                    :min="50"
                                    :max="500"
                                >
                                    <div class="text-muted small">요약이 없을 때 자동으로 생성되는 요약의 글자 수입니다.</div>
                                </x-input-number>

                                <x-input-number
                                    name="pagination_limit"
                                    label="페이지당 게시글 수"
                                    :value="$config['settings']['pagination_limit']"
                                    :min="5"
                                    :max="50"
                                >
                                    <div class="text-muted small">한 페이지에 표시할 게시글 개수입니다.</div>
                                </x-input-number>
                            </div>

                            <div class="col-md-6">
                                <x-switch
                                    name="enable_comments"
                                    :checked="$config['settings']['enable_comments']"
                                >
                                    <strong>댓글 기능 활성화</strong>
                                    <div class="text-muted small">포럼 글에 댓글 기능을 활성화합니다.</div>
                                </x-switch>

                                <x-switch
                                    name="comment_approval"
                                    :checked="$config['settings']['comment_approval']"
                                >
                                    <strong>댓글 승인 필요</strong>
                                    <div class="text-muted small">댓글이 표시되기 전에 관리자 승인이 필요합니다.</div>
                                </x-switch>

                                <x-switch
                                    name="enable_voting"
                                    :checked="$config['settings']['enable_voting']"
                                >
                                    <strong>투표 기능 활성화</strong>
                                    <div class="text-muted small">글에 대한 추천/비추천 투표 기능을 활성화합니다.</div>
                                </x-switch>

                                <x-switch
                                    name="category_restriction"
                                    :checked="$config['policies']['category_restriction']['enabled']"
                                >
                                    <strong>카테고리 제한</strong>
                                    <div class="text-muted small">사용자가 사용할 수 있는 카테고리를 제한합니다.</div>
                                </x-switch>
                            </div>
                        </div>
                    </x-card-body>
                </x-card>

                <!-- 고급 기능 설정 -->
                <x-card>
                    <x-card-header>
                        <h5 class="mb-0">
                            <i class="bi bi-gear-wide-connected me-2 text-primary"></i>
                            고급 기능 설정
                        </h5>
                    </x-card-header>
                    <x-card-body>
                        <div class="row">
                            <div class="col-md-6">
                                <x-switch
                                    name="enable_tags"
                                    :checked="$config['settings']['enable_tags']"
                                >
                                    <strong>태그 기능 활성화</strong>
                                    <div class="text-muted small">글에 태그를 추가하여 분류할 수 있습니다.</div>
                                </x-switch>

                                <x-input-number
                                    name="max_tags_per_post"
                                    label="글당 최대 태그 수"
                                    :value="$config['settings']['max_tags_per_post']"
                                    :min="1"
                                    :max="10"
                                >
                                    <div class="text-muted small">하나의 글에 추가할 수 있는 최대 태그 수입니다.</div>
                                </x-input-number>

                                <x-switch
                                    name="enable_search"
                                    :checked="$config['settings']['enable_search']"
                                >
                                    <strong>검색 기능 활성화</strong>
                                    <div class="text-muted small">포럼 내 글 검색 기능을 활성화합니다.</div>
                                </x-switch>
                            </div>

                            <div class="col-md-6">
                                <x-switch
                                    name="enable_file_upload"
                                    :checked="$config['settings']['enable_file_upload']"
                                >
                                    <strong>파일 업로드 허용</strong>
                                    <div class="text-muted small">글에 파일을 첨부할 수 있습니다.</div>
                                </x-switch>

                                <x-input-number
                                    name="max_file_size_mb"
                                    label="최대 파일 크기 (MB)"
                                    :value="$config['settings']['max_file_size_mb']"
                                    :min="1"
                                    :max="20"
                                >
                                    <div class="text-muted small">업로드할 수 있는 파일의 최대 크기입니다.</div>
                                </x-input-number>
                            </div>
                        </div>
                    </x-card-body>
                </x-card>

                <!-- 저장 버튼 -->
                <x-btn-save>설정 저장</x-btn-save>
            </x-form-put>
        </x-content-main>

        <x-content-side>
            <!-- 설정 정보 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2 text-info"></i>
                        설정 정보
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">마지막 업데이트</small>
                        <div class="fw-medium">{{ $config['updated_at'] ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">업데이트한 사용자</small>
                        <div class="fw-medium">{{ $config['updated_by'] ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">설정 파일 위치</small>
                        <div class="small font-monospace text-break">vendor/jiny/post/config/forum.json</div>
                    </div>
                </div>
            </div>

            <!-- 권장 설정 -->
            <x-help title="권장 설정" icon="bi-lightbulb" iconColor="text-warning">
                <x-help-title icon="bi-shield-check" iconColor="text-success">
                    보안 중심형
                </x-help-title>
                <ul class="small text-muted mb-0">
                    <li>관리자만 작성 허용</li>
                    <li>사용자 글 승인 필요</li>
                    <li>기업 공지사항 포럼에 적합</li>
                </ul>

                <x-help-title icon="bi-people" iconColor="text-primary">
                    커뮤니티형
                </x-help-title>
                <ul class="small text-muted mb-0">
                    <li>회원 작성 + 선택적 승인</li>
                    <li>투표 및 태그 기능 활성화</li>
                    <li>커뮤니티 토론 포럼에 적합</li>
                </ul>

                <x-help-title icon="bi-globe" iconColor="text-info" marginBottom="mb-0">
                    개방형
                </x-help-title>
                <ul class="small text-muted mb-0">
                    <li>비회원도 작성 가능</li>
                    <li>즉시 발행 + 사후 관리</li>
                    <li>자유 게시판에 적합</li>
                </ul>
            </x-help>
        </x-content-side>
    </x-content>
</div>
@endsection

