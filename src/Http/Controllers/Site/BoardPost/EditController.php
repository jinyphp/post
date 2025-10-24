<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;

/**
 * 게시글 수정 폼 컨트롤러
 */
class EditController extends Controller
{
    use BoardPermissions;

    protected $viewPath = 'jiny-post::www.board_post';

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

        // 첨부된 이미지 가져오기
        $images = [];
        $imageTable = $table . "_images";
        if (Schema::hasTable($imageTable)) {
            $images = DB::table($imageTable)
                ->where('post_id', $id)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();
        }

        // 첨부파일 가져오기
        $attachments = [];
        $attachmentTable = $table . "_attachments";
        if (Schema::hasTable($attachmentTable)) {
            $attachments = DB::table($attachmentTable)
                ->where('post_id', $id)
                ->orderBy('created_at')
                ->get();
        }

        return view("{$this->viewPath}.edit", [
            'board' => $board,
            'post' => $post,
            'code' => $code,
            'images' => $images,
            'attachments' => $attachments,
            'user' => $user,
            'isAuthenticated' => Auth::check(),
        ]);
    }
}