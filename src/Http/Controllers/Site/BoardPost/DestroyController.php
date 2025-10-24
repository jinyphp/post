<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;

/**
 * 게시글 삭제 컨트롤러
 */
class DestroyController extends Controller
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

        // 삭제 권한 확인
        if (!$this->hasPostPermission($board, $post, 'delete')) {
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', '로그인이 필요합니다.');
            }
            return redirect()->route('board.show', [$code, $id])
                ->with('error', '게시글을 삭제할 권한이 없습니다.');
        }

        // 관련 파일들 삭제
        $this->deleteRelatedFiles($code, $id);

        // 관련 데이터 삭제 (댓글, 평가 등)
        $this->deleteRelatedData($code, $id);

        // 하위 게시글들도 삭제
        $this->deleteChildPosts($table, $id, $code);

        // 게시글 삭제
        DB::table($table)->where('id', $id)->delete();

        return redirect()->route('board.index', $code)
            ->with('success', '게시글이 삭제되었습니다.');
    }

    /**
     * 관련 파일들 삭제
     */
    private function deleteRelatedFiles($code, $postId)
    {
        // 이미지 파일 삭제
        $imageTable = "site_board_{$code}_images";
        if (Schema::hasTable($imageTable)) {
            $images = DB::table($imageTable)->where('post_id', $postId)->get();
            foreach ($images as $image) {
                if (Storage::disk('public')->exists($image->file_path)) {
                    Storage::disk('public')->delete($image->file_path);
                }
            }
            DB::table($imageTable)->where('post_id', $postId)->delete();
        }

        // 첨부파일 삭제
        $attachmentTable = "site_board_{$code}_attachments";
        if (Schema::hasTable($attachmentTable)) {
            $attachments = DB::table($attachmentTable)->where('post_id', $postId)->get();
            foreach ($attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }
            DB::table($attachmentTable)->where('post_id', $postId)->delete();
        }
    }

    /**
     * 관련 데이터 삭제
     */
    private function deleteRelatedData($code, $postId)
    {
        // 댓글 삭제
        $commentTable = "site_board_{$code}_comments";
        if (Schema::hasTable($commentTable)) {
            DB::table($commentTable)->where('post_id', $postId)->delete();
        }

        // 평가 삭제
        $ratingTable = "site_board_{$code}_ratings";
        if (Schema::hasTable($ratingTable)) {
            DB::table($ratingTable)->where('post_id', $postId)->delete();
        }
    }

    /**
     * 하위 게시글들 재귀적으로 삭제
     */
    private function deleteChildPosts($table, $parentId, $code)
    {
        $childPosts = DB::table($table)->where('parent_id', $parentId)->get();

        foreach ($childPosts as $childPost) {
            // 하위 게시글의 파일들과 관련 데이터 삭제
            $this->deleteRelatedFiles($code, $childPost->id);
            $this->deleteRelatedData($code, $childPost->id);

            // 재귀적으로 더 하위 게시글들 삭제
            $this->deleteChildPosts($table, $childPost->id, $code);

            // 하위 게시글 삭제
            DB::table($table)->where('id', $childPost->id)->delete();
        }
    }
}