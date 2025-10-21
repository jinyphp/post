<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogComment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBlogCommentIndex extends Controller
{
    private $table = 'site_blog_comments';

    public function __invoke(Request $request)
    {
        // 검색 및 필터 파라미터
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $blogId = $request->get('blog_id', '');

        // 기본 쿼리
        $query = DB::table($this->table . ' as c')
            ->leftJoin('site_blog as b', 'c.blog_id', '=', 'b.id')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->select([
                'c.*',
                'b.title as blog_title',
                'b.slug as blog_slug',
                'u.name as user_name'
            ]);

        // 검색 조건
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('c.author_name', 'like', "%{$search}%")
                  ->orWhere('c.author_email', 'like', "%{$search}%")
                  ->orWhere('c.content', 'like', "%{$search}%")
                  ->orWhere('b.title', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if (!empty($status)) {
            if ($status === 'approved') {
                $query->where('c.is_approved', 1);
            } elseif ($status === 'pending') {
                $query->where('c.is_approved', 0);
            }
        }

        // 특정 블로그 필터
        if (!empty($blogId)) {
            $query->where('c.blog_id', $blogId);
        }

        // 정렬 및 페이지네이션
        $comments = $query->orderBy('c.created_at', 'desc')
                         ->paginate(20);

        // 상태별 통계
        $statusCounts = [
            'total' => DB::table($this->table)->count(),
            'approved' => DB::table($this->table)->where('is_approved', 1)->count(),
            'pending' => DB::table($this->table)->where('is_approved', 0)->count()
        ];

        // 최근 블로그 목록 (필터용)
        $recentBlogs = DB::table('site_blog')
            ->select('id', 'title')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('jiny-post::admin.blog_comment.index', compact(
            'comments',
            'statusCounts',
            'search',
            'status',
            'blogId',
            'recentBlogs'
        ));
    }
}