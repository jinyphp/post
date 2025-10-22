<?php
namespace Jiny\Post\Http\Controllers\Admin\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Forum Show Controller
 *
 * 포럼 글 상세보기를 표시하는 단일 액션 컨트롤러입니다.
 */
class AdminForumShow extends Controller
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
        'title' => '포럼 글 상세보기',
        'subtitle' => '포럼 글의 상세 정보를 확인합니다.',
    ];

    /**
     * 포럼 글 상세보기
     */
    public function __invoke(Request $request, $id)
    {
        $row = DB::table($this->table)->where('id', $id)->first();

        if (!$row) {
            return redirect()->route('admin.cms.forum')
                           ->with('error', '요청하신 포럼 글을 찾을 수 없습니다.');
        }

        // 조회수 증가
        DB::table($this->table)
          ->where('id', $id)
          ->increment('click');

        // 카테고리 정보 가져오기
        $category = null;
        if ($row->categories) {
            $category = DB::table('site_forum_cate')
                          ->where('slug', $row->categories)
                          ->first();
        }

        // 포럼 이미지들 조회
        $forumImages = DB::table('site_forum_images')
            ->where('forum_id', $id)
            ->orderBy('sort_order')
            ->get();

        return view("{$this->viewPath}.show", [
            'row' => $row,
            'config' => $this->config,
            'actions' => $this->config,
            'category' => $category,
            'forumImages' => $forumImages,
        ]);
    }
}