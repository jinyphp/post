<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Index Controller
 *
 * 포럼 글 목록을 표시하는 단일 액션 컨트롤러입니다.
 */
class AdminForumIndex extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_forum';

    /**
     * 뷰 경로
     */
    protected $viewPath = 'jiny-post::admin.forum';

    /**
     * 페이지 설정
     */
    protected $config = [
        'title' => '포럼',
        'subtitle' => '작성된 포럼 글을 관리합니다.',
    ];

    /**
     * 포럼 글 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = DB::table($this->table);

        // 검색 기능
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        // 카테고리 필터
        if ($category = $request->get('category')) {
            $query->where('categories', $category);
        }

        $rows = $query->orderBy('created_at', 'desc')->paginate(15);

        // 검색 파라미터를 페이지네이션에 전달
        $rows->appends($request->query());

        // 활성화된 카테고리 목록 가져오기 (필터용)
        $categories = DB::table('site_forum_cate')
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();

        return view("{$this->viewPath}.index", [
            'rows' => $rows,
            'config' => $this->config,
            'actions' => $this->config,
            'categories' => $categories,
        ]);
    }
}