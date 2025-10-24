<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;
use Jiny\Auth\Services\JwtService;
use Jiny\Post\Services\FileSecurityValidator;

/**
 * 게시글 저장 컨트롤러
 */
class StoreController extends Controller
{
    use BoardPermissions;

    public function __invoke(Request $request, $code)
    {
        // JWT 또는 세션 기반 인증 설정 (맨 처음에 처리)
        $user = $this->setupAuth($request);

        $board = $this->getBoardInfo($code);

        if (!$board) {
            abort(404, '게시판을 찾을 수 없습니다.');
        }

        // 글쓰기 권한 확인
        if (!$this->hasPermission($board, 'create')) {
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', '로그인이 필요합니다.');
            }
            return redirect()->route('board.index', $code)
                ->with('error', '글쓰기 권한이 없습니다.');
        }

        // 기본 validation
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];

        // 파일 업로드 validation 추가
        if ($board->allow_image_upload && $request->hasFile('images')) {
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB
        }

        if ($board->allow_file_upload && $request->hasFile('attachments')) {
            $rules['attachments.*'] = 'file|max:5120'; // 5MB
        }

        $request->validate($rules);

        $table = "site_board_" . $code;

        if (!Schema::hasTable($table)) {
            abort(404, '게시판 테이블이 존재하지 않습니다.');
        }

        // JWT 인증 디버깅
        \Log::info('BoardPost StoreController Debug', [
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'user' => $user,
            'jwt_token' => $request->bearerToken(),
            'access_token_cookie' => $request->cookie('access_token'),
            'hidden_jwt_token' => $request->input('_jwt_token') ? substr($request->input('_jwt_token'), 0, 50) . '...' : null,
            'all_cookies' => array_keys($request->cookies->all()),
            'session_id' => session()->getId(),
            'guards' => array_keys(config('auth.guards', [])),
        ]);

        $data = $request->only(['title', 'content']);
        $data['code'] = $code;

        // user_id 컬럼이 있는 경우에만 추가
        if (Schema::hasColumn($table, 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        // UUID 컬럼이 있는 경우 UUID 생성
        if (Schema::hasColumn($table, 'uuid')) {
            $data['uuid'] = (string) Str::uuid();
        }

        // 사용자 UUID 처리 (사용자가 로그인한 경우)
        if (Schema::hasColumn($table, 'user_uuid') && $user) {
            // 사용자 테이블에서 UUID를 가져오거나, 없으면 user_id 기반으로 생성
            $data['user_uuid'] = $user->uuid ?? $this->generateUserUuid($user->id);
        }

        // 샤드 ID 처리
        if (Schema::hasColumn($table, 'shard_id')) {
            $data['shard_id'] = $this->getShardId($code, $user);
        }

        $data['name'] = $user ? $user->name : '익명';
        $data['email'] = $user ? $user->email : '';
        $data['click'] = 0;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // parent_id가 있으면 하위 글, level 계산
        if ($request->has('parent_id') && $request->parent_id) {
            $parent = DB::table($table)->find($request->parent_id);
            if ($parent) {
                $data['parent_id'] = $request->parent_id;
                $data['level'] = ($parent->level ?? 0) + 1;
            } else {
                $data['level'] = 0;
                $data['parent_id'] = null;
            }
        } else {
            $data['level'] = 0;
            $data['parent_id'] = null;
        }

        // 게시글 저장하고 ID 받기
        $postId = DB::table($table)->insertGetId($data);

        // 파일 업로드 처리
        \Log::info('BoardPost StoreController - Detailed file upload debug', [
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'hasFile_images' => $request->hasFile('images'),
            'hasFile_attachments' => $request->hasFile('attachments'),
            'files_count' => $request->file() ? count($request->file()) : 0,
            'request_files' => array_keys($request->allFiles()),
            'all_input' => $request->except(['_token', 'password']),
            'images_input' => $request->input('images'),
            'attachments_input' => $request->input('attachments'),
            'board_allow_image' => $board->allow_image_upload,
            'board_allow_file' => $board->allow_file_upload,
            'enctype_check' => $request->header('Content-Type'),
        ]);

        $uploadResults = $this->handleFileUploads($request, $board, $code, $postId);

        \Log::info('BoardPost StoreController - After file upload', [
            'uploaded_count' => count($uploadResults['uploaded'] ?? []),
            'failed_count' => count($uploadResults['failed'] ?? [])
        ]);

        // 성공 메시지 구성
        $message = '게시글이 등록되었습니다.';
        if (!empty($uploadResults['uploaded'])) {
            $message .= ' ' . count($uploadResults['uploaded']) . '개의 파일이 업로드되었습니다.';
        }
        if (!empty($uploadResults['failed'])) {
            $message .= ' ' . count($uploadResults['failed']) . '개의 파일이 업로드에 실패했습니다.';
        }

        return redirect()->route('board.index', $code)
            ->with('success', $message);
    }

    /**
     * 사용자 UUID 생성
     */
    private function generateUserUuid($userId)
    {
        // 사용자 ID 기반으로 일관된 UUID 생성
        return (string) Str::uuid();
    }

    /**
     * 파일 업로드 처리
     */
    private function handleFileUploads(Request $request, $board, $code, $postId)
    {
        $results = ['uploaded' => [], 'failed' => []];
        $fileValidator = new FileSecurityValidator();

        // 이미지 업로드 처리
        if ($board->allow_image_upload && $request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $result = $this->processImageUpload($image, $fileValidator, $board, $code, $postId);
                if ($result['success']) {
                    $results['uploaded'][] = $result;
                } else {
                    $results['failed'][] = $result;
                }
            }
        }

        // 첨부파일 업로드 처리
        if ($board->allow_file_upload && $request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $result = $this->processFileUpload($attachment, $fileValidator, $board, $code, $postId);
                if ($result['success']) {
                    $results['uploaded'][] = $result;
                } else {
                    $results['failed'][] = $result;
                }
            }
        }

        return $results;
    }

    /**
     * 이미지 업로드 처리
     */
    private function processImageUpload($image, $fileValidator, $board, $code, $postId)
    {
        try {
            // 파일 보안 검증
            $validation = $fileValidator->validateFile($image, [
                'blocked_extensions' => $board->blocked_extensions ?? '',
                'max_file_size' => 5120, // 5MB in KB
                'allowed_types' => ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
            ]);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'filename' => $image->getClientOriginalName(),
                    'error' => $validation['error']
                ];
            }

            // 고유 파일명 생성
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // 계층적 경로: board/{code}/images/{year}/{month}/{day}/{postId}/
            $now = now();
            $path = "board/{$code}/images/{$now->year}/{$now->month}/{$now->day}/{$postId}";

            // 파일 저장
            $filePath = $image->storeAs($path, $filename, 'public');

            // 이미지 테이블 생성 (없는 경우)
            $imagesTable = "site_board_{$code}_images";
            if (!Schema::hasTable($imagesTable)) {
                $this->createBoardImagesTable($imagesTable);
            }

            // 이미지 정보 저장
            DB::table($imagesTable)->insert([
                'post_id' => $postId,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'alt_text' => null,
                'caption' => null,
                'sort_order' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'path' => $filePath
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'filename' => $image->getClientOriginalName(),
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 첨부파일 업로드 처리
     */
    private function processFileUpload($file, $fileValidator, $board, $code, $postId)
    {
        try {
            // 파일 보안 검증
            $validation = $fileValidator->validateFile($file, [
                'blocked_extensions' => $board->blocked_extensions ?? '',
                'max_file_size' => 5120, // 5MB in KB
            ]);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $validation['error']
                ];
            }

            // 고유 파일명 생성
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // 계층적 경로: board/{code}/attachments/{year}/{month}/{day}/{postId}/
            $now = now();
            $path = "board/{$code}/attachments/{$now->year}/{$now->month}/{$now->day}/{$postId}";

            // 파일 저장
            $filePath = $file->storeAs($path, $filename, 'public');

            // 첨부파일 테이블 생성 (없는 경우)
            $attachmentsTable = "site_board_{$code}_attachments";
            if (!Schema::hasTable($attachmentsTable)) {
                $this->createBoardAttachmentsTable($attachmentsTable);
            }

            // 첨부파일 정보 저장
            DB::table($attachmentsTable)->insert([
                'post_id' => $postId,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'download_count' => 0,
                'is_secure' => true,
                'scanned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path' => $filePath
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'filename' => $file->getClientOriginalName(),
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 보드별 이미지 테이블 생성
     */
    private function createBoardImagesTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);

            $table->index('post_id');
        });
    }

    /**
     * 보드별 첨부파일 테이블 생성
     */
    private function createBoardAttachmentsTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_secure')->default(true);
            $table->timestamp('scanned_at')->nullable();

            $table->index('post_id');
        });
    }

    /**
     * 샤드 ID 계산
     */
    private function getShardId($code, $user = null)
    {
        // 기본 샤드 계산 로직
        // 실제 환경에서는 더 복잡한 샤딩 알고리즘을 사용할 수 있습니다

        if ($user && $user->id) {
            // 사용자 ID 기반 샤딩
            return $user->id % 10; // 10개 샤드로 분산
        }

        // 게시판 코드 기반 샤딩 (익명 사용자의 경우)
        return abs(crc32($code)) % 10;
    }

}