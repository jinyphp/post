<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Single Action Controller for Blog Edit
 *
 * 블로그 수정을 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogEdit extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        return $this->showEditForm($request, $id);
    }

    /**
     * 수정 폼 표시
     */
    protected function showEditForm(Request $request, $id)
    {
        try {
            $blog = DB::table($this->table)->find($id);

            if (!$blog) {
                return redirect()->route('admin.cms.blog')
                    ->with('error', '수정하려는 블로그 글을 찾을 수 없습니다.');
            }

            // 카테고리 목록
            $categories = DB::table('site_blog_cate')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // 블로그 이미지 가져오기
            $blogImages = DB::table('site_blog_images')
                ->where('blog_id', $id)
                ->orderBy('sort_order', 'asc')
                ->get();

            return view('jiny-post::admin.blog.edit', compact('blog', 'categories', 'blogImages'));

        } catch (\Exception $e) {
            \Log::error('Blog edit form error', ['id' => $id, 'error' => $e->getMessage()]);

            return redirect()->route('admin.cms.blog')
                ->with('error', '블로그 수정 폼을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}