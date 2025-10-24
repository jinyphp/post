<?php

namespace Jiny\Post\Http\Controllers\Admin\CommentReports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * CommentReports 관리 메인 컨트롤러 (목록/인덱스 페이지)
 *
 * 게시판 댓글 신고 목록을 표시하고 관리하는 기능을 제공합니다.
 * Livewire 컴포넌트(AdminTable)와 Hook 패턴을 통해 동작합니다.
 *
 * @package Jiny\Post\Http\Controllers\Admin\CommentReports
 * @since   1.0.0
 */
class CommentReports extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     *
     * CommentReports.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * CommentReports 목록 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'CommentReports.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // Stats 데이터 계산
        $statsData = $this->getStatsData();

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            // Stats 데이터 추가
            'pendingCount' => $statsData['pending_reports'] ?? 0,
            'approvedCount' => $statsData['approved_reports'] ?? 0,
            'rejectedCount' => $statsData['rejected_reports'] ?? 0,
            'todayCount' => $statsData['today_reports'] ?? 0,
        ]);
    }

    /**
     * Hook: Livewire 컴포넌트의 데이터 조회 전 실행
     * 데이터베이스 쿼리 조건을 수정하거나 추가 로직을 실행할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     * 조회된 데이터를 가공하거나 추가 처리를 할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param mixed $rows 조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        // 댓글 정보와 게시글 정보를 추가로 조회하여 포함
        foreach ($rows as $row) {
            if ($row->board_code && $row->comment_id) {
                $commentTable = "site_board_" . $row->board_code . "_comments";
                if (DB::getSchemaBuilder()->hasTable($commentTable)) {
                    $comment = DB::table($commentTable)->find($row->comment_id);
                    if ($comment) {
                        $row->comment_content = $comment->content;
                        $row->comment_author = $comment->name;
                    }
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

        return $rows;
    }

    /**
     * Hook: 통계 데이터 제공
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param string $query 통계 쿼리 이름
     * @return int 통계 수치
     */
    public function hookStatsQuery($wire, $query)
    {
        $table = 'site_board_comment_reports';

        switch ($query) {
            case 'pending_reports':
                return DB::table($table)->where('status', 'pending')->count();

            case 'approved_reports':
                return DB::table($table)->where('status', 'approved')->count();

            case 'rejected_reports':
                return DB::table($table)->where('status', 'rejected')->count();

            case 'today_reports':
                return DB::table($table)
                    ->whereDate('created_at', today())
                    ->count();

            default:
                return 0;
        }
    }

    /**
     * Stats 데이터 가져오기
     *
     * @return array
     */
    private function getStatsData()
    {
        $stats = [];

        // JSON 설정에서 정의된 stats queries 실행
        if (isset($this->jsonData['index']['stats']['cards'])) {
            foreach ($this->jsonData['index']['stats']['cards'] as $card) {
                $query = $card['query'] ?? null;
                if ($query) {
                    $stats[$query] = $this->hookStatsQuery(null, $query);
                }
            }
        }

        return $stats;
    }

    /**
     * Hook: 신고 처리 (승인)
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomApprove($wire, $params = [])
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            $wire->dispatch('showError', message: 'ID가 필요합니다.');
            return false;
        }

        try {
            // 신고 정보 조회
            $report = DB::table('site_board_comment_reports')->find($id);
            if (!$report) {
                $wire->dispatch('showError', message: '신고를 찾을 수 없습니다.');
                return false;
            }

            if ($report->status !== 'pending') {
                $wire->dispatch('showError', message: '대기중인 신고만 처리할 수 있습니다.');
                return false;
            }

            // 신고 승인 처리
            DB::table('site_board_comment_reports')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            // 해당 댓글을 숨김 처리 (삭제하지 않고 숨김)
            $commentTable = "site_board_" . $report->board_code . "_comments";
            if (DB::getSchemaBuilder()->hasTable($commentTable)) {
                DB::table($commentTable)
                    ->where('id', $report->comment_id)
                    ->update([
                        'is_hidden' => true,
                        'hidden_reason' => '신고 승인으로 인한 숨김 처리',
                        'hidden_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $wire->dispatch('showSuccess', message: '신고가 승인되었고, 해당 댓글이 숨김 처리되었습니다.');
            $wire->dispatch('refreshTable');
            return true;

        } catch (\Exception $e) {
            \Log::error('댓글 신고 승인 실패', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $wire->dispatch('showError', message: '신고 승인 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hook: 신고 거부
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomReject($wire, $params = [])
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            $wire->dispatch('showError', message: 'ID가 필요합니다.');
            return false;
        }

        try {
            // 신고 정보 조회
            $report = DB::table('site_board_comment_reports')->find($id);
            if (!$report) {
                $wire->dispatch('showError', message: '신고를 찾을 수 없습니다.');
                return false;
            }

            if ($report->status !== 'pending') {
                $wire->dispatch('showError', message: '대기중인 신고만 처리할 수 있습니다.');
                return false;
            }

            // 신고 거부 처리
            DB::table('site_board_comment_reports')
                ->where('id', $id)
                ->update([
                    'status' => 'rejected',
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            $wire->dispatch('showSuccess', message: '신고가 거부되었습니다.');
            $wire->dispatch('refreshTable');
            return true;

        } catch (\Exception $e) {
            \Log::error('댓글 신고 거부 실패', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            $wire->dispatch('showError', message: '신고 거부 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hook: 대량 삭제
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomBulkDelete($wire, $params = [])
    {
        $ids = $params['ids'] ?? [];

        if (empty($ids)) {
            $wire->dispatch('showError', message: '삭제할 항목을 선택해주세요.');
            return false;
        }

        try {
            DB::table('site_board_comment_reports')->whereIn('id', $ids)->delete();
            $wire->dispatch('showSuccess', message: count($ids) . '개의 신고가 삭제되었습니다.');
            $wire->dispatch('refreshTable');
            return true;
        } catch (\Exception $e) {
            $wire->dispatch('showError', message: '삭제 실패: ' . $e->getMessage());
            return false;
        }
    }
}