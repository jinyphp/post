<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogComment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBlogCommentApprove extends Controller
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

            // 승인 상태 토글
            $newStatus = $comment->is_approved ? 0 : 1;

            DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'is_approved' => $newStatus,
                    'updated_at' => now()
                ]);

            $message = $newStatus ? '댓글이 승인되었습니다.' : '댓글 승인이 취소되었습니다.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_approved' => $newStatus
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Comment approval error', [
                'comment_id' => $id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '처리 중 오류가 발생했습니다.'
                ], 500);
            }

            return redirect()->back()->with('error', '처리 중 오류가 발생했습니다.');
        }
    }
}