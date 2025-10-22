<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Post\Services\ForumPermissionManager;
use Jiny\Site\Facades\Header;
use Jiny\Site\Facades\Footer;

/**
 * 포럼 글 수정 폼 컨트롤러
 */
class ForumEdit extends Controller
{
    protected $viewPath = 'jiny-post::www.forum';
    protected $permissionManager;

    public function __construct(ForumPermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function __invoke(Request $request, $id)
    {
        $table = 'site_forum';

        if (!Schema::hasTable($table)) {
            abort(404, '포럼 테이블이 존재하지 않습니다.');
        }

        // 포럼 글 조회
        $forum = DB::table($table)->where('id', $id)->first();

        if (!$forum) {
            abort(404, '존재하지 않는 글입니다.');
        }

        // 현재 사용자 정보 가져오기
        $user = $this->permissionManager->getCurrentUser();

        // 수정 권한 확인
        if (!$this->canEditForum($forum, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '이 글을 수정할 권한이 없습니다.',
                ], 403);
            } else {
                return redirect()->route('forum.show', $id)
                    ->with('error', '이 글을 수정할 권한이 없습니다.');
            }
        }

        // 헤더와 푸터 경로 가져오기
        $header = Header::getDefaultHeaderPath();
        $footer = Footer::getDefaultFooterPath();

        // 포럼 이미지들 조회
        $forumImages = DB::table('site_forum_images')
            ->where('forum_id', $id)
            ->orderBy('sort_order')
            ->get();

        // 포럼 설정 정보
        $config = $this->permissionManager->getConfigManager();
        $forumSettings = [
            'enable_tags' => $config->getSetting('enable_tags', true),
            'enable_file_upload' => $config->getSetting('enable_file_upload', true),
            'max_images_per_post' => $config->getSetting('max_images_per_post', 10),
            'max_file_size_mb' => $config->getSetting('max_file_size_mb', 5),
            'max_tags_per_post' => $config->getSetting('max_tags_per_post', 5),
            'auto_excerpt_length' => $config->getSetting('auto_excerpt_length', 150),
        ];

        return view("{$this->viewPath}.edit", [
            'forum' => $forum,
            'header' => $header,
            'footer' => $footer,
            'requires_approval' => $this->permissionManager->requiresApproval($user),
            'forumSettings' => $forumSettings,
            'forumImages' => $forumImages,
        ]);
    }

    /**
     * 포럼 글 수정 권한 확인
     */
    protected function canEditForum($forum, $user)
    {
        // 관리자는 모든 글 수정 가능
        if ($user && $this->isAdmin($user)) {
            return true;
        }

        // 로그인하지 않은 경우
        if (!$user) {
            return false;
        }

        // 본인이 작성한 글인지 확인
        if (isset($forum->user_id) && $user->id == $forum->user_id) {
            return true;
        }

        // UUID로 확인 (샤딩 사용자인 경우)
        if (isset($forum->user_uuid) && isset($user->uuid) && $user->uuid == $forum->user_uuid) {
            return true;
        }

        return false;
    }

    /**
     * 관리자 권한 확인
     */
    protected function isAdmin($user)
    {
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

        // utype 필드 확인 (jiny/admin 패키지 방식)
        if (isset($user->utype) && $user->utype === 'admin') {
            return true;
        }

        return false;
    }
}