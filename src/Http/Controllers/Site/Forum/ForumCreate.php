<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Jiny\Post\Services\ForumPermissionManager;
use Jiny\Site\Facades\Header;
use Jiny\Site\Facades\Footer;

/**
 * 포럼 글작성 폼 컨트롤러
 */
class ForumCreate extends Controller
{
    protected $viewPath = 'jiny-post::www.forum';
    protected $permissionManager;

    public function __construct(ForumPermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function __invoke(Request $request)
    {
        $table = 'site_forum';

        if (!Schema::hasTable($table)) {
            abort(404, '포럼 테이블이 존재하지 않습니다.');
        }

        // 글작성 권한 확인
        $permission = $this->permissionManager->canWrite();
        if (!$permission['allowed']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $permission['message'],
                    'reason' => $permission['reason'],
                ], 403);
            } else {
                return redirect()->route('forum.index')
                    ->with('error', $permission['message']);
            }
        }

        // 헤더와 푸터 경로 가져오기
        $header = Header::getDefaultHeaderPath();
        $footer = Footer::getDefaultFooterPath();

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

        return view("{$this->viewPath}.create", [
            'header' => $header,
            'footer' => $footer,
            'permission' => $permission,
            'requires_approval' => $this->permissionManager->requiresApproval(),
            'forumSettings' => $forumSettings,
        ]);
    }
}