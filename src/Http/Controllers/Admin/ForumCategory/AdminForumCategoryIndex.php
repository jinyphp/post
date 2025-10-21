<?php
namespace Jiny\Post\Http\Controllers\Admin\ForumCategory;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Category Index Controller
 *
 * 포럼 카테고리 목록을 표시하는 단일 액션 컨트롤러입니다.
 */
class AdminForumCategoryIndex extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_forum_cate';

    /**
     * 뷰 경로
     */
    protected $viewPath = 'jiny-post::admin.forum_category';

    /**
     * 페이지 설정
     */
    protected $config = [
        'title' => '포럼 카테고리',
        'subtitle' => '포럼 카테고리를 관리합니다.',
    ];

    /**
     * 포럼 카테고리 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = DB::table($this->table);

        // 검색 기능
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 상태 필터
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        $rows = $query->orderBy('sort_order', 'asc')
                     ->orderBy('created_at', 'desc')
                     ->paginate(15);

        // 검색 파라미터를 페이지네이션에 전달
        $rows->appends($request->query());

        // 각 카테고리의 게시글 수 업데이트
        foreach ($rows as $row) {
            $postCount = DB::table('site_forum')
                           ->where('categories', $row->slug)
                           ->count();

            if ($postCount != $row->post_count) {
                DB::table($this->table)
                  ->where('id', $row->id)
                  ->update(['post_count' => $postCount]);
                $row->post_count = $postCount;
            }
        }

        return view("{$this->viewPath}.index", [
            'rows' => $rows,
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }
}