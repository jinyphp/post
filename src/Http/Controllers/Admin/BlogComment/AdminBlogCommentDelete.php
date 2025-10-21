<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogComment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBlogCommentDelete extends Controller
{
    private $table = 'site_blog_comments';

    public function __invoke(Request $request, $id)
    {
        try {
            // 댓글 존재 확인
            $comment = DB::table($this->table)->find($id);
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => '댓글을 찾을 수 없습니다.'
                ], 404);
            }

            // 하위 댓글 개수 확인
            $replyCount = DB::table($this->table)
                ->where('parent_id', $id)
                ->count();

            if ($replyCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "이 댓글에 {$replyCount}개의 답글이 있습니다. 답글을 먼저 삭제해주세요."
                ], 400);
            }

            // 댓글 삭제
            DB::table($this->table)->where('id', $id)->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '댓글이 삭제되었습니다.'
                ]);
            }

            return redirect()->back()->with('success', '댓글이 삭제되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Comment deletion error', [
                'comment_id' => $id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다.'
                ], 500);
            }

            return redirect()->back()->with('error', '삭제 중 오류가 발생했습니다.');
        }
    }
}