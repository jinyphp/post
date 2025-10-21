<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogConfig;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Post\Facades\BlogConfig;

/**
 * Admin Blog Config Index Controller
 *
 * 블로그 설정 폼을 표시하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogConfigIndex extends Controller
{
    /**
     * 블로그 설정 폼 표시
     */
    public function __invoke(Request $request)
    {
        // BlogConfig Facade를 사용하여 설정 로드 (캐시 클리어 후 새로 로드)
        BlogConfig::clearCache();
        $config = BlogConfig::load();

        // 블로그 통계 데이터 로드
        $stats = [
            'total_blogs' => DB::table('site_blogs')->count(),
            'total_posts' => DB::table('site_blogs')->count(),
            'published_blogs' => DB::table('site_blogs')->where('status', 'published')->count(),
            'published_posts' => DB::table('site_blogs')->where('status', 'published')->count(),
            'pending_blogs' => DB::table('site_blogs')->where('status', 'pending')->count(),
            'pending_posts' => DB::table('site_blogs')->where('status', 'pending')->count(),
            'draft_blogs' => DB::table('site_blogs')->where('status', 'draft')->count(),
            'draft_posts' => DB::table('site_blogs')->where('status', 'draft')->count(),
            'admin_blogs' => DB::table('site_blogs')->where('user_type', 'admin')->count(),
            'user_blogs' => DB::table('site_blogs')->where('user_type', '!=', 'admin')->count(),
            'total_categories' => DB::table('site_blog_cate')->count(),
            'active_categories' => DB::table('site_blog_cate')->where('is_active', true)->count(),
        ];

        return view('jiny-post::admin.blog_config.config', compact('config', 'stats'));
    }
}