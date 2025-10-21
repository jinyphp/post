<?php

namespace Jiny\Post\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BlogCommentStore extends Controller
{
    private $table = 'site_blog_comments';

    public function __invoke(Request $request)
    {
        try {
            // 블로그 ID 가져오기
            $blogId = $request->input('blog_id');

            if (!$blogId) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 ID가 필요합니다.'
                ], 400);
            }

            // 블로그 존재 및 댓글 허용 여부 확인
            $blog = DB::table('site_blog')->find($blogId);
            if (!$blog) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 글을 찾을 수 없습니다.'
                ], 404);
            }

            if (!$blog->allow_comments) {
                return response()->json([
                    'success' => false,
                    'message' => '이 글은 댓글이 허용되지 않습니다.'
                ], 403);
            }

            // 사용자 정보 확인
            $userId = Auth::id();

            // 입력값 검증
            $validationRules = [
                'content' => 'required|string|max:2000',
                'author_website' => 'nullable|url|max:255',
                'parent_id' => 'nullable|exists:site_blog_comments,id'
            ];

            // 로그인하지 않은 사용자는 이름과 이메일 필수
            if (!$userId) {
                $validationRules['author_name'] = 'required|string|max:255';
                $validationRules['author_email'] = 'required|email|max:255';
            }

            $request->validate($validationRules);

            // 사용자 정보 설정
            $authorName = $userId ? Auth::user()->name : $request->input('author_name');
            $authorEmail = $userId ? Auth::user()->email : $request->input('author_email');

            // 댓글 데이터 준비
            $commentData = [
                'blog_id' => $blogId,
                'parent_id' => $request->input('parent_id'),
                'user_id' => $userId,
                'author_name' => $authorName,
                'author_email' => $authorEmail,
                'author_website' => $request->input('author_website'),
                'content' => trim($request->input('content')),
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_approved' => 0, // 기본적으로 승인 대기
                'created_at' => now(),
                'updated_at' => now()
            ];

            // 로그인한 사용자의 경우 자동 승인 (선택사항)
            if ($userId) {
                $commentData['is_approved'] = 1;
                $commentData['status'] = 'approved';
            }

            // 댓글 저장
            $commentId = DB::table($this->table)->insertGetId($commentData);

            // 저장된 댓글 정보 반환
            $comment = DB::table($this->table)->find($commentId);

            return response()->json([
                'success' => true,
                'message' => $userId ? '댓글이 등록되었습니다.' : '댓글이 등록되었습니다. 관리자 승인 후 표시됩니다.',
                'comment' => $comment
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력값을 확인해주세요.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Comment submission error', [
                'blog_id' => $blogId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '댓글 등록 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}