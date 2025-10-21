<?php
namespace Jiny\Post\Http\Controllers\Admin\BlogCategory;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Admin Blog Category Edit Controller
 *
 * 블로그 카테고리 수정을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogCategoryEdit extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog_cate';

    /**
     * 뷰 경로
     */
    protected $viewPath = 'jiny-post::admin.blog_category';

    /**
     * 페이지 설정
     */
    protected $config = [
        'title' => '블로그 카테고리',
        'subtitle' => '블로그 카테고리를 관리합니다.',
    ];

    /**
     * 블로그 카테고리 수정 처리 (GET: 폼 표시, POST/PUT: 수정 처리)
     */
    public function __invoke(Request $request, $id)
    {
        // 디버깅 로그
        \Log::info('BlogCategory Edit Request', [
            'method' => $request->getMethod(),
            'id' => $id,
            'all_data' => $request->all()
        ]);

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
        $category = DB::table($this->table)->find($id);

        if (!$category) {
            return redirect()->route('admin.cms.blog.category')
                ->with('error', '카테고리를 찾을 수 없습니다.');
        }

        return view("{$this->viewPath}.edit", [
            'category' => $category,
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }

    /**
     * 데이터 수정
     */
    protected function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|unique:site_blog_cate,name,' . $id,
                'color' => 'required',
            ]);

            $data = $request->only([
                'name', 'slug', 'description', 'color', 'icon', 'sort_order'
            ]);

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

            $affected = DB::table($this->table)
                ->where('id', $id)
                ->update($data);

            if ($affected === 0) {
                return redirect()->route('admin.cms.blog.category')
                    ->with('error', '카테고리를 찾을 수 없거나 수정할 수 없습니다.');
            }

            return redirect()->route('admin.cms.blog.category')
                ->with('success', '블로그 카테고리가 수정되었습니다.');

        } catch (\Exception $e) {
            // 에러 로깅
            \Log::error('BlogCategory update error: ' . $e->getMessage());

            return redirect()->route('admin.cms.blog.category')
                ->with('error', '수정 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}