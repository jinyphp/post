<?php

namespace Jiny\Post\Http\Controllers\Admin\CommentReports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * CommentReports 상세보기 컨트롤러
 *
 * 개별 댓글 신고의 상세 정보를 표시합니다.
 *
 * @package Jiny\Post\Http\Controllers\Admin\CommentReports
 * @since   1.0.0
 */
class CommentReportsShow extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * CommentReports 상세 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  조회할 신고 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // 신고 정보 조회
        $report = DB::table('site_board_comment_reports')->find($id);

        if (!$report) {
            abort(404, '신고를 찾을 수 없습니다.');
        }

        // 댓글 정보 조회
        $comment = null;
        $post = null;
        if ($report->board_code && $report->comment_id) {
            $commentTable = "site_board_" . $report->board_code . "_comments";
            if (DB::getSchemaBuilder()->hasTable($commentTable)) {
                $comment = DB::table($commentTable)->find($report->comment_id);

                // 게시글 정보 조회
                if ($comment && $comment->post_id) {
                    $postTable = "site_board_" . $report->board_code;
                    if (DB::getSchemaBuilder()->hasTable($postTable)) {
                        $post = DB::table($postTable)->find($comment->post_id);
                    }
                }
            }
        }

        // 처리자 정보 조회
        $reviewer = null;
        if ($report->reviewed_by) {
            $reviewer = DB::table('users')->find($report->reviewed_by);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'CommentReports.json';
        $settingsPath = $jsonPath;

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'data' => $report,  // jiny-admin 템플릿에서 기대하는 변수명
            'row' => $report,   // 기존 호환성을 위해 유지
            'comment' => $comment,
            'post' => $post,
            'reviewer' => $reviewer,
            'id' => $report->id,
            'itemId' => $report->id,
        ]);
    }

    /**
     * Hook: 상세 데이터 조회 후 실행
     * 조회된 데이터를 가공하거나 추가 처리를 할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param mixed $row 조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookShowed($wire, $row)
    {
        // 댓글 정보와 게시글 정보를 추가로 조회하여 포함
        if ($row->board_code && $row->comment_id) {
            $commentTable = "site_board_" . $row->board_code . "_comments";
            if (DB::getSchemaBuilder()->hasTable($commentTable)) {
                $comment = DB::table($commentTable)->find($row->comment_id);
                if ($comment) {
                    $row->comment_content = $comment->content;
                    $row->comment_author = $comment->name;
                    $row->comment_created_at = $comment->created_at;
                }

                // 게시글 정보도 조회
                $postTable = "site_board_" . $row->board_code;
                if (DB::getSchemaBuilder()->hasTable($postTable) && isset($comment->post_id)) {
                    $post = DB::table($postTable)->find($comment->post_id);
                    if ($post) {
                        $row->post_title = $post->title;
                        $row->post_id = $post->id;
                    }
                }
            }
        }

        // 처리자 정보 추가
        if ($row->reviewed_by) {
            $reviewer = DB::table('users')->find($row->reviewed_by);
            if ($reviewer) {
                $row->reviewer_name = $reviewer->name;
                $row->reviewer_email = $reviewer->email;
            }
        }

        return $row;
    }
}