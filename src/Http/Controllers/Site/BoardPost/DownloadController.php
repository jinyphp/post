<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;

/**
 * 파일 다운로드 컨트롤러
 */
class DownloadController extends Controller
{
    use BoardPermissions;

    public function __invoke(Request $request, $code, $postId, $attachmentId)
    {
        // JWT 또는 세션 기반 인증 설정
        $user = $this->setupAuth($request);

        $board = $this->getBoardInfo($code);

        if (!$board) {
            abort(404, '게시판을 찾을 수 없습니다.');
        }

        $table = "site_board_" . $code;
        $attachmentTable = $table . "_attachments";

        if (!Schema::hasTable($attachmentTable)) {
            abort(404, '첨부파일 테이블이 존재하지 않습니다.');
        }

        // 첨부파일 정보 조회
        $attachment = DB::table($attachmentTable)
            ->where('id', $attachmentId)
            ->where('post_id', $postId)
            ->first();

        if (!$attachment) {
            abort(404, '첨부파일을 찾을 수 없습니다.');
        }

        // 게시글 존재 확인
        $post = DB::table($table)->find($postId);
        if (!$post) {
            abort(404, '게시글을 찾을 수 없습니다.');
        }

        // 읽기 권한 확인
        if (!$this->hasPermission($board, 'read')) {
            abort(403, '파일 다운로드 권한이 없습니다.');
        }

        // 파일 경로 확인
        $filePath = $attachment->file_path;

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, '파일이 존재하지 않습니다.');
        }

        // 다운로드 카운트 증가
        DB::table($attachmentTable)
            ->where('id', $attachmentId)
            ->increment('download_count');

        // 파일 다운로드 응답
        $fileContent = Storage::disk('public')->get($filePath);
        $fileName = $attachment->original_name;

        return Response::make($fileContent, 200, [
            'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($fileContent),
        ]);
    }
}