<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

/**
 * Blog Delete Controller
 *
 * 블로그 글 삭제를 처리하는 컨트롤러입니다.
 */
class BlogDelete extends Controller
{
    /**
     * 블로그 글 삭제
     */
    public function __invoke(Request $request, $slug)
    {
        // 블로그 글 조회
        $blog = DB::table('site_blog')->where('slug', $slug)->first();

        if (!$blog) {
            abort(404, '존재하지 않는 글입니다.');
        }

        // 현재 사용자 정보 가져오기
        $user = Auth::user();

        // 삭제 권한 확인
        if (!$this->canDeleteBlog($blog, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '이 글을 삭제할 권한이 없습니다.',
                ], 403);
            } else {
                return redirect()->route('blog.show', $slug)
                    ->with('error', '이 글을 삭제할 권한이 없습니다.');
            }
        }

        try {
            DB::beginTransaction();

            // 관련 이미지들 물리적 파일 삭제
            $blogImages = DB::table('site_blog_images')
                ->where('blog_id', $blog->id)
                ->get();

            foreach ($blogImages as $image) {
                try {
                    if ($image->path && Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                        \Log::info('Blog image file deleted: ' . $image->path);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to delete blog image file: ' . $e->getMessage(), [
                        'image_id' => $image->id,
                        'path' => $image->path ?? 'no path',
                    ]);
                }
            }

            // 관련 이미지 데이터베이스 레코드 삭제
            DB::table('site_blog_images')->where('blog_id', $blog->id)->delete();

            // 댓글 삭제 (있는 경우)
            DB::table('site_blog_comments')->where('blog_id', $blog->id)->delete();

            // 블로그 글 삭제
            DB::table('site_blog')->where('id', $blog->id)->delete();

            DB::commit();

            \Log::info('Blog deleted successfully:', [
                'blog_id' => $blog->id,
                'slug' => $slug,
                'user_id' => $user->id ?? null,
            ]);

            // 성공 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 글이 성공적으로 삭제되었습니다.',
                ]);
            } else {
                return redirect()->route('blog.index')
                    ->with('success', '블로그 글이 성공적으로 삭제되었습니다.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Blog delete error: ' . $e->getMessage(), [
                'blog_id' => $blog->id,
                'slug' => $slug,
                'user_id' => $user->id ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 글 삭제 중 오류가 발생했습니다.',
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', '블로그 글 삭제 중 오류가 발생했습니다.');
            }
        }
    }

    /**
     * 블로그 글 삭제 권한 확인
     */
    protected function canDeleteBlog($blog, $user)
    {
        // 로그인하지 않은 경우
        if (!$user) {
            return false;
        }

        // 관리자는 모든 글 삭제 가능
        if ($this->isAdmin($user)) {
            return true;
        }

        // 본인이 작성한 글인지 확인
        if (isset($blog->user_id) && $user->id == $blog->user_id) {
            return true;
        }

        // UUID로 확인 (샤딩 사용자인 경우)
        if (isset($blog->user_uuid) && isset($user->uuid) && $user->uuid == $blog->user_uuid) {
            return true;
        }

        return false;
    }

    /**
     * 관리자 권한 확인
     */
    protected function isAdmin($user)
    {
        // isAdmin 플래그 확인
        if (isset($user->isAdmin) && $user->isAdmin) {
            return true;
        }

        // 관리자 역할 확인
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super-admin');
        }

        // role 필드 직접 확인
        if (isset($user->role) && in_array($user->role, ['admin', 'super-admin'])) {
            return true;
        }

        // utype 필드 확인 (jiny/admin 패키지 방식)
        if (isset($user->utype) && $user->utype === 'admin') {
            return true;
        }

        return false;
    }
}