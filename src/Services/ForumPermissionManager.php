<?php

namespace Jiny\Post\Services;

use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Services\JwtService;

/**
 * Forum Permission Manager
 *
 * 포럼 글작성 권한 관리를 위한 서비스 클래스입니다.
 */
class ForumPermissionManager
{
    protected $configManager;
    protected $jwtService;

    public function __construct(
        ForumConfigManager $configManager,
        JwtService $jwtService = null
    ) {
        $this->configManager = $configManager;
        $this->jwtService = $jwtService;
    }

    /**
     * ForumConfigManager 인스턴스 반환
     *
     * @return ForumConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

    /**
     * 사용자의 글작성 권한 확인
     *
     * @param mixed $user 사용자 객체 (선택적)
     * @return array ['allowed' => bool, 'reason' => string, 'user_type' => string]
     */
    public function canWrite($user = null)
    {
        // 사용자 정보 가져오기
        if ($user === null) {
            $user = $this->getCurrentUser();
        }

        // 게스트 사용자인 경우
        if (!$user) {
            $permission = $this->checkGuestWritePermission();
            $permission['user_type'] = 'guest';
            return $permission;
        }

        // 로그인한 사용자인 경우
        $permission = $this->checkUserWritePermission($user);
        $permission['user_type'] = $this->isAdmin($user) ? 'admin' : 'user';
        return $permission;
    }

