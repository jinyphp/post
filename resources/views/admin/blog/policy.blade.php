@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '블로그 정책 설정')

@section('content')
<div class="container-fluid">
    <!-- 헤딩 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3><i class="bi bi-shield-check me-2 text-primary"></i>블로그 정책 설정</h3>
            <p class="text-muted mb-0">블로그 작성 권한과 관련 정책을 설정합니다.</p>
        </div>
        <div>
            <a href="{{ route('admin.cms.blog') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> 블로그 관리
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
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_blogs'] }}</div>
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
                    <div class="h4 mb-0 fw-bold">{{ $stats['published_blogs'] }}</div>
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
                    <div class="h4 mb-0 fw-bold">{{ $stats['pending_blogs'] }}</div>
                    <div class="text-muted small">승인 대기</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['admin_blogs'] }}</div>
                    <div class="text-muted small">관리자 글</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['user_blogs'] }}</div>
                    <div class="text-muted small">사용자 글</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">
                        <i class="bi bi-pencil fs-3"></i>
                    </div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['draft_blogs'] }}</div>
                    <div class="text-muted small">초안</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 정책 설정 폼 -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-gear me-2 text-primary"></i>
                블로그 정책 설정
            </h5>
        </div>
        <div class="card-body">
            <form id="policyForm" method="POST">
                @csrf

                <!-- 작성 권한 설정 -->
                <div class="row">
                    <div class="col-lg-6">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-pencil-square me-2"></i>작성 권한 설정
                        </h6>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="admin_write" name="blog_policy_admin_write" value="1"
                                       {{ $currentPolicies['blog_policy_admin_write'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="admin_write">
                                    <strong>관리자 작성 허용</strong>
                                    <div class="text-muted small">시스템 관리자가 블로그를 작성할 수 있습니다.</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="user_write" name="blog_policy_user_write" value="1"
                                       {{ $currentPolicies['blog_policy_user_write'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_write">
                                    <strong>일반 사용자 작성 허용</strong>
                                    <div class="text-muted small">로그인한 일반 사용자가 블로그를 작성할 수 있습니다.</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="guest_write" name="blog_policy_guest_write" value="1"
                                       {{ $currentPolicies['blog_policy_guest_write'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="guest_write">
                                    <strong>비회원 작성 허용</strong>
                                    <div class="text-muted small">로그인하지 않은 사용자도 블로그를 작성할 수 있습니다.</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-check-circle me-2"></i>승인 및 관리 정책
                        </h6>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="user_approval" name="blog_policy_user_approval" value="1"
                                       {{ $currentPolicies['blog_policy_user_approval'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_approval">
                                    <strong>사용자 글 승인 필요</strong>
                                    <div class="text-muted small">일반 사용자의 글은 관리자 승인 후 발행됩니다.</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_approve_admin" name="blog_policy_auto_approve_admin" value="1"
                                       {{ $currentPolicies['blog_policy_auto_approve_admin'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_approve_admin">
                                    <strong>관리자 글 자동 승인</strong>
                                    <div class="text-muted small">관리자의 글은 승인 절차 없이 바로 발행됩니다.</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="featured_admin_only" name="blog_policy_featured_admin_only" value="1"
                                       {{ $currentPolicies['blog_policy_featured_admin_only'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured_admin_only">
                                    <strong>추천글은 관리자만</strong>
                                    <div class="text-muted small">추천글 설정은 관리자만 가능합니다.</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="category_restriction" name="blog_policy_category_restriction" value="1"
                                       {{ $currentPolicies['blog_policy_category_restriction'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="category_restriction">
                                    <strong>카테고리 제한</strong>
                                    <div class="text-muted small">사용자가 사용할 수 있는 카테고리를 제한합니다.</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> 정책 저장
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 정책 추천 안내 -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0">
                <i class="bi bi-lightbulb me-2 text-warning"></i>
                정책 설정 권장사항
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-success">
                            <i class="bi bi-shield-check me-1"></i>보안 중심형
                        </h6>
                        <ul class="small text-muted mb-0">
                            <li>관리자만 작성 허용</li>
                            <li>엄격한 품질 관리</li>
                            <li>기업/공식 블로그에 적합</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-primary">
                            <i class="bi bi-people me-1"></i>커뮤니티형
                        </h6>
                        <ul class="small text-muted mb-0">
                            <li>회원 작성 + 승인 시스템</li>
                            <li>적절한 품질 관리</li>
                            <li>커뮤니티 사이트에 적합</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-info">
                            <i class="bi bi-globe me-1"></i>개방형
                        </h6>
                        <ul class="small text-muted mb-0">
                            <li>누구나 작성 가능</li>
                            <li>최대한의 참여도</li>
                            <li>오픈 플랫폼에 적합</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('policyForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // 체크되지 않은 체크박스들도 0으로 전송
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                formData.append(checkbox.name, '0');
            }
        });

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i> 저장 중...';

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message || '정책 저장에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Policy save error:', error);
            showAlert('danger', '서버 오류가 발생했습니다.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;

        const container = document.querySelector('.container-fluid');
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHtml;
        const alert = tempDiv.firstElementChild;

        container.insertBefore(alert, container.firstElementChild);

        // 5초 후 자동 제거
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endpush

@push('styles')
<style>
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.card {
    transition: all 0.15s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>
@endpush