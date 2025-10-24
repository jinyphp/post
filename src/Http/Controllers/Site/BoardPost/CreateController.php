<?php

namespace Jiny\Post\Http\Controllers\Site\BoardPost;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;

/**
 * 새 게시글 작성 폼 컨트롤러
 */
class CreateController extends Controller
{
    use BoardPermissions;

    protected $viewPath = 'jiny-post::www.board_post';

    public function __invoke(Request $request, $code, $id = null)
    {
        // JWT 또는 세션 기반 인증 설정
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

        // 답글인 경우 부모 게시글 정보 가져오기
        $parent = null;
        $parentId = $id ?? $request->get('parent');
        if ($parentId) {
            $table = "site_board_" . $code;
            $parent = \DB::table($table)->find($parentId);

            if (!$parent) {
                return redirect()->route('board.index', $code)
                    ->with('error', '답글을 작성할 원본 게시글을 찾을 수 없습니다.');
            }
        }

        return view("{$this->viewPath}.create", [
            'board' => $board,
            'code' => $code,
            'user' => $user, // JWT 사용자 정보 전달
            'isAuthenticated' => Auth::check(),
            'parent' => $parent, // 답글인 경우 부모 게시글 정보
        ]);
    }
}
