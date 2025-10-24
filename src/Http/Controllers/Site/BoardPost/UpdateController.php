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
use Jiny\Post\Services\FileSecurityValidator;

/**
 * 게시글 수정 컨트롤러
 */
class UpdateController extends Controller
{
    use BoardPermissions;

    public function __invoke(Request $request, $code, $id)
    {
        // JWT 또는 세션 기반 인증 설정
        $user = $this->setupAuth($request);

        $board = $this->getBoardInfo($code);

        if (!$board) {
            abort(404, '게시판을 찾을 수 없습니다.');
        }

        $table = "site_board_" . $code;

        if (!Schema::hasTable($table)) {
            abort(404, '게시판 테이블이 존재하지 않습니다.');
        }

        $post = DB::table($table)->find($id);

        if (!$post) {
            abort(404, '게시글을 찾을 수 없습니다.');
        }

        // 수정 권한 확인
        if (!$this->hasPostPermission($board, $post, 'edit')) {
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', '로그인이 필요합니다.');
            }
            return redirect()->route('board.show', [$code, $id])
                ->with('error', '게시글을 수정할 권한이 없습니다.');
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

        $data = $request->only(['title', 'content']);
        $data['updated_at'] = now();

        DB::table($table)
            ->where('id', $id)
            ->update($data);

        // 파일 삭제 처리
        $this->handleFileDeletion($request, $code, $id);

        // 새 파일 업로드 처리
        $uploadResults = $this->handleFileUploads($request, $board, $code, $id);

        // 성공 메시지 구성
        $message = '게시글이 수정되었습니다.';
        if (!empty($uploadResults['uploaded'])) {
            $message .= ' ' . count($uploadResults['uploaded']) . '개의 파일이 업로드되었습니다.';
        }
        if (!empty($uploadResults['failed'])) {
            $message .= ' ' . count($uploadResults['failed']) . '개의 파일이 업로드에 실패했습니다.';
        }

        return redirect()->route('board.show', [$code, $id])
            ->with('success', $message);
    }

    /**
     * 파일 삭제 처리
     */
    private function handleFileDeletion($request, $code, $postId)
    {
        \Log::info('HandleFileDeletion called', [
            'code' => $code,
            'postId' => $postId,
            'remove_image_ids' => $request->input('remove_image_ids'),
            'remove_attachment_ids' => $request->input('remove_attachment_ids'),
        ]);

        // 삭제할 이미지 처리
        if ($request->has('remove_image_ids') && $request->remove_image_ids) {
            $deletedImageIds = explode(',', $request->remove_image_ids);
            $imageTable = "site_board_{$code}_images";

            \Log::info('Image deletion processing', [
                'deletedImageIds' => $deletedImageIds,
                'imageTable' => $imageTable
            ]);

            if (Schema::hasTable($imageTable)) {
                foreach ($deletedImageIds as $imageId) {
                    if (!empty($imageId) && is_numeric($imageId)) {
                        $image = DB::table($imageTable)->find($imageId);
                        if ($image) {
                            \Log::info('Deleting image', [
                                'imageId' => $imageId,
                                'file_path' => $image->file_path
                            ]);

                            // 파일 삭제
                            if (Storage::disk('public')->exists($image->file_path)) {
                                Storage::disk('public')->delete($image->file_path);
                                \Log::info('Physical file deleted', ['file_path' => $image->file_path]);
                            }
                            // DB 레코드 삭제
                            $deleted = DB::table($imageTable)->where('id', $imageId)->delete();
                            \Log::info('DB record deleted', ['imageId' => $imageId, 'deleted_count' => $deleted]);
                        } else {
                            \Log::warning('Image not found for deletion', ['imageId' => $imageId]);
                        }
                    }
                }
            }
        }

        // 삭제할 첨부파일 처리
        if ($request->has('remove_attachment_ids') && $request->remove_attachment_ids) {
            $deletedAttachmentIds = explode(',', $request->remove_attachment_ids);
            $attachmentTable = "site_board_{$code}_attachments";

            if (Schema::hasTable($attachmentTable)) {
                foreach ($deletedAttachmentIds as $attachmentId) {
                    if (!empty($attachmentId) && is_numeric($attachmentId)) {
                        $attachment = DB::table($attachmentTable)->find($attachmentId);
                        if ($attachment) {
                            // 파일 삭제
                            if (Storage::disk('public')->exists($attachment->file_path)) {
                                Storage::disk('public')->delete($attachment->file_path);
                            }
                            // DB 레코드 삭제
                            DB::table($attachmentTable)->where('id', $attachmentId)->delete();
                        }
                    }
                }
            }
        }
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
}