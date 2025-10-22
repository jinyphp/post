<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Admin Forum Store Controller
 *
 * 포럼 글 저장을 처리하는 단일 액션 컨트롤러입니다.
 * AJAX 요청과 일반 Form Submit을 구분하여 적절한 응답을 반환합니다.
 *
 * @author Jiny Framework
 * @since 1.0.0
 *
 * Routes:
 * - POST /admin/cms/forum (admin.cms.forum.store)
 *
 * Response Types:
 * - AJAX 요청: JSON 응답 (성공: 201, 실패: 422/500)
 * - Form Submit: 리다이렉션 응답 (성공 메시지 또는 오류 메시지 포함)
 */
class AdminForumStore extends Controller
{
    /**
     * 데이터베이스 테이블명
     *
     * @var string
     */
    protected $table = 'site_forum';

    /**
     * 포럼 글 저장 처리
     *
     * 요청 타입에 따라 다른 응답 형식을 제공합니다:
     * - AJAX 요청: JSON 형태의 API 응답
     * - Form Submit: 웹 페이지 리다이렉션
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException 유효성 검사 실패 시
     * @throws \Exception 데이터베이스 오류 등 예외 발생 시
     *
     * @example AJAX 요청 시 응답:
     * {
     *   "success": true,
     *   "message": "포럼 글이 생성되었습니다.",
     *   "data": {
     *     "id": 123,
     *     "title": "글 제목",
     *     "created_at": "2024-10-22T15:30:00.000000Z"
     *   }
     * }
     */
    public function __invoke(Request $request)
    {
        try {
            /**
             * 입력 데이터 유효성 검사
             *
             * 검증 규칙:
             * - title: 필수, 문자열, 최대 255자
             * - content: 필수, 문자열
             * - name: 선택, 문자열, 최대 100자 (작성자명)
             * - email: 선택, 이메일 형식, 최대 100자
             * - categories: 선택, 문자열, 최대 50자 (카테고리 슬러그)
             * - images.*: 다중 이미지 파일 (jpg, jpeg, png, gif, webp), 최대 5MB 각각
             */
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'name' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:100',
                'categories' => 'nullable|string|max:50',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB per image
            ]);

            // CSRF 토큰과 HTTP 메서드 필드 제외하고 데이터 준비
            $data = $request->except(['_token', '_method', 'images']);

            // 타임스탬프 설정
            $data['created_at'] = now();
            $data['updated_at'] = now();

            /**
             * 작성자 정보 자동 설정
             *
             * 입력된 작성자 정보가 없는 경우,
             * 현재 인증된 사용자의 정보를 사용합니다.
             */
            if (auth()->check()) {
                $data['name'] = $data['name'] ?? auth()->user()->name ?? '';
                $data['email'] = $data['email'] ?? auth()->user()->email ?? '';
            }

            /**
             * 기본값 설정
             *
             * - click: 조회수 (기본값: 0)
             * - like: 좋아요 수 (기본값: 0)
             * - rank: 순위 (기본값: 0)
             */
            $data['click'] = $data['click'] ?? 0;
            $data['like'] = $data['like'] ?? 0;
            $data['rank'] = $data['rank'] ?? 0;

            // 데이터베이스에 삽입하고 생성된 ID 반환
            $id = DB::table($this->table)->insertGetId($data);

            /**
             * 다중 이미지 파일 업로드 처리
             *
             * 각 이미지를 개별적으로 저장하고 site_forum_images 테이블에 메타데이터 저장
             */
            \Log::info('Admin image upload debug info:', [
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'hasFile_images' => $request->hasFile('images'),
                'files' => $request->file('images') ? count($request->file('images')) : 0,
                'request_files' => array_keys($request->allFiles()),
                'all_input' => $request->except(['_token', 'password']),
                'images_input' => $request->input('images'),
                'ajax_check' => [
                    'ajax' => $request->ajax(),
                    'expectsJson' => $request->expectsJson(),
                    'wantsJson' => $request->wantsJson(),
                    'header_requested_with' => $request->header('X-Requested-With'),
                    'accept_header' => $request->header('Accept')
                ]
            ]);

