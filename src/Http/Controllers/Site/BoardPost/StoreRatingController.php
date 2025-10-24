<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;

/**
 * 평가(좋아요/별점) 저장 컨트롤러
 */
class StoreRatingController extends Controller
{
    use BoardPermissions;

    public function __invoke(Request $request, $code, $postId)
    {
        // JWT 또는 세션 기반 인증 설정
        $user = $this->setupAuth($request);

        $board = $this->getBoardInfo($code);

        if (!$board) {
            abort(404, '게시판을 찾을 수 없습니다.');
        }

        // 로그인 필요
        if (!Auth::check()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $table = "site_board_" . $code;

        if (!Schema::hasTable($table)) {
            abort(404, '게시판 테이블이 존재하지 않습니다.');
        }

        // 게시글 존재 확인
        $post = DB::table($table)->find($postId);
        if (!$post) {
            abort(404, '게시글을 찾을 수 없습니다.');
        }

        // 입력 검증
        $request->validate([
            'type' => 'required|in:like,rating',
            'is_like' => 'sometimes|boolean',
            'rating' => 'sometimes|integer|min:1|max:5',
        ]);

        $ratingTable = $table . "_ratings";

        // 평가 테이블이 없으면 생성
        if (!Schema::hasTable($ratingTable)) {
            $this->createRatingTable($ratingTable);
        }

        $type = $request->input('type');
        $userId = Auth::id();

        // 기존 평가 확인
        $existingRating = DB::table($ratingTable)
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($type === 'like') {
            $isLike = $request->input('is_like', false);

            if ($existingRating) {
                // 기존 좋아요 상태 업데이트
                DB::table($ratingTable)
                    ->where('id', $existingRating->id)
                    ->update([
                        'is_like' => $isLike,
                        'updated_at' => now(),
                    ]);
            } else {
                // 새로운 좋아요 생성
                DB::table($ratingTable)->insert([
                    'post_id' => $postId,
                    'user_id' => $userId,
                    'type' => 'like',
                    'is_like' => $isLike,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $result = $isLike ? 'liked' : 'unliked';
        } elseif ($type === 'rating') {
            $rating = $request->input('rating');

            if ($existingRating) {
                // 기존 별점 업데이트
                DB::table($ratingTable)
                    ->where('id', $existingRating->id)
                    ->update([
                        'rating' => $rating,
                        'updated_at' => now(),
                    ]);
            } else {
                // 새로운 별점 생성
                DB::table($ratingTable)->insert([
                    'post_id' => $postId,
                    'user_id' => $userId,
                    'type' => 'rating',
                    'rating' => $rating,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $result = 'rated';
        }

        // 통계 계산
        $stats = $this->calculateRatingStats($ratingTable, $postId);

        return response()->json([
            'success' => true,
            'result' => $result,
            'stats' => $stats,
            'message' => $type === 'like' ?
                ($isLike ? '좋아요를 눌렀습니다.' : '좋아요를 취소했습니다.') :
                "{$rating}점으로 평가했습니다."
        ]);
    }

    /**
     * 평가 테이블 생성
     */
    private function createRatingTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['like', 'rating']);
            $table->boolean('is_like')->nullable();
            $table->tinyInteger('rating')->nullable();

            $table->index(['post_id', 'user_id', 'type']);
            $table->unique(['post_id', 'user_id', 'type']);
        });
    }

    /**
     * 평가 통계 계산
     */
    private function calculateRatingStats($ratingTable, $postId)
    {
        // 좋아요 수 계산
        $likeCount = DB::table($ratingTable)
            ->where('post_id', $postId)
            ->where('type', 'like')
            ->where('is_like', true)
            ->count();

        // 별점 통계 계산
        $ratingStats = DB::table($ratingTable)
            ->where('post_id', $postId)
            ->where('type', 'rating')
            ->whereNotNull('rating')
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        $ratingCount = $ratingStats->count ?? 0;
        $ratingAverage = $ratingCount > 0 ? round($ratingStats->average, 1) : 0;

        return [
            'like_count' => $likeCount,
            'rating_count' => $ratingCount,
            'rating_average' => $ratingAverage,
        ];
    }
}