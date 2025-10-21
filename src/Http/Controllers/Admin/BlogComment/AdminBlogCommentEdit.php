<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogComment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBlogCommentEdit extends Controller
{
    private $table = 'site_blog_comments';

    public function __invoke(Request $request, $id)
    {
        try {
            $comment = DB::table($this->table)->find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => '댓글을 찾을 수 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'comment' => $comment
            ]);

        } catch (\Exception $e) {
            \Log::error('Comment edit fetch error', [
                'comment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '댓글 정보를 불러오는 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}