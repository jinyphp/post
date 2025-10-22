<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Jiny\Post\Services\ForumPermissionManager;

/**
 * 포럼 글 삭제 컨트롤러
 */
class ForumDelete extends Controller
{
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

        // 포럼 글 조회
        $forum = DB::table($table)->where('id', $id)->first();

        if (!$forum) {
            abort(404, '존재하지 않는 글입니다.');
        }

        // 현재 사용자 정보 가져오기
        $user = $this->permissionManager->getCurrentUser();

        // 삭제 권한 확인
        if (!$this->canDeleteForum($forum, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '이 글을 삭제할 권한이 없습니다.',
                ], 403);
            } else {
                return redirect()->route('forum.show', $id)
                    ->with('error', '이 글을 삭제할 권한이 없습니다.');
            }
        }

        try {
            DB::beginTransaction();

            // 관련 이미지들 삭제 처리
            $this->deleteForumImages($id);

            // 소프트 삭제 또는 실제 삭제 처리
            if (Schema::hasColumn($table, 'deleted_at')) {
                // 소프트 삭제
                DB::table($table)->where('id', $id)->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // 실제 삭제
                DB::table($table)->where('id', $id)->delete();
            }

            DB::commit();

            // 성공 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '글이 성공적으로 삭제되었습니다.',
                ]);
            } else {
                return redirect()->route('forum.index')
                    ->with('success', '글이 성공적으로 삭제되었습니다.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Forum delete error: ' . $e->getMessage(), [
                'forum_id' => $id,
                'user_id' => $user->id ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '글 삭제 중 오류가 발생했습니다.',
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', '글 삭제 중 오류가 발생했습니다.');
            }
        }
    }

    /**
     * 포럼 글 삭제 권한 확인
     */
    protected function canDeleteForum($forum, $user)
    {
        // 관리자는 모든 글 삭제 가능
        if ($user && $this->isAdmin($user)) {
            return true;
        }

        // 로그인하지 않은 경우
        if (!$user) {
            return false;
        }

        // 본인이 작성한 글인지 확인
        if (isset($forum->user_id) && $user->id == $forum->user_id) {
            return true;
        }

        // UUID로 확인 (샤딩 사용자인 경우)
        if (isset($forum->user_uuid) && isset($user->uuid) && $user->uuid == $forum->user_uuid) {
            return true;
        }

        return false;
    }

    /**
     * 관리자 권한 확인
     */
    protected function isAdmin($user)
    {
        // isAdmin 플래그 확인
        if (isset($user->isAdmin) && $user->isAdmin) {
            return true;
        }

        // 관리자 역할 확인
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super-admin');
        }

        // role 필드 직접 확인
        if (isset($user->role) && in_array($user->role, ['admin', 'super-admin'])) {
            return true;
        }

        // utype 필드 확인 (jiny/admin 패키지 방식)
        if (isset($user->utype) && $user->utype === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * 포럼과 관련된 모든 이미지 삭제
     */
    protected function deleteForumImages($forumId)
    {
        try {
            // 포럼과 연관된 모든 이미지 조회
            $images = DB::table('site_forum_images')
                ->where('forum_id', $forumId)
                ->get();

            if ($images->isEmpty()) {
                \Log::info("No images found for forum ID: {$forumId}");
                return;
            }

            \Log::info("Deleting {$images->count()} images for forum ID: {$forumId}");

            foreach ($images as $image) {
                try {
                    // 물리적 파일 삭제
                    if ($image->path && Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                        \Log::info("Deleted image file: {$image->path}");
                    } else {
                        \Log::warning("Image file not found or already deleted: " . ($image->path ?? 'no path'));
                    }

                    // 데이터베이스에서 이미지 레코드 삭제
                    DB::table('site_forum_images')->where('id', $image->id)->delete();
                    \Log::info("Deleted image record from database: ID {$image->id}");

                } catch (\Exception $e) {
                    \Log::error("Failed to delete individual image: " . $e->getMessage(), [
                        'image_id' => $image->id,
                        'path' => $image->path ?? 'no path',
                        'forum_id' => $forumId,
                    ]);
                    // Continue with other images even if one fails
                }
            }

            \Log::info("Completed image deletion for forum ID: {$forumId}");

        } catch (\Exception $e) {
            \Log::error("Failed to delete forum images: " . $e->getMessage(), [
                'forum_id' => $forumId,
            ]);
            throw $e; // Re-throw to trigger rollback
        }
    }
}