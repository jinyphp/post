<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

/**
 * 포럼 좋아요 AJAX 컨트롤러
 */
class ForumLike extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $table = 'site_forum';

        if (!Schema::hasTable($table)) {
            return response()->json([
                'success' => false,
                'message' => '포럼 테이블이 존재하지 않습니다.'
            ], 404);
        }

        // 포럼 글 존재 확인
        $post = DB::table($table)->find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => '포럼 글을 찾을 수 없습니다.'
            ], 404);
        }

        try {
            // 좋아요 토글 처리
            $result = $this->toggleLike($id, $request->ip());

            return response()->json([
                'success' => true,
                'liked' => $result['liked'],
                'likeCount' => $result['likeCount'],
                'message' => $result['liked'] ? '좋아요를 눌렀습니다.' : '좋아요를 취소했습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '좋아요 처리 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 좋아요 토글 처리
     */
    private function toggleLike($postId, $ipAddress)
    {
        $likeTable = 'site_forum_likes';

        // likes 테이블이 없으면 생성
        if (!Schema::hasTable($likeTable)) {
            Schema::create($likeTable, function ($table) {
                $table->id();
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ip_address');
                $table->timestamps();

                $table->unique(['post_id', 'user_id', 'ip_address']);
                $table->index(['post_id']);
            });
        }

        $user = Auth::user();
        $userId = $user ? $user->id : null;

        // 기존 좋아요 확인
        $existingLike = DB::table($likeTable)
            ->where('post_id', $postId)
            ->where('ip_address', $ipAddress);

        if ($userId) {
            $existingLike->where('user_id', $userId);
        } else {
            $existingLike->whereNull('user_id');
        }

        $like = $existingLike->first();

        if ($like) {
            // 좋아요 취소
            DB::table($likeTable)->where('id', $like->id)->delete();

            // 포럼 글의 좋아요 수 감소
            DB::table('site_forum')
                ->where('id', $postId)
                ->decrement('like');

            $liked = false;
        } else {
            // 좋아요 추가
            DB::table($likeTable)->insert([
                'post_id' => $postId,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 포럼 글의 좋아요 수 증가
            DB::table('site_forum')
                ->where('id', $postId)
                ->increment('like');

            $liked = true;
        }

        // 업데이트된 좋아요 수 조회
        $updatedPost = DB::table('site_forum')->find($postId);
        $likeCount = $updatedPost->like ?? 0;

        return [
            'liked' => $liked,
            'likeCount' => $likeCount
        ];
    }
}