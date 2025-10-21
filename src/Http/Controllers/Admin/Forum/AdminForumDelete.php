<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Delete Controller
 *
 * 포럼 글 삭제를 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminForumDelete extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_forum';

    /**
     * 포럼 글 삭제 처리
     */
    public function __invoke(Request $request, $id)
    {
        try {
            $item = DB::table($this->table)->find($id);

            if (!$item) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '포럼 글을 찾을 수 없습니다.'
                    ], 404);
                }

                return redirect()->route('admin.cms.forum')
                    ->with('error', '포럼 글을 찾을 수 없습니다.');
            }

            DB::table($this->table)->where('id', $id)->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '포럼 글이 삭제되었습니다.',
                    'redirect' => route('admin.cms.forum')
                ]);
            }

            return redirect()->route('admin.cms.forum')
                ->with('success', '포럼 글이 삭제되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Forum delete error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.cms.forum')
                ->with('error', '삭제 중 오류가 발생했습니다.');
        }
    }
}