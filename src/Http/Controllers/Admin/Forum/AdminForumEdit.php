<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Edit Controller
 *
 * 포럼 글 수정을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminForumEdit extends Controller
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
     * 포럼 글 수정 처리 (GET: 폼 표시, POST/PUT: 수정 처리)
     */
    public function __invoke(Request $request, $id)
    {
        if ($request->isMethod('GET')) {
            return $this->showEditForm($request, $id);
        }

        return $this->update($request, $id);
    }

    /**
     * 수정 폼 표시
     */
    protected function showEditForm(Request $request, $id)
    {
        $item = DB::table($this->table)->find($id);

        if (!$item) {
            return redirect()->route('admin.cms.forum')
                ->with('error', '포럼 글을 찾을 수 없습니다.');
        }

        // 활성화된 카테고리 목록 가져오기
        $categories = DB::table('site_forum_cate')
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'config' => $this->config,
            'actions' => $this->config,
            'categories' => $categories,
        ]);
    }

    /**
     * 데이터 수정
     */
    protected function update(Request $request, $id)
    {
        $data = $request->except(['_token', '_method']);
        $data['updated_at'] = now();

        DB::table($this->table)
            ->where('id', $id)
            ->update($data);

        return redirect()->route('admin.cms.forum')
            ->with('success', '포럼 글이 수정되었습니다.');
    }
}