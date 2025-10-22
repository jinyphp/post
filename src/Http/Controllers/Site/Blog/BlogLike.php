<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Blog Like Controller
 *
 * 블로그 좋아요 기능을 처리하는 컨트롤러입니다.
 */
class BlogLike extends Controller
{
    /**
     * 블로그 글 좋아요 토글
     */
    public function __invoke($slug)
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
}