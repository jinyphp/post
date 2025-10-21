<?php
namespace Jiny\Post\Http\Controllers\Admin\ForumCategory;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Category Delete Controller
 *
 * 포럼 카테고리 삭제를 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminForumCategoryDelete extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_forum_cate';

    /**
     * 포럼 카테고리 삭제 처리
     */
    public function __invoke(Request $request, $id)
    {
        $category = DB::table($this->table)->find($id);

        if (!$category) {
            return redirect()->route('admin.cms.forum.category')
                ->with('error', '카테고리를 찾을 수 없습니다.');
        }

        // 해당 카테고리를 사용하는 게시글이 있는지 확인
        $postCount = DB::table('site_forum')
                       ->where('categories', $category->slug)
                       ->count();

        if ($postCount > 0) {
            return redirect()->route('admin.cms.forum.category')
                ->with('error', '이 카테고리를 사용하는 게시글이 ' . $postCount . '개 있어 삭제할 수 없습니다.');
        }

        DB::table($this->table)->where('id', $id)->delete();

        return redirect()->route('admin.cms.forum.category')
            ->with('success', '포럼 카테고리가 삭제되었습니다.');
    }
}