<?php
namespace Jiny\Post\Http\Controllers\Admin\BlogCategory;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Blog Category Order Controller
 *
 * 블로그 카테고리 정렬 순서 업데이트를 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogCategoryOrder extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog_cate';

    /**
     * 정렬 순서 업데이트 처리
     */
    public function __invoke(Request $request)
    {
        $orders = $request->input('orders', []);

        foreach ($orders as $id => $order) {
            DB::table($this->table)
                ->where('id', $id)
                ->update(['sort_order' => $order]);
        }

        return response()->json(['success' => true]);
    }
}