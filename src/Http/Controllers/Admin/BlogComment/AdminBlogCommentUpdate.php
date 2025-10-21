<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogComment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBlogCommentUpdate extends Controller
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

            // 입력값 검증
            $request->validate([
                'author_name' => 'required|string|max:255',
                'author_email' => 'required|email|max:255',
                'author_website' => 'nullable|url|max:255',
                'content' => 'required|string|max:2000',
                'is_approved' => 'required|boolean'
            ]);

            // 댓글 데이터 업데이트
            $updateData = [
                'author_name' => $request->input('author_name'),
                'author_email' => $request->input('author_email'),
                'author_website' => $request->input('author_website'),
                'content' => trim($request->input('content')),
                'is_approved' => $request->input('is_approved'),
                'updated_at' => now()
            ];

            DB::table($this->table)
                ->where('id', $id)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => '댓글이 성공적으로 수정되었습니다.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력값을 확인해주세요.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Comment update error', [
                'comment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '댓글 수정 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}