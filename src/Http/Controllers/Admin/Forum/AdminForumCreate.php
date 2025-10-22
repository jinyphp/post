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
     * 포럼 글 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
        // 활성화된 카테고리 목록 가져오기
        $categories = DB::table('site_forum_cate')
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();

        // 포럼 설정 정보 (기본값으로 제공)
        $forumSettings = [
            'enable_tags' => true,
            'enable_file_upload' => true,
            'max_images_per_post' => 10,
            'max_file_size_mb' => 5,
            'max_tags_per_post' => 5,
            'auto_excerpt_length' => 150,
        ];

        return view("{$this->viewPath}.create", [
            'config' => $this->config,
            'actions' => $this->config,
            'categories' => $categories,
            'forumSettings' => $forumSettings,
        ]);
    }
}