            if ($request->hasFile('images')) {
                $this->saveForumImages($id, $request->file('images'));
            } else {
                \Log::info('Admin image upload skipped - no images found');
            }

            /**
             * 요청 타입에 따른 응답 분기
             *
             * AJAX 요청 감지 조건:
             * - ajax(): X-Requested-With: XMLHttpRequest 헤더 존재
             * - expectsJson(): Accept 헤더에 application/json 포함
             * - wantsJson(): JSON 응답을 선호하는 요청
             */
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                // AJAX 요청: JSON 응답 반환 (HTTP 201 Created)
                return response()->json([
                    'success' => true,
                    'message' => '포럼 글이 생성되었습니다.',
                    'data' => [
                        'id' => $id,
                        'title' => $data['title'],
                        'created_at' => $data['created_at']
                    ]
                ], 201);
            }

            /**
             * 일반 Form Submit: 리다이렉션 응답
             *
             * 성공 시 포럼 목록 페이지로 이동하며,
             * 세션에 성공 메시지를 저장합니다.
             */
            return redirect()->route('admin.cms.forum')
                ->with('success', '포럼 글이 생성되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            /**
             * 유효성 검사 실패 처리
             */

            // AJAX 요청: JSON 오류 응답 (HTTP 422 Unprocessable Entity)
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '유효성 검사 실패',
                    'errors' => $e->errors()
                ], 422);
            }

            // Form Submit: Laravel 기본 유효성 검사 처리 위임
            // (자동으로 이전 페이지로 리다이렉션되며 오류 메시지와 입력값이 유지됨)
            throw $e;

        } catch (\Exception $e) {
            /**
             * 일반 예외 처리 (데이터베이스 오류, 서버 오류 등)
             */

            // AJAX 요청: JSON 오류 응답 (HTTP 500 Internal Server Error)
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '포럼 글 생성 중 오류가 발생했습니다.',
                    // 개발 환경에서만 상세 오류 메시지 노출
                    'error' => app()->environment('local') ? $e->getMessage() : '서버 오류'
                ], 500);
            }

            /**
             * Form Submit: 이전 페이지로 리다이렉션
             *
             * - withInput(): 입력했던 데이터 유지
             * - with('error'): 오류 메시지 세션에 저장
             */
            return redirect()->back()
                ->withInput()
                ->with('error', '포럼 글 생성 중 오류가 발생했습니다.');
        }
    }

    /**
     * 포럼 이미지들을 저장하는 메소드
     *
     * @param int $forumId 포럼 글 ID
     * @param array $images 업로드된 이미지 파일 배열
     * @return void
     */
    protected function saveForumImages($forumId, $images)
    {
        foreach ($images as $index => $image) {
            try {
                // 계층화된 경로 생성: forum/YYYY/MM/DD/HH/
                $now = now();
                $hierarchicalPath = sprintf(
                    'forum/%04d/%02d/%02d/%02d',
                    $now->year,
                    $now->month,
                    $now->day,
                    $now->hour
                );

                // UUID 기반 파일명 생성
                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();

                // 파일 저장 (public 디스크 사용)
                $imagePath = $image->storeAs($hierarchicalPath, $fileName, 'public');

                if ($imagePath) {
                    // site_forum_images 테이블에 이미지 정보 저장
                    DB::table('site_forum_images')->insert([
                        'forum_id' => $forumId,
                        'filename' => $fileName,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $imagePath,
                        'url' => asset('storage/' . $imagePath),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'sort_order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // 개별 이미지 저장 실패 시 로그 기록 (전체 프로세스는 계속)
                \Log::error('Admin forum image save failed:', [
                    'forum_id' => $forumId,
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_name' => $image->getClientOriginalName(),
                ]);
            }
        }
    }
}