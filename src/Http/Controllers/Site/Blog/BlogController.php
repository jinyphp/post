<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Site Blog Controller
 *
 * 블로그 목록 페이지를 처리하는 컨트롤러입니다.
 */
class BlogController extends Controller
{
    /**
     * 블로그 목록 표시
     */
    public function index(Request $request)
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

    /**
     * 블로그 상세 보기
     */
    public function show($slug)
    {
        // 블로그 글 조회
        $post = DB::table('site_blog')
                  ->select([
                      'site_blog.*',
                      'site_blog_cate.name as category_name',
                      'site_blog_cate.color as category_color',
                      'site_blog_cate.icon as category_icon'
                  ])
                  ->leftJoin('site_blog_cate', 'site_blog.category_slug', '=', 'site_blog_cate.slug')
                  ->where('site_blog.slug', $slug)
                  ->where('site_blog.status', 'published')
                  ->where('site_blog.published_at', '<=', now())
                  ->first();

        if (!$post) {
            abort(404, '블로그 글을 찾을 수 없습니다.');
        }

        // 조회수 증가
        DB::table('site_blog')
          ->where('id', $post->id)
          ->increment('views');

        // 관련 글 (같은 카테고리)
        $relatedPosts = DB::table('site_blog')
                          ->select([
                              'site_blog.*',
                              'site_blog_cate.name as category_name',
                              'site_blog_cate.color as category_color'
                          ])
                          ->leftJoin('site_blog_cate', 'site_blog.category_slug', '=', 'site_blog_cate.slug')
                          ->where('site_blog.category_slug', $post->category_slug)
                          ->where('site_blog.id', '!=', $post->id)
                          ->where('site_blog.status', 'published')
                          ->where('site_blog.published_at', '<=', now())
                          ->orderBy('site_blog.published_at', 'desc')
                          ->limit(3)
                          ->get();

        // 이전/다음 글
        $prevPost = DB::table('site_blog')
                      ->where('published_at', '<', $post->published_at)
                      ->where('status', 'published')
                      ->orderBy('published_at', 'desc')
                      ->first();

        $nextPost = DB::table('site_blog')
                      ->where('published_at', '>', $post->published_at)
                      ->where('status', 'published')
                      ->orderBy('published_at', 'asc')
                      ->first();

        // 카테고리 목록
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
                         ->where('id', '!=', $post->id)
                         ->orderBy('published_at', 'desc')
                         ->limit(5)
                         ->get();

        // 댓글 기능 관련 데이터 (댓글 허용인 경우에만)
        $comments = collect();
        $commentsTree = collect();
        $commentStats = ['total' => 0, 'approved' => 0];

        if ($post->allow_comments) {
            // 댓글 가져오기 (승인된 댓글만)
            $comments = DB::table('site_blog_comments')
                ->leftJoin('users', 'site_blog_comments.user_id', '=', 'users.id')
                ->select([
                    'site_blog_comments.*',
                    'users.name as user_name'
                ])
                ->where('site_blog_comments.blog_id', $post->id)
                ->where('site_blog_comments.is_approved', 1)
                ->orderBy('site_blog_comments.created_at', 'asc')
                ->get();

            // 댓글 계층 구조 생성
            $commentsTree = $this->buildCommentsTree($comments);

            // 댓글 통계
            $commentStats = [
                'total' => DB::table('site_blog_comments')->where('blog_id', $post->id)->count(),
                'approved' => DB::table('site_blog_comments')->where('blog_id', $post->id)->where('is_approved', 1)->count()
            ];
        }

        return view('jiny-post::www.blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost,
            'categories' => $categories,
            'latestPosts' => $latestPosts,
            'commentsTree' => $commentsTree,
            'commentStats' => $commentStats,
        ]);
    }

    /**
     * 카테고리별 블로그 목록
     */
    public function category($slug, Request $request)
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

    /**
     * 블로그 글 좋아요 토글
     */
    public function like($slug)
    {
        // 블로그 글 조회
        $post = DB::table('site_blog')
                  ->where('slug', $slug)
                  ->where('status', 'published')
                  ->where('published_at', '<=', now())
                  ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => '블로그 글을 찾을 수 없습니다.'
            ], 404);
        }

        // 세션을 이용한 간단한 좋아요 구현
        $sessionKey = 'blog_liked_' . $post->id;
        $liked = session()->has($sessionKey);

        if ($liked) {
            // 좋아요 취소
            session()->forget($sessionKey);
            DB::table('site_blog')
              ->where('id', $post->id)
              ->decrement('likes');
            $newLikeCount = $post->likes - 1;
            $isLiked = false;
        } else {
            // 좋아요 추가
            session()->put($sessionKey, true);
            DB::table('site_blog')
              ->where('id', $post->id)
              ->increment('likes');
            $newLikeCount = $post->likes + 1;
            $isLiked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $isLiked,
            'likes' => max(0, $newLikeCount),
            'message' => $isLiked ? '좋아요를 추가했습니다.' : '좋아요를 취소했습니다.'
        ]);
    }

    /**
     * 댓글을 계층 구조로 변환
     */
    private function buildCommentsTree($comments)
    {
        $commentMap = [];
        $tree = [];

        // 먼저 모든 댓글을 ID로 맵핑
        foreach ($comments as $comment) {
            $comment->children = [];
            $commentMap[$comment->id] = $comment;
        }

        // 계층 구조 생성
        foreach ($comments as $comment) {
            if ($comment->parent_id && isset($commentMap[$comment->parent_id])) {
                // 답글인 경우 부모에 추가
                $commentMap[$comment->parent_id]->children[] = $comment;
            } else {
                // 최상위 댓글인 경우 트리에 직접 추가
                $tree[] = $comment;
            }
        }

        return collect($tree);
    }
}