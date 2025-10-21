<?php

namespace Jiny\Post\Http\Controllers\Admin\ForumConfig;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Post\Facades\ForumConfig;

/**
 * Admin Forum Config Index Controller
 *
 * 포럼 설정 폼을 표시하는 단일 액션 컨트롤러입니다.
 */
class AdminForumConfigIndex extends Controller
{
    /**
     * 포럼 설정 폼 표시
     */
    public function __invoke(Request $request)
    {
        // ForumConfig Facade를 사용하여 설정 로드 (캐시 클리어 후 새로 로드)
        ForumConfig::clearCache();
        $config = ForumConfig::load();

        // 포럼 통계 데이터 로드
        $stats = [
            'total_posts' => DB::table('site_forum')->count(),
            'published_posts' => DB::table('site_forum')->where('status', 'published')->count(),
            'draft_posts' => DB::table('site_forum')->where('status', 'draft')->count(),
            'pending_posts' => DB::table('site_forum')->where('status', 'pending')->count(),
            'total_categories' => DB::table('site_forum_cate')->count(),
            'active_categories' => DB::table('site_forum_cate')->where('is_active', true)->count(),
        ];

        return view('jiny-post::admin.forum_config.config', compact('config', 'stats'));
    }
}
