<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Jiny\Post\Services\ForumPermissionManager;

/**
 * 포럼 글 수정 처리 컨트롤러
 */
class ForumUpdate extends Controller
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

        // 수정 권한 확인
        if (!$this->canEditForum($forum, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '이 글을 수정할 권한이 없습니다.',
                ], 403);
            } else {
                return redirect()->route('forum.show', $id)
                    ->with('error', '이 글을 수정할 권한이 없습니다.');
            }
        }

        // 포럼 설정 가져오기
        $config = $this->permissionManager->getConfigManager();
        $maxFileSize = $config->getSetting('max_file_size_mb', 5) * 1024; // KB 단위로 변환

        // 입력값 검증
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'categories' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:' . $maxFileSize,
            'remove_image_ids' => 'nullable|string',
        ], [
            'title.required' => '제목을 입력해주세요.',
            'title.max' => '제목은 255자를 초과할 수 없습니다.',
            'content.required' => '내용을 입력해주세요.',
            'categories.max' => '카테고리는 255자를 초과할 수 없습니다.',
            'tags.max' => '태그는 255자를 초과할 수 없습니다.',
            'images.*.image' => '이미지 파일만 업로드 가능합니다.',
            'images.*.mimes' => '지원되는 이미지 형식: jpeg, png, jpg, gif, webp',
            'images.*.max' => '이미지 파일 크기는 ' . $config->getSetting('max_file_size_mb', 5) . 'MB를 초과할 수 없습니다.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '입력값이 올바르지 않습니다.',
                    'errors' => $validator->errors(),
                ], 422);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // 데이터 준비
            $data = [
                'title' => $request->title,
                'content' => $request->content,
                'updated_at' => now(),
            ];

            // 선택적 컬럼 추가
            if ($request->filled('categories')) {
                $data['categories'] = $request->categories;
            }
            if ($request->filled('tags')) {
                $data['tags'] = $request->tags;
            }

            // 기존 이미지 삭제 처리
            $removedImageIds = [];
            if ($request->filled('remove_image_ids')) {
                $removedImageIds = array_filter(explode(',', $request->remove_image_ids));
                if (!empty($removedImageIds)) {
                    $this->removeForumImages($removedImageIds);
                }
            }

            // 새 이미지 업로드 처리
            if ($request->hasFile('images')) {
                $this->saveForumImages($id, $request->file('images'));
            }

            // 메인 데이터베이스 업데이트
            DB::table($table)->where('id', $id)->update($data);

            DB::commit();

            // 성공 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '글이 성공적으로 수정되었습니다.',
                    'forum_id' => $id,
                ]);
            } else {
                return redirect()->route('forum.show', $id)
                    ->with('success', '글이 성공적으로 수정되었습니다.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Forum update error: ' . $e->getMessage(), [
                'forum_id' => $id,
                'user_id' => $user->id ?? null,
                'request_data' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '글 수정 중 오류가 발생했습니다.',
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', '글 수정 중 오류가 발생했습니다.')
                    ->withInput();
            }
        }
    }

    /**
     * 포럼 글 수정 권한 확인
     */
    protected function canEditForum($forum, $user)
    {
        // 관리자는 모든 글 수정 가능
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
     * 포럼 이미지 삭제 처리
     */
    protected function removeForumImages($imageIds)
    {
        // 삭제할 이미지 정보 조회
        $images = DB::table('site_forum_images')
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            try {
                // 물리적 파일 삭제
                if ($image->path && Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                    \Log::info('Image file deleted: ' . $image->path);
                }

                // 데이터베이스에서 삭제
                DB::table('site_forum_images')->where('id', $image->id)->delete();

            } catch (\Exception $e) {
                \Log::error('Failed to delete image: ' . $e->getMessage(), [
                    'image_id' => $image->id,
                    'path' => $image->path ?? 'no path',
                ]);
            }
        }
    }

    /**
     * 새 포럼 이미지 저장
     */
    protected function saveForumImages($forumId, $images)
    {
        // 현재 이미지 개수 확인
        $currentImageCount = DB::table('site_forum_images')
            ->where('forum_id', $forumId)
            ->count();

        // 최대 정렬 순서 확인
        $maxSortOrder = DB::table('site_forum_images')
            ->where('forum_id', $forumId)
            ->max('sort_order') ?? 0;

        \Log::info('Site Forum image upload debug:', [
            'forum_id' => $forumId,
            'image_count' => count($images),
            'current_db_count' => $currentImageCount,
            'max_sort_order' => $maxSortOrder
        ]);

        foreach ($images as $index => $image) {
            try {
                // 계층화된 경로 생성: forum/{year}/{month}/{day}/{forumId}/
                $now = now();
                $hierarchicalPath = "forum/{$now->year}/{$now->month}/{$now->day}/{$forumId}";

                // UUID 기반 파일명 생성 (Admin용과 동일하게)
                $filename = \Illuminate\Support\Str::uuid() . '.' . $image->getClientOriginalExtension();

                // 파일 저장 (public 디스크 사용)
                $imagePath = $image->storeAs($hierarchicalPath, $filename, 'public');

                if ($imagePath) {
                    // site_forum_images 테이블에 이미지 정보 저장
                    $inserted = DB::table('site_forum_images')->insert([
                        'forum_id' => $forumId,
                        'filename' => $filename,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $imagePath,
                        'url' => asset('storage/' . $imagePath),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'sort_order' => $maxSortOrder + $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    \Log::info('Site Forum image saved successfully:', [
                        'forum_id' => $forumId,
                        'index' => $index,
                        'filename' => $filename,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $imagePath,
                        'size' => $image->getSize(),
                        'inserted' => $inserted,
                        'sort_order' => $maxSortOrder + $index + 1,
                    ]);
                } else {
                    \Log::error('Site Forum image path is empty:', [
                        'forum_id' => $forumId,
                        'index' => $index,
                        'original_name' => $image->getClientOriginalName(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Site Forum image save failed:', [
                    'forum_id' => $forumId,
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_name' => $image->getClientOriginalName(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e; // Re-throw to trigger rollback
            }
        }

        // 저장 완료 후 최종 확인
        $finalImageCount = DB::table('site_forum_images')
            ->where('forum_id', $forumId)
            ->count();

        \Log::info('Site Forum image upload completed:', [
            'forum_id' => $forumId,
            'initial_count' => $currentImageCount,
            'uploaded_count' => count($images),
            'final_count' => $finalImageCount,
            'expected_count' => $currentImageCount + count($images)
        ]);
    }

}