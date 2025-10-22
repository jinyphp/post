<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Blog Create Controller
 *
 * 블로그 글 작성 폼을 처리하는 컨트롤러입니다.
 */
class BlogCreate extends Controller
{
    /**
     * 블로그 글 작성 폼 표시
     */
    public function __invoke(Request $request)
    {
        // 인증된 사용자인지 확인
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 카테고리 목록 가져오기
        $categories = DB::table('site_blog_cate')
                        ->where('is_active', 1)
                        ->where('is_private', 0)
                        ->orderBy('sort_order', 'asc')
                        ->get();

        return view('jiny-post::www.blog.create', [
            'categories' => $categories,
        ]);
    }
}