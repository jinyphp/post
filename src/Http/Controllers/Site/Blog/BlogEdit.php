<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Blog Edit Controller
 *
 * 블로그 글 수정 폼을 처리하는 컨트롤러입니다.
 */
class BlogEdit extends Controller
{
    /**
     * 블로그 글 수정 폼 표시
     */
    public function __invoke($slug)
    {
        // 블로그 글 조회
        $blog = DB::table('site_blog')
                  ->where('slug', $slug)
                  ->first();

        if (!$blog) {
            abort(404, '존재하지 않는 글입니다.');
        }

        // 현재 사용자 정보 가져오기
        $user = Auth::user();

        // 수정 권한 확인
        if (!$this->canEditBlog($blog, $user)) {
            return redirect()->route('blog.show', $slug)
                ->with('error', '이 글을 수정할 권한이 없습니다.');
        }

        // 블로그 이미지 가져오기
        $blogImages = DB::table('site_blog_images')
            ->where('blog_id', $blog->id)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 카테고리 목록 가져오기
        $categories = DB::table('site_blog_cate')
                        ->where('is_active', 1)
                        ->where('is_private', 0)
                        ->orderBy('sort_order', 'asc')
                        ->get();

        return view('jiny-post::www.blog.edit', [
            'blog' => $blog,
            'blogImages' => $blogImages,
            'categories' => $categories,
        ]);
    }

    /**
     * 블로그 글 수정 권한 확인
     */
    protected function canEditBlog($blog, $user)
    {
        // 로그인하지 않은 경우
        if (!$user) {
            return false;
        }

        // 관리자는 모든 글 수정 가능
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