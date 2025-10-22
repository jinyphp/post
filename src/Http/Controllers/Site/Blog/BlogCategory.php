<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Blog Category Controller
 *
 * 카테고리별 블로그 목록을 처리하는 컨트롤러입니다.
 */
class BlogCategory extends Controller
{
    /**
     * 카테고리별 블로그 목록
     */
    public function __invoke($slug, Request $request)
    {
        // 카테고리 정보 조회
        $category = DB::table('site_blog_cate')
                      ->where('slug', $slug)
                      ->where('is_active', 1)
                      ->where('is_private', 0)
                      ->first();

        if (!$category) {
            abort(404, '카테고리를 찾을 수 없습니다.');
        }

        // 해당 카테고리의 블로그 글 조회
        $query = DB::table('site_blog')
                   ->select([
                       'site_blog.*',
                       'site_blog_cate.name as category_name',
                       'site_blog_cate.color as category_color',
                       'site_blog_cate.icon as category_icon'
                   ])
                   ->leftJoin('site_blog_cate', 'site_blog.category_slug', '=', 'site_blog_cate.slug')
                   ->where('site_blog.category_slug', $slug)
                   ->where('site_blog.status', 'published')
                   ->where('site_blog.published_at', '<=', now());

        // 검색
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('site_blog.title', 'LIKE', "%{$search}%")
                  ->orWhere('site_blog.content', 'LIKE', "%{$search}%")
                  ->orWhere('site_blog.tags', 'LIKE', "%{$search}%");
            });
        }

        $posts = $query->orderBy('site_blog.is_sticky', 'desc')
                      ->orderBy('site_blog.is_featured', 'desc')
                      ->orderBy('site_blog.published_at', 'desc')
                      ->paginate(9);

        // 카테고리 목록
        $categories = DB::table('site_blog_cate')
                        ->where('is_active', 1)
                        ->where('show_in_menu', 1)
                        ->where('is_private', 0)
                        ->orderBy('sort_order', 'asc')
                        ->get();

        // 최신 글 (사이드바용)
        $latestPosts = DB::table('site_blog')
                         ->select([
                             'site_blog.*',
                             'site_blog_cate.name as category_name',
                             'site_blog_cate.color as category_color',
                             'site_blog_cate.icon as category_icon'
                         ])
                         ->leftJoin('site_blog_cate', 'site_blog.category_slug', '=', 'site_blog_cate.slug')
                         ->where('site_blog.status', 'published')
                         ->where('site_blog.published_at', '<=', now())
                         ->orderBy('site_blog.published_at', 'desc')
                         ->limit(5)
                         ->get();

        return view('jiny-post::www.blog.category', [
            'posts' => $posts,
            'category' => $category,
            'categories' => $categories,
            'latestPosts' => $latestPosts,
            'searchTerm' => $request->get('search'),
        ]);
    }
}