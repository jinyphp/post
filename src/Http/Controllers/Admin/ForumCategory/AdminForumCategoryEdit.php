<?php
namespace Jiny\Post\Http\Controllers\Admin\ForumCategory;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Admin Forum Category Edit Controller
 *
 * 포럼 카테고리 수정을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminForumCategoryEdit extends Controller
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
     * 포럼 카테고리 수정 처리 (GET: 폼 표시, POST/PUT: 수정 처리)
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
            return redirect()->route('admin.cms.forum.category')
                ->with('error', '카테고리를 찾을 수 없습니다.');
        }

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }

    /**
     * 데이터 수정
     */
    protected function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:site_forum_cate,name,' . $id,
            'color' => 'required',
        ]);

        $data = $request->except(['_token', '_method']);

        // 슬러그 업데이트
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // 현재 항목을 제외하고 슬러그 중복 체크
        $originalSlug = $data['slug'];
        $counter = 1;
        while (DB::table($this->table)
                 ->where('slug', $data['slug'])
                 ->where('id', '!=', $id)
                 ->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // 체크박스 처리
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // 타임스탬프 및 수정자 추가
        $data['updated_at'] = now();
        $data['updated_by'] = auth()->user()->name ?? 'admin';

        DB::table($this->table)
            ->where('id', $id)
            ->update($data);

        return redirect()->route('admin.cms.forum.category')
            ->with('success', '포럼 카테고리가 수정되었습니다.');
    }
}