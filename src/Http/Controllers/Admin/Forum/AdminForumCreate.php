<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Create Controller
 *
 * 포럼 글 생성을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminForumCreate extends Controller
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
     * 포럼 글 생성 처리 (GET: 폼 표시, POST: 저장 처리)
     */
    public function __invoke(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->showCreateForm($request);
        }

        return $this->store($request);
    }

    /**
     * 생성 폼 표시
     */
    protected function showCreateForm(Request $request)
    {
        // 활성화된 카테고리 목록 가져오기
        $categories = DB::table('site_forum_cate')
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();

        return view("{$this->viewPath}.create", [
            'config' => $this->config,
            'actions' => $this->config,
            'categories' => $categories,
        ]);
    }

    /**
     * 데이터 저장
     */
    protected function store(Request $request)
    {
        $data = $request->except(['_token', '_method']);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table($this->table)->insert($data);

        return redirect()->route('admin.cms.forum')
            ->with('success', '포럼 글이 생성되었습니다.');
    }
}