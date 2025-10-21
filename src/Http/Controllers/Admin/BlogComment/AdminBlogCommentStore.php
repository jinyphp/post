<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogComment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminBlogCommentStore extends Controller
{
    private $table = 'site_blog_comments';

    public function __invoke(Request $request)
    {
        try {
            // 입력값 검증
            $request->validate([
                'blog_id' => 'required|exists:site_blog,id',
                'author_name' => 'required|string|max:255',
                'author_email' => 'required|email|max:255',
                'author_website' => 'nullable|url|max:255',
                'content' => 'required|string|max:2000',
                'is_approved' => 'required|boolean'
            ]);

            // 블로그 글 존재 및 댓글 허용 여부 확인
            $blog = DB::table('site_blog')->find($request->input('blog_id'));
            if (!$blog) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 글을 찾을 수 없습니다.'
                ], 404);
            }

            // 댓글 데이터 준비
            $commentData = [
                'blog_id' => $request->input('blog_id'),
                'parent_id' => null, // 관리자가 생성하는 댓글은 최상위 댓글
                'user_id' => Auth::id(), // 관리자 계정
                'author_name' => $request->input('author_name'),
                'author_email' => $request->input('author_email'),
                'author_website' => $request->input('author_website'),
                'content' => trim($request->input('content')),
                'status' => $request->input('is_approved') ? 'approved' : 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_approved' => $request->input('is_approved'),
                'created_at' => now(),
                'updated_at' => now()
            ];

            // 댓글 저장
            $commentId = DB::table($this->table)->insertGetId($commentData);

            return response()->json([
                'success' => true,
                'message' => '댓글이 성공적으로 작성되었습니다.',
                'comment_id' => $commentId
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력값을 확인해주세요.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Admin comment creation error', [
                'blog_id' => $request->input('blog_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '댓글 작성 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}