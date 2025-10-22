<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Blog Show Controller
 *
 * 블로그 상세 보기를 처리하는 컨트롤러입니다.
 */
class BlogShow extends Controller
{
    /**
     * 블로그 상세 보기
     */
    public function __invoke($slug)
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

        // 블로그 이미지 가져오기
        $blogImages = DB::table('site_blog_images')
            ->where('blog_id', $post->id)
            ->orderBy('sort_order', 'asc')
            ->get();

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

        if ($post->allow_comments ?? false) {
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
            'blogImages' => $blogImages,
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