    /**
     * 현재 인증된 사용자 정보 가져오기 (관리자 세션 우선, JWT 보조)
     */
    public function getCurrentUser()
    {
        // 1. Laravel 기본 세션 인증 확인 (관리자용)
        $sessionUser = Auth::user();
        if ($sessionUser) {
            // 세션 사용자에게 인증 타입 표시
            $sessionUser->auth_type = 'session';
            return $sessionUser;
        }

        // 2. JWT 토큰에서 사용자 정보 추출 시도 (샤딩 사용자용)
        if ($this->jwtService) {
            try {
                $token = $this->jwtService->getTokenFromRequest();
                if ($token) {
                    $user = $this->jwtService->getUserFromToken($token);
                    if ($user) {
                        // JWT 사용자에게 인증 타입 표시
                        $user->auth_type = 'jwt';
                        return $user;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('JWT token validation failed in forum permission check: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * 게스트 사용자 글작성 권한 확인
     */
    protected function checkGuestWritePermission()
    {
        $guestWriteEnabled = $this->configManager->getPolicy('guest_write', false);

        if (!$guestWriteEnabled) {
            return [
                'allowed' => false,
                'reason' => 'guest_write_disabled',
                'message' => '게스트는 글을 작성할 수 없습니다. 로그인이 필요합니다.'
            ];
        }

        return [
            'allowed' => true,
            'reason' => 'guest_write_allowed',
            'message' => '게스트 글작성이 허용됩니다.'
        ];
    }

    /**
     * 로그인한 사용자 글작성 권한 확인
     */
    protected function checkUserWritePermission($user)
    {
        // 관리자 권한 확인
        if ($this->isAdmin($user)) {
            $adminWriteEnabled = $this->configManager->getPolicy('admin_write', true);

            if (!$adminWriteEnabled) {
                return [
                    'allowed' => false,
                    'reason' => 'admin_write_disabled',
                    'message' => '관리자 글작성이 비활성화되어 있습니다.'
                ];
            }

            return [
                'allowed' => true,
                'reason' => 'admin_write_allowed',
                'message' => '관리자 권한으로 글작성이 허용됩니다.'
            ];
        }

        // 일반 사용자 권한 확인
        $userWriteEnabled = $this->configManager->getPolicy('user_write', true);

        if (!$userWriteEnabled) {
            return [
                'allowed' => false,
                'reason' => 'user_write_disabled',
                'message' => '일반 사용자 글작성이 비활성화되어 있습니다.'
            ];
        }

        // 사용자 계정 상태 확인
        $accountCheck = $this->checkUserAccountStatus($user);
        if (!$accountCheck['allowed']) {
            return $accountCheck;
        }

        return [
            'allowed' => true,
            'reason' => 'user_write_allowed',
            'message' => '글작성이 허용됩니다.'
        ];
    }

    /**
     * 사용자 계정 상태 확인
     */
    protected function checkUserAccountStatus($user)
    {
        // 계정 차단 상태 확인
        if (isset($user->is_blocked) && $user->is_blocked) {
            return [
                'allowed' => false,
                'reason' => 'account_blocked',
                'message' => '차단된 계정입니다.'
            ];
        }

        // 계정 활성화 상태 확인
        if (isset($user->is_active) && !$user->is_active) {
            return [
                'allowed' => false,
                'reason' => 'account_inactive',
                'message' => '비활성화된 계정입니다.'
            ];
        }

        // 이메일 인증 확인 (선택적)
        if (config('forum.require_email_verification', false)) {
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                return [
                    'allowed' => false,
                    'reason' => 'email_unverified',
                    'message' => '이메일 인증이 필요합니다.'
                ];
            }
        }

        return [
            'allowed' => true,
            'reason' => 'account_active',
            'message' => '계정 상태가 정상입니다.'
        ];
    }

    /**
     * 관리자 권한 확인
     */
    protected function isAdmin($user)
    {
        if (!$user) {
            return false;
        }

        // 1. 세션 기반 사용자 (Laravel user 테이블)
        if (isset($user->auth_type) && $user->auth_type === 'session') {
            // isAdmin 플래그 확인
            if (isset($user->isAdmin) && $user->isAdmin) {
                return true;
            }

            // 관리자 역할 확인 (Laravel의 role 시스템 사용하는 경우)
            if (method_exists($user, 'hasRole')) {
                return $user->hasRole('admin') || $user->hasRole('super-admin');
            }

            // role 필드 직접 확인
            if (isset($user->role) && in_array($user->role, ['admin', 'super-admin'])) {
                return true;
            }

            // utype 필드 확인 (jiny/admin 패키지 방식)
            if (isset($user->utype) && $user->utype === 'admin') {
                return true;
            }

            return false;
        }

        // 2. JWT 기반 사용자 (샤딩 users_0xx 테이블)
        if (isset($user->auth_type) && $user->auth_type === 'jwt') {
            // JWT 사용자는 일반적으로 관리자가 아님 (샤딩 회원)
            // 하지만 특별한 경우가 있다면 여기서 처리

            // 샤딩 사용자 중에서도 관리자 권한이 있는 경우 확인
            if (isset($user->isAdmin) && $user->isAdmin) {
                return true;
            }

            if (isset($user->role) && in_array($user->role, ['admin', 'super-admin'])) {
                return true;
            }

            return false;
        }

        // 3. 레거시 코드 호환성 (auth_type이 없는 경우)
        // isAdmin 플래그 확인
        if (isset($user->isAdmin) && $user->isAdmin) {
            return true;
        }

        // 관리자 역할 확인
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super-admin');
        }

        // role 필드 직접 확인
        if (isset($user->role) && in_array($user->role, ['admin', 'super-admin'])) {
            return true;
        }

        // utype 필드 확인
        if (isset($user->utype) && $user->utype === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * 글 승인이 필요한지 확인
     */
    public function requiresApproval($user = null)
    {
        if ($user === null) {
            $user = $this->getCurrentUser();
        }

        // 관리자는 자동 승인
        if ($user && $this->isAdmin($user)) {
            $autoApproveAdmin = $this->configManager->getPolicy('auto_approve_admin', true);
            return !$autoApproveAdmin;
        }

        // 일반 사용자 승인 정책
        return $this->configManager->getPolicy('user_approval', false);
    }

    /**
     * 권한 확인 결과를 HTTP 응답으로 변환
     */
    public function getPermissionResponse($user = null)
    {
        $permission = $this->canWrite($user);

        if (!$permission['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $permission['message'],
                'reason' => $permission['reason'],
            ], 403);
        }

        return null; // 권한이 있으면 null 반환
    }

    /**
     * 미들웨어에서 사용할 수 있는 권한 체크
     */
    public function checkPermissionOrFail($user = null)
    {
        $permission = $this->canWrite($user);

        if (!$permission['allowed']) {
            if (request()->expectsJson()) {
                abort(403, $permission['message']);
            } else {
                abort(403, $permission['message']);
            }
        }

        return true;
    }
}