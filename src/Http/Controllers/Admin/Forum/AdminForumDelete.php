<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Admin Forum Delete Controller
 *
 * 포럼 글 삭제를 처리하는 단일 액션 컨트롤러입니다.
 * AJAX 요청과 일반 Form Submit을 구분하여 적절한 응답을 반환합니다.
 *
 * @author Jiny Framework
 * @since 1.0.0
 *
 * Routes:
 * - DELETE /admin/cms/forum/{id} (admin.cms.forum.destroy)
 *
 * Response Types:
 * - AJAX 요청: JSON 응답 (성공: 200, 실패: 404/500)
 * - Form Submit: 리다이렉션 응답 (성공 메시지 또는 오류 메시지 포함)
 */
class AdminForumDelete extends Controller
{
    /**
     * 데이터베이스 테이블명
     *
     * @var string
     */
    protected $table = 'site_forum';

    /**
     * 포럼 글 삭제 처리
     *
     * 요청 타입에 따라 다른 응답 형식을 제공합니다:
     * - AJAX 요청: JSON 형태의 API 응답
     * - Form Submit: 웹 페이지 리다이렉션
     *
     * @param Request $request HTTP 요청 객체
     * @param int|string $id 삭제할 포럼 글의 ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     *
     * @throws \Exception 데이터베이스 오류 등 예외 발생 시
     *
     * @example AJAX 요청 시 응답:
     * {
     *   "success": true,
     *   "message": "포럼 글이 삭제되었습니다.",
     *   "data": {
     *     "id": 123,
     *     "redirect": "/admin/cms/forum"
     *   }
     * }
     */
    public function __invoke(Request $request, $id)
    {
        try {
            /**
             * 삭제할 포럼 글 존재 여부 확인
             *
             * 삭제 전에 해당 글이 존재하는지 확인하여
             * 적절한 오류 메시지를 제공합니다.
             */
            $item = DB::table($this->table)->find($id);

            if (!$item) {
                /**
                 * 요청 타입에 따른 404 오류 응답 분기
                 *
                 * AJAX 요청 감지 조건:
                 * - ajax(): X-Requested-With: XMLHttpRequest 헤더 존재
                 * - expectsJson(): Accept 헤더에 application/json 포함
                 * - wantsJson(): JSON 응답을 선호하는 요청
                 */
                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    // AJAX 요청: JSON 오류 응답 (HTTP 404 Not Found)
                    return response()->json([
                        'success' => false,
                        'message' => '요청하신 포럼 글을 찾을 수 없습니다.',
                        'error' => 'Resource not found'
                    ], 404);
                }

                // Form Submit: 리다이렉션
                return redirect()->route('admin.cms.forum')
                    ->with('error', '요청하신 포럼 글을 찾을 수 없습니다.');
            }

            // 트랜잭션으로 데이터 일관성 보장
            DB::beginTransaction();

            try {
                /**
                 * 다중 이미지 파일 삭제 처리
                 *
                 * site_forum_images 테이블에 저장된 모든 이미지를 삭제합니다.
                 */
                $this->deleteForumImages($id);

                /**
                 * 기본 이미지 파일 삭제 처리
                 *
                 * 포럼 글에 첨부된 메인 이미지가 로컬 저장소에 있는 경우
                 * 파일 시스템에서도 삭제합니다.
                 */
                if (!empty($item->image) && str_starts_with($item->image, '/storage/')) {
                    $imagePath = str_replace('/storage/', '', $item->image);

                    try {
                        if (Storage::disk('public')->exists($imagePath)) {
                            Storage::disk('public')->delete($imagePath);

                            // 이미지 삭제 로그 기록
                            Log::info('Forum main image deleted', [
                                'forum_id' => $id,
                                'image_path' => $item->image,
                                'user_id' => auth()->id(),
                                'ip' => $request->ip()
                            ]);
                        }
                    } catch (\Exception $imageDeleteError) {
                        // 이미지 삭제 실패는 로그만 기록하고 포럼 글 삭제는 계속 진행
                        Log::warning('Failed to delete forum main image', [
                            'forum_id' => $id,
                            'image_path' => $item->image,
                            'error' => $imageDeleteError->getMessage(),
                            'user_id' => auth()->id(),
                            'ip' => $request->ip()
                        ]);
                    }
                }

                /**
                 * 데이터베이스에서 포럼 글 삭제 실행
                 *
                 * 관련된 댓글이나 첨부파일이 있다면
                 * 추후 관련 데이터도 함께 삭제하는 로직을 추가할 수 있습니다.
                 */
                $affected = DB::table($this->table)->where('id', $id)->delete();

                DB::commit();

            } catch (\Exception $transactionError) {
                DB::rollBack();
                throw $transactionError;
            }

            /**
             * 요청 타입에 따른 성공 응답 분기
             */
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                // AJAX 요청: JSON 성공 응답 (HTTP 200 OK)
                return response()->json([
                    'success' => true,
                    'message' => '포럼 글이 삭제되었습니다.',
                    'data' => [
                        'id' => (int) $id,
                        'affected_rows' => $affected,
                        'redirect' => route('admin.cms.forum')
                    ]
                ], 200);
            }

