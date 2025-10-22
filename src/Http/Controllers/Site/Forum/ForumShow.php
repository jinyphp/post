<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Jiny\Post\Services\ForumPermissionManager;

/**
 * 포럼 글 상세보기 단일 액션 컨트롤러
 */
class ForumShow extends Controller
{
    protected $viewPath = 'jiny-post::www.forum';
    protected $permissionManager;

    public function __construct(ForumPermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function __invoke(Request $request, $id)
    {
        $table = 'site_forum';

        if (!Schema::hasTable($table)) {
            abort(404, '포럼 테이블이 존재하지 않습니다.');
        }

        // 게시글 조회
        $post = DB::table($table)->find($id);

        if (!$post) {
            abort(404, '포럼 글을 찾을 수 없습니다.');
        }

        // 조회수 증가
        $this->incrementViews($id);

        // 관련 글 조회 (같은 카테고리의 다른 글들)
        $relatedPosts = collect();
        if ($post->categories) {
            $categories = array_map('trim', explode(',', $post->categories));

            $relatedPosts = DB::table($table)
                ->where('id', '!=', $id)
                ->where(function($query) use ($categories) {
                    foreach ($categories as $category) {
                        $query->orWhere('categories', 'like', "%{$category}%");
                    }
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // 이전글/다음글 조회
        $prevPost = DB::table($table)
            ->where('id', '<', $id)
            ->orderBy('id', 'desc')
            ->first();

        $nextPost = DB::table($table)
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->first();

        // 태그 배열로 변환
        $tags = [];
        if ($post->tags) {
            $tags = array_map('trim', explode(',', $post->tags));
        }

        // 카테고리 배열로 변환
        $categories = [];
        if ($post->categories) {
            $categories = array_map('trim', explode(',', $post->categories));
        }

        // 포럼 설정 정보
        $config = $this->permissionManager->getConfigManager();
        $enableVoting = $config->getSetting('enable_voting', false);

        // 사용자의 좋아요 상태 확인 (투표 기능이 활성화된 경우만)
        $userLiked = false;
        if ($enableVoting) {
            $userLiked = $this->checkUserLiked($id, $request->ip());
        }

        // 포럼 이미지들 조회
        $forumImages = DB::table('site_forum_images')
            ->where('forum_id', $id)
            ->orderBy('sort_order')
            ->get();

        // 현재 사용자 정보 가져오기
        $currentUser = $this->permissionManager->getCurrentUser();

        // 포럼 설정 정보
        $forumSettings = [
            'enable_voting' => $enableVoting,
            'enable_tags' => $config->getSetting('enable_tags', true),
        ];

        return view("{$this->viewPath}.show", [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost,
            'tags' => $tags,
            'categories' => $categories,
            'userLiked' => $userLiked,
            'currentUser' => $currentUser,
            'forumSettings' => $forumSettings,
            'forumImages' => $forumImages,
        ]);
    }

    /**
     * 조회수 증가
     */
    private function incrementViews($postId)
    {
        DB::table('site_forum')
            ->where('id', $postId)
            ->increment('click');
    }

    /**
     * 사용자의 좋아요 상태 확인
     */
    private function checkUserLiked($postId, $ipAddress)
    {
        $likeTable = 'site_forum_likes';

        if (!Schema::hasTable($likeTable)) {
            return false;
        }

        $user = Auth::user();
        $userId = $user ? $user->id : null;

        $query = DB::table($likeTable)
            ->where('post_id', $postId)
            ->where('ip_address', $ipAddress);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id');
        }

        return $query->exists();
    }
}