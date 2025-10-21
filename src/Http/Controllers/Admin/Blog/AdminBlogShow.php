<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Single Action Controller for Blog Show
 *
 * 블로그 상세 보기를 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogShow extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 상세 보기
     */
    public function __invoke(Request $request, $id)
    {
        try {
            $blog = DB::table($this->table)
                ->select([
                    $this->table . '.*',
                    'site_blog_cate.name as category_name',
                    'site_blog_cate.color as category_color',
                    'site_blog_cate.icon as category_icon'
                ])
                ->leftJoin('site_blog_cate', $this->table . '.category_slug', '=', 'site_blog_cate.slug')
                ->where($this->table . '.id', $id)
                ->first();

            if (!$blog) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '블로그 글을 찾을 수 없습니다.'
                    ], 404);
                }

                return redirect()->route('admin.cms.blog')
                    ->with('error', '블로그 글을 찾을 수 없습니다.');
            }

            // 조회수 증가
            DB::table($this->table)
                ->where('id', $id)
                ->increment('views');

            // 댓글 가져오기 (승인된 댓글만, 답글 구조 유지)
            $comments = DB::table('site_blog_comments')
                ->leftJoin('users', 'site_blog_comments.user_id', '=', 'users.id')
                ->select([
                    'site_blog_comments.*',
                    'users.name as user_name'
                ])
                ->where('site_blog_comments.blog_id', $id)
                ->where('site_blog_comments.is_approved', 1)
                ->orderBy('site_blog_comments.created_at', 'asc')
                ->get();

            // 댓글 계층 구조 생성
            $commentsTree = $this->buildCommentsTree($comments);

            // 댓글 통계
            $commentStats = [
                'total' => DB::table('site_blog_comments')->where('blog_id', $id)->count(),
                'approved' => DB::table('site_blog_comments')->where('blog_id', $id)->where('is_approved', 1)->count(),
                'pending' => DB::table('site_blog_comments')->where('blog_id', $id)->where('is_approved', 0)->count()
            ];

            return view('jiny-post::admin.blog.show', compact('blog', 'commentsTree', 'commentStats'));

        } catch (\Exception $e) {
            \Log::error('Blog show error', ['id' => $id, 'error' => $e->getMessage()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 글을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.cms.blog')
                ->with('error', '블로그 글을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 댓글을 계층 구조로 변환
     */
    private function buildCommentsTree($comments)
    {
        $commentMap = [];
        $tree = [];

        // 먼저 모든 댓글을 ID로 맵핑
        foreach ($comments as $comment) {
            $comment->children = [];
            $commentMap[$comment->id] = $comment;
        }

        // 계층 구조 생성
        foreach ($comments as $comment) {
            if ($comment->parent_id && isset($commentMap[$comment->parent_id])) {
                // 답글인 경우 부모에 추가
                $commentMap[$comment->parent_id]->children[] = $comment;
            } else {
                // 최상위 댓글인 경우 트리에 직접 추가
                $tree[] = $comment;
            }
        }

        return $tree;
    }
}