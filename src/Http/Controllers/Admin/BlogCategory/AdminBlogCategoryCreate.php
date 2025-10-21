<?php
namespace Jiny\Post\Http\Controllers\Admin\BlogCategory;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Admin Blog Category Create Controller
 *
 * 블로그 카테고리 생성을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogCategoryCreate extends Controller
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
     * 블로그 카테고리 생성 처리 (GET: 폼 표시, POST: 저장 처리)
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
        return view("{$this->viewPath}.create", [
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }

    /**
     * 데이터 저장
     */
    protected function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:site_blog_cate,name',
            'color' => 'required',
        ]);

        $data = $request->only([
            'name', 'slug', 'description', 'color', 'icon', 'sort_order'
        ]);

        // 슬러그 자동 생성
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // 슬러그 중복 체크 및 수정
        $originalSlug = $data['slug'];
        $counter = 1;
        while (DB::table($this->table)->where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // 체크박스 처리
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // 타임스탬프 추가
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $data['created_by'] = auth()->user()->name ?? 'admin';

        DB::table($this->table)->insert($data);

        return redirect()->route('admin.cms.blog.category')
            ->with('success', '블로그 카테고리가 생성되었습니다.');
    }
}