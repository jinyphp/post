<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Blog Index Controller
 *
 * 블로그 목록 페이지를 처리하는 컨트롤러입니다.
 */
class BlogIndex extends Controller
{
    /**
     * 블로그 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = DB::table('site_blog')
                   ->select([
                       'site_blog.*',
                       'site_blog_cate.name as category_name',
                       'site_blog_cate.color as category_color',
                       'site_blog_cate.icon as category_icon'
                   ])
                   ->leftJoin('site_blog_cate', 'site_blog.category_slug', '=', 'site_blog_cate.slug')
                   ->where('site_blog.status', 'published')
                   ->where('site_blog.published_at', '<=', now());

        // 카테고리 필터
        if ($category = $request->get('category')) {
            $query->where('site_blog.category_slug', $category);
        }

        // 검색
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('site_blog.title', 'LIKE', "%{$search}%")
                  ->orWhere('site_blog.content', 'LIKE', "%{$search}%")
                  ->orWhere('site_blog.tags', 'LIKE', "%{$search}%");
            });
        }

        // 정렬 (추천글 우선, 상단 고정 우선, 최신순)
        $posts = $query->orderBy('site_blog.is_sticky', 'desc')
                      ->orderBy('site_blog.is_featured', 'desc')
                      ->orderBy('site_blog.published_at', 'desc')
                      ->paginate(9);

        // 카테고리 목록 가져오기
        $categories = DB::table('site_blog_cate')
                        ->where('is_active', 1)
                        ->where('show_in_menu', 1)
                        ->where('is_private', 0)
                        ->orderBy('sort_order', 'asc')
                        ->get();

        // 최신 글 (사이드바용)
        $latestPosts = DB::table('site_blog')
                         ->where('status', 'published')
                         ->where('published_at', '<=', now())
                         ->orderBy('published_at', 'desc')
                         ->limit(5)
                         ->get();

        // 인기 글 (조회수 기준)
        $popularPosts = DB::table('site_blog')
                          ->where('status', 'published')
                          ->where('published_at', '<=', now())
                          ->orderBy('views', 'desc')
                          ->limit(5)
                          ->get();

        return view('jiny-post::www.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'latestPosts' => $latestPosts,
            'popularPosts' => $popularPosts,
            'currentCategory' => $category,
            'searchTerm' => $search,
        ]);
    }
}