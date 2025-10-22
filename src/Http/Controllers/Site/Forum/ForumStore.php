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
 * 포럼 글 저장 컨트롤러
 */
class ForumStore extends Controller
{
    protected $permissionManager;

    public function __construct(ForumPermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function __invoke(Request $request)
    {
        $table = 'site_forum';

        if (!Schema::hasTable($table)) {
            abort(404, '포럼 테이블이 존재하지 않습니다.');
        }

        // 글작성 권한 확인
        $permission = $this->permissionManager->canWrite();
        if (!$permission['allowed']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $permission['message'],
                    'reason' => $permission['reason'],
                ], 403);
            } else {
                return redirect()->route('forum.index')
                    ->with('error', $permission['message']);
            }
        }

        // 포럼 설정 가져오기
        $config = $this->permissionManager->getConfigManager();
        $enableFileUpload = $config->getSetting('enable_file_upload', true);
        $maxFileSize = $config->getSetting('max_file_size_mb', 5) * 1024; // KB로 변환
        $maxImagesPerPost = $config->getSetting('max_images_per_post', 10);
        $enableTags = $config->getSetting('enable_tags', true);
        $maxTagsPerPost = $config->getSetting('max_tags_per_post', 5);

        // 동적 검증 규칙 생성
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'categories' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:100', // 게스트용
            'email' => 'nullable|email|max:255', // 게스트용
        ];

        $messages = [
            'title.required' => '제목을 입력해주세요.',
            'title.max' => '제목은 255자를 초과할 수 없습니다.',
            'content.required' => '내용을 입력해주세요.',
            'categories.max' => '카테고리는 255자를 초과할 수 없습니다.',
            'name.max' => '이름은 100자를 초과할 수 없습니다.',
            'email.email' => '올바른 이메일 주소를 입력해주세요.',
            'email.max' => '이메일은 255자를 초과할 수 없습니다.',
        ];

        // 태그 기능이 활성화된 경우
        if ($enableTags) {
            $rules['tags'] = 'nullable|string|max:255';
            $messages['tags.max'] = '태그는 255자를 초과할 수 없습니다.';
        }

        // 파일 업로드가 활성화된 경우
        if ($enableFileUpload) {
            $rules['images.*'] = "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxFileSize}";
            $rules['images'] = "array|max:{$maxImagesPerPost}";
            $messages['images.*.image'] = '이미지 파일만 업로드 가능합니다.';
            $messages['images.*.mimes'] = '지원되는 이미지 형식: jpeg, png, jpg, gif, webp';
            $messages['images.*.max'] = "이미지 파일 크기는 {$config->getSetting('max_file_size_mb', 5)}MB를 초과할 수 없습니다.";
            $messages['images.max'] = "최대 {$maxImagesPerPost}개의 이미지만 업로드할 수 있습니다.";
        }

        // 입력값 검증
        $validator = Validator::make($request->all(), $rules, $messages);

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
            // 현재 사용자 정보 가져오기
            $user = $this->permissionManager->getCurrentUser();

            // 기본 데이터 준비 (실제 존재하는 컬럼만)
            $data = [
                'title' => $request->title,
                'content' => $request->content,
                'click' => 0,
                'like' => 0,
                'rank' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 선택적 컬럼 추가 (테이블에 존재하는 경우만)
            if ($request->filled('categories')) {
                $data['categories'] = $request->categories;
            }

            // 태그 기능이 활성화된 경우만 저장
            if ($enableTags && $request->filled('tags')) {
                // 태그 개수 제한 적용
                $tags = array_map('trim', explode(',', $request->tags));
                if (count($tags) > $maxTagsPerPost) {
                    $tags = array_slice($tags, 0, $maxTagsPerPost);
                }
                $data['tags'] = implode(',', $tags);
            }

            // 사용자 정보 추가
            if ($user) {
                $data['user_id'] = $user->id ?? null;
                if (isset($user->uuid)) {
                    $data['user_uuid'] = $user->uuid;
                }
                $data['name'] = $user->name ?? 'Anonymous';
                if (isset($user->email)) {
                    $data['email'] = $user->email;
                }
            } else {
                // 게스트 사용자인 경우
                $data['name'] = $request->input('name', 'Anonymous');
                if ($request->filled('email')) {
                    $data['email'] = $request->input('email');
                }
            }

            // IP 주소 저장 (컬럼이 존재하는 경우만)
            if (Schema::hasColumn($table, 'ip_address')) {
                $data['ip_address'] = $request->ip();
            }

            // 이미지 업로드 처리 (설정에서 활성화된 경우만)
            $uploadedImages = [];

            \Log::info('Image upload check:', [
                'enableFileUpload' => $enableFileUpload,
                'hasFile_images' => $request->hasFile('images'),
                'files' => $request->file('images') ? count($request->file('images')) : 0,
                'request_files' => array_keys($request->allFiles())
            ]);

            if ($enableFileUpload && $request->hasFile('images')) {
                $uploadedImages = $this->handleImageUploads($request->file('images'), $maxImagesPerPost);
                \Log::info('Images processed:', [
                    'uploaded_count' => count($uploadedImages),
                    'max_allowed' => $maxImagesPerPost
                ]);

                if (!empty($uploadedImages)) {
                    // 첫 번째 이미지를 메인 이미지로 설정 (기존 image 컬럼 활용)
                    $data['image'] = $uploadedImages[0]['url'] ?? null;
                    \Log::info('Main image set:', ['main_image' => $data['image']]);
                }
            } else {
                \Log::info('Image upload skipped:', [
                    'enableFileUpload' => $enableFileUpload,
                    'hasFile' => $request->hasFile('images')
                ]);
            }

            \Log::info('Forum store data:', $data);

            // 데이터베이스에 저장
            $forumId = DB::table($table)->insertGetId($data);

            // 다중 이미지를 별도 테이블에 저장
            if (!empty($uploadedImages)) {
                $this->saveForumImages($forumId, $uploadedImages);
            }

            \Log::info('Forum created successfully:', ['forum_id' => $forumId]);

            // 성공 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '글이 성공적으로 작성되었습니다.',
                    'forum_id' => $forumId,
                ]);
            } else {
                return redirect()->route('forum.show', $forumId)
                    ->with('success', '글이 성공적으로 작성되었습니다.');
            }

        } catch (\Exception $e) {
            \Log::error('Forum store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => isset($user) ? ($user->id ?? null) : null,
                'request_data' => $request->all(),
                'table' => $table,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '글 작성 중 오류가 발생했습니다: ' . $e->getMessage(),
                    'debug' => config('app.debug') ? $e->getTraceAsString() : null,
                ], 500);
            } else {
                $errorMessage = config('app.debug')
                    ? '글 작성 중 오류가 발생했습니다: ' . $e->getMessage()
                    : '글 작성 중 오류가 발생했습니다.';

                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }
        }
    }

    /**
     * 이미지 업로드 처리
     */
    protected function handleImageUploads($images, $maxCount = 10)
    {
        $uploadedImages = [];
        $count = 0;

        foreach ($images as $index => $image) {
            if ($count >= $maxCount) {
                break; // 설정된 최대 개수 초과 시 중단
            }
            try {
                // 파일명 생성 (중복 방지)
                $filename = time() . '_' . $index . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // 저장 경로
                $path = $image->storeAs('forum', $filename, 'public');

                if ($path) {
                    $uploadedImages[] = [
                        'filename' => $filename,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'uploaded_at' => now()->toISOString(),
                    ];

                    \Log::info('Image uploaded successfully:', [
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $image->getSize(),
                    ]);
                    $count++; // 성공한 경우에만 카운트 증가
                }
            } catch (\Exception $e) {
                \Log::error('Image upload failed:', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        return $uploadedImages;
    }

    /**
     * 포럼 이미지들을 별도 테이블에 저장
     */
    protected function saveForumImages($forumId, $uploadedImages)
    {
        $imageData = [];

        foreach ($uploadedImages as $index => $image) {
            $imageData[] = [
                'forum_id' => $forumId,
                'filename' => $image['filename'],
                'original_name' => $image['original_name'],
                'path' => $image['path'],
                'url' => $image['url'],
                'size' => $image['size'],
                'mime_type' => $image['mime_type'],
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($imageData)) {
            DB::table('site_forum_images')->insert($imageData);
            \Log::info('Forum images saved:', [
                'forum_id' => $forumId,
                'image_count' => count($imageData)
            ]);
        }
    }
}