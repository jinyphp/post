<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;
use Livewire\Livewire;

/**
 * 게시글 상세보기 컨트롤러
 */
class ShowController extends Controller
{
    use BoardPermissions;

    protected $viewPath = 'jiny-post::www.board_post';

    public function __invoke(Request $request, $code, $id)
    {
        // JWT 또는 세션 기반 인증 설정
        $user = $this->setupAuth($request);

        $board = $this->getBoardInfo($code);

        if (!$board) {
            abort(404, '게시판을 찾을 수 없습니다.');
        }

        // 읽기 권한 확인
        if (!$this->hasPermission($board, 'read')) {
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', '로그인이 필요합니다.');
            }
            abort(403, '게시판에 접근할 권한이 없습니다.');
        }

        $table = "site_board_" . $code;

        if (!Schema::hasTable($table)) {
            abort(404, '게시판 테이블이 존재하지 않습니다.');
        }

        $post = DB::table($table)->find($id);

        if (!$post) {
            abort(404, '게시글을 찾을 수 없습니다.');
        }

        // 조회수 증가
        DB::table($table)
            ->where('id', $id)
            ->increment('click');

        // 첨부된 이미지 가져오기
        $images = [];
        $imageTable = $table . "_images";
        if (Schema::hasTable($imageTable)) {
            $images = DB::table($imageTable)
                ->where('post_id', $id)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();
        }

        // 첨부파일 가져오기
        $attachments = [];
        $attachmentTable = $table . "_attachments";
        if (Schema::hasTable($attachmentTable)) {
            $attachments = DB::table($attachmentTable)
                ->where('post_id', $id)
                ->orderBy('created_at')
                ->get();
        }

        // 댓글 가져오기
        $comments = [];
        $commentTable = $table . "_comments";
        if (Schema::hasTable($commentTable)) {
            $comments = DB::table($commentTable)
                ->where('post_id', $id)
                ->orderBy('created_at')
                ->get();
        }

        // 평가 정보 가져오기
        $ratings = [];
        $userRating = null;
        $ratingTable = $table . "_ratings";
        if (Schema::hasTable($ratingTable)) {
            // 전체 평가 통계
            $ratings = $this->getRatingStats($ratingTable, $id);

            // 현재 사용자의 평가 정보
            if (Auth::check()) {
                $userRating = DB::table($ratingTable)
                    ->where('post_id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
            }
        }

        // 부모 게시글 가져오기 (답글인 경우)
        $parentPost = null;
        if ($post->parent_id) {
            $parentPost = DB::table($table)->find($post->parent_id);
        }

        // 답글 가져오기 (계층적 구조) - 이름을 children으로 변경
        $children = $this->getReplies($table, $id);

        // 이전/다음 게시글
        $prevPost = DB::table($table)
            ->where('id', '<', $id)
            ->where('parent_id', null) // 원본 글만
            ->orderBy('id', 'desc')
            ->first();

        $nextPost = DB::table($table)
            ->where('id', '>', $id)
            ->where('parent_id', null) // 원본 글만
            ->orderBy('id', 'asc')
            ->first();

        // 평가 데이터 구조 변경
        $ratingData = [
            'like_count' => $ratings['like_count'] ?? 0,
            'rating_count' => $ratings['rating_count'] ?? 0,
            'rating_average' => $ratings['rating_average'] ?? 0,
            'user_like' => false,
            'user_rating' => null,
        ];

        // 사용자 평가 정보 추가
        if ($userRating) {
            if ($userRating->type === 'like') {
                $ratingData['user_like'] = $userRating->is_like;
            } elseif ($userRating->type === 'rating') {
                $ratingData['user_rating'] = $userRating->rating;
            }
        }

        // 사용자 정보 추가
        $authUserId = Auth::id();
        $authUserEmail = Auth::user()->email ?? '';

        return view("{$this->viewPath}.show", [
            'board' => $board,
            'post' => $post,
            'parentPost' => $parentPost,
            'code' => $code,
            'images' => $images,
            'attachments' => $attachments,
            'comments' => $comments,
            'children' => $children,
            'ratings' => $ratings,
            'ratingData' => $ratingData,
            'userRating' => $userRating,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost,
            'user' => $user,
            'authUserId' => $authUserId,
            'authUserEmail' => $authUserEmail,
            'isAuthenticated' => Auth::check(),
            'canEdit' => $this->hasPostPermission($board, $post, 'edit'),
            'canDelete' => $this->hasPostPermission($board, $post, 'delete'),
            'canComment' => $this->hasPermission($board, 'comment'),
            'canReply' => $this->hasPermission($board, 'create'),
            'canCreate' => $this->hasPermission($board, 'create'),
        ]);
    }

    /**
     * 답글 가져오기 (계층적 구조)
     */
    private function getReplies($table, $parentId, $level = 1, $maxLevel = 5)
    {
        if ($level > $maxLevel) {
            return [];
        }

        $replies = DB::table($table)
            ->where('parent_id', $parentId)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($replies as $reply) {
            $reply->level = $level;
            $reply->replies = $this->getReplies($table, $reply->id, $level + 1, $maxLevel);
        }

        return $replies;
    }

    /**
     * 게시글의 평가 통계 조회
     */
    private function getRatingStats($ratingTable, $postId)
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
