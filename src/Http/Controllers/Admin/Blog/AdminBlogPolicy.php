<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Blog Policy Management Controller
 *
 * 블로그 작성 정책을 관리하는 컨트롤러입니다.
 */
class AdminBlogPolicy extends Controller
{
    /**
     * 블로그 정책 설정 페이지 표시 및 저장
     */
    public function __invoke(Request $request)
    {
        if ($request->isMethod('POST')) {
            return $this->updatePolicy($request);
        }

        return $this->showPolicy($request);
    }

    /**
     * 현재 정책 설정 표시
     */
    private function showPolicy(Request $request)
    {
        try {
            // 현재 정책 설정 조회
            $policies = DB::table('site_config')
                ->where('key', 'LIKE', 'blog_policy_%')
                ->pluck('value', 'key')
                ->toArray();

            // 기본 정책 설정
            $defaultPolicies = [
                'blog_policy_admin_write' => 'true',           // 관리자 작성
                'blog_policy_user_write' => 'false',           // 일반 사용자 작성
                'blog_policy_guest_write' => 'false',          // 비회원 작성
                'blog_policy_user_approval' => 'true',         // 사용자 글 승인 필요
                'blog_policy_auto_approve_admin' => 'true',    // 관리자 글 자동 승인
                'blog_policy_featured_admin_only' => 'true',   // 추천글은 관리자만
                'blog_policy_category_restriction' => 'false', // 카테고리 제한
            ];

            // 기본값과 병합
            $currentPolicies = array_merge($defaultPolicies, $policies);

            // 통계 정보
            $stats = [
                'total_blogs' => DB::table('site_blog')->count(),
                'published_blogs' => DB::table('site_blog')->where('status', 'published')->count(),
                'draft_blogs' => DB::table('site_blog')->where('status', 'draft')->count(),
                'pending_blogs' => DB::table('site_blog')->where('status', 'pending')->count(),
                'admin_blogs' => DB::table('site_blog')
                    ->join('users', 'site_blog.author_id', '=', 'users.id')
                    ->where('users.isAdmin', true)
                    ->count(),
                'user_blogs' => DB::table('site_blog')
                    ->join('users', 'site_blog.author_id', '=', 'users.id')
                    ->where('users.isAdmin', false)
                    ->count(),
            ];

            return view('jiny-post::admin.blog.policy', compact('currentPolicies', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Blog policy view error', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', '정책 설정을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 정책 설정 업데이트
     */
    private function updatePolicy(Request $request)
    {
        try {
            // 입력값 검증
            $request->validate([
                'blog_policy_admin_write' => 'required|boolean',
                'blog_policy_user_write' => 'required|boolean',
                'blog_policy_guest_write' => 'required|boolean',
                'blog_policy_user_approval' => 'required|boolean',
                'blog_policy_auto_approve_admin' => 'required|boolean',
                'blog_policy_featured_admin_only' => 'required|boolean',
                'blog_policy_category_restriction' => 'required|boolean',
            ]);

            // 정책 설정 저장
            $policies = [
                'blog_policy_admin_write',
                'blog_policy_user_write',
                'blog_policy_guest_write',
                'blog_policy_user_approval',
                'blog_policy_auto_approve_admin',
                'blog_policy_featured_admin_only',
                'blog_policy_category_restriction'
            ];

            foreach ($policies as $policy) {
                DB::table('site_config')->updateOrInsert(
                    ['key' => $policy],
                    [
                        'value' => $request->input($policy) ? 'true' : 'false',
                        'updated_at' => now()
                    ]
                );
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 정책이 성공적으로 업데이트되었습니다.'
                ]);
            }

            return redirect()->back()
                ->with('success', '블로그 정책이 성공적으로 업데이트되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '입력값을 확인해주세요.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            \Log::error('Blog policy update error', ['error' => $e->getMessage()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '정책 업데이트 중 오류가 발생했습니다.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', '정책 업데이트 중 오류가 발생했습니다: ' . $e->getMessage())
                ->withInput();
        }
    }
}