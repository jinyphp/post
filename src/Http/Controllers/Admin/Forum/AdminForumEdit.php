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
     * 포럼 글 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        $item = DB::table($this->table)->find($id);

        if (!$item) {
            return redirect()->route('admin.cms.forum.index')
                ->with('error', '포럼 글을 찾을 수 없습니다.');
        }

        // 디버깅: 로드된 데이터 확인
        \Log::info('Admin forum edit data loaded:', [
            'forum_id' => $id,
            'title' => $item->title ?? 'null',
            'content' => $item->content ?? 'null',
            'content_length' => strlen($item->content ?? ''),
            'name' => $item->name ?? 'null',
            'email' => $item->email ?? 'null',
            'all_fields' => (array) $item
        ]);

        // 활성화된 카테고리 목록 가져오기
        $categories = DB::table('site_forum_cate')
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();

        // 포럼 이미지들 조회
        $forumImages = DB::table('site_forum_images')
            ->where('forum_id', $id)
            ->orderBy('sort_order')
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

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'config' => $this->config,
            'actions' => $this->config,
            'categories' => $categories,
            'forumImages' => $forumImages,
            'forumSettings' => $forumSettings,
        ]);
    }
}