            /**
             * 일반 Form Submit: 리다이렉션 응답
             *
             * 성공 시 포럼 목록 페이지로 이동하며,
             * 세션에 성공 메시지를 저장합니다.
             */
            return redirect()->route('admin.cms.forum')
                ->with('success', '포럼 글이 삭제되었습니다.');

        } catch (\Exception $e) {
            /**
             * 예외 발생 시 로그 기록
             *
             * 삭제 과정에서 발생한 오류를 로그에 기록하여
             * 추후 디버깅 및 모니터링에 활용합니다.
             */
            Log::error('Forum delete error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            /**
             * 요청 타입에 따른 오류 응답 분기
             */
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                // AJAX 요청: JSON 오류 응답 (HTTP 500 Internal Server Error)
                return response()->json([
                    'success' => false,
                    'message' => '포럼 글 삭제 중 오류가 발생했습니다.',
                    // 개발 환경에서만 상세 오류 메시지 노출
                    'error' => app()->environment('local') ? $e->getMessage() : '서버 오류'
                ], 500);
            }

            /**
             * Form Submit: 리다이렉션 응답
             *
             * 오류 발생 시 포럼 목록 페이지로 이동하며,
             * 세션에 오류 메시지를 저장합니다.
             */
            return redirect()->route('admin.cms.forum')
                ->with('error', '포럼 글 삭제 중 오류가 발생했습니다.');
        }
    }

    /**
     * 포럼과 관련된 모든 이미지 삭제
     *
     * @param int $forumId 포럼 글 ID
     * @return void
     */
    protected function deleteForumImages($forumId)
    {
        try {
            // 포럼과 연관된 모든 이미지 조회
            $images = DB::table('site_forum_images')
                ->where('forum_id', $forumId)
                ->get();

            if ($images->isEmpty()) {
                Log::info("No images found for forum ID: {$forumId}");
                return;
            }

            Log::info("Deleting {$images->count()} images for forum ID: {$forumId}");

            foreach ($images as $image) {
                try {
                    // 물리적 파일 삭제
                    if ($image->path && Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                        Log::info("Deleted image file: {$image->path}");
                    } else {
                        Log::warning("Image file not found or already deleted: " . ($image->path ?? 'no path'));
                    }

                    // 데이터베이스에서 이미지 레코드 삭제
                    DB::table('site_forum_images')->where('id', $image->id)->delete();
                    Log::info("Deleted image record from database: ID {$image->id}");

                } catch (\Exception $e) {
                    Log::error("Failed to delete individual image: " . $e->getMessage(), [
                        'image_id' => $image->id,
                        'path' => $image->path ?? 'no path',
                        'forum_id' => $forumId,
                    ]);
                    // Continue with other images even if one fails
                }
            }

            Log::info("Completed image deletion for forum ID: {$forumId}");

        } catch (\Exception $e) {
            Log::error("Failed to delete forum images: " . $e->getMessage(), [
                'forum_id' => $forumId,
            ]);
            throw $e; // Re-throw to trigger rollback
        }
    }
}