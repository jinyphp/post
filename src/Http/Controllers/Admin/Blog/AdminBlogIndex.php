<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Single Action Controller for Blog Index
 *
 * 블로그 목록 표시를 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogIndex extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 목록 표시
     */
    public function __invoke(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $status = $request->get('status');
            $category = $request->get('category');

            $query = DB::table($this->table)
                ->select([
                    $this->table . '.*',
                    'site_blog_cate.name as category_name',
                    'site_blog_cate.color as category_color',
                    DB::raw('(SELECT COUNT(*) FROM site_blog_comments WHERE site_blog_comments.blog_id = ' . $this->table . '.id) as comments_count')
                ])
                ->leftJoin('site_blog_cate', $this->table . '.category_slug', '=', 'site_blog_cate.slug')
                ->orderBy($this->table . '.created_at', 'desc');

            // 검색 필터
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where($this->table . '.title', 'like', "%{$search}%")
                      ->orWhere($this->table . '.content', 'like', "%{$search}%")
                      ->orWhere($this->table . '.excerpt', 'like', "%{$search}%");
                });
            }

            // 상태 필터
            if ($status) {
                $query->where($this->table . '.status', $status);
            }

            // 카테고리 필터
            if ($category) {
                $query->where($this->table . '.category_slug', $category);
            }

            $blogs = $query->paginate($perPage);

            // 상태별 카운트
            $statusCounts = DB::table($this->table)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // 카테고리 목록
            $categories = DB::table('site_blog_cate')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return view('jiny-post::admin.blog.index', compact('blogs', 'statusCounts', 'categories', 'search', 'status', 'category'));

        } catch (\Exception $e) {
            \Log::error('Blog index error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 목록을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', '블로그 목록을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}