<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Post\Http\Controllers\Admin\Blog\BlogPolicyHelper;
use Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig;

/**
 * Single Action Controller for Blog Create Form
 *
 * 블로그 생성 폼 표시를 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogCreate extends Controller
{
    /**
     * 블로그 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
        return $this->showCreateForm($request);
    }

    /**
     * 생성 폼 표시
     */
    protected function showCreateForm(Request $request)
    {
        try {
            // 정책 확인 - 블로그 작성 권한 체크
            $user = auth()->user();
            if (!BlogPolicyHelper::canUserWriteBlog($user)) {
                return redirect()->route('admin.cms.blog')
                    ->with('error', '블로그 작성 권한이 없습니다. 정책 설정을 확인해주세요.');
            }

            // 카테고리 목록
            $categories = DB::table('site_blog_cate')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // JSON 설정 값 가져오기
            $config = [
                'default_status' => AdminBlogConfig::getSettingValue('default_status', 'draft'),
                'max_images_per_post' => AdminBlogConfig::getSettingValue('max_images_per_post', 10),
                'auto_excerpt_length' => AdminBlogConfig::getSettingValue('auto_excerpt_length', 200),
                'enable_comments' => AdminBlogConfig::getSettingValue('enable_comments', true),
                'seo_enabled' => AdminBlogConfig::getSettingValue('seo_enabled', true),
            ];

            return view('jiny-post::admin.blog.create', compact('categories', 'config'));

        } catch (\Exception $e) {
            \Log::error('Blog create form error', ['error' => $e->getMessage()]);

            return redirect()->route('admin.cms.blog')
                ->with('error', '블로그 생성 폼을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}