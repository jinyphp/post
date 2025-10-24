<?php

namespace Jiny\Post\Http\Controllers\Admin\CommentReports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * CommentReports 삭제 컨트롤러
 *
 * 댓글 신고를 삭제하는 기능을 제공합니다.
 *
 * @package Jiny\Post\Http\Controllers\Admin\CommentReports
 * @since   1.0.0
 */
class CommentReportsDelete extends Controller
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
     * CommentReports 삭제 처리
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  삭제할 신고 ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        try {
            // 신고 정보 조회
            $report = DB::table('site_board_comment_reports')->find($id);

            if (!$report) {
                session()->flash('error', '신고를 찾을 수 없습니다.');
                return redirect()->route('admin.cms.board.comment.reports.index');
            }

            // 신고 삭제
            DB::table('site_board_comment_reports')
                ->where('id', $id)
                ->delete();

            session()->flash('success', '신고가 삭제되었습니다.');
            return redirect()->route('admin.cms.board.comment.reports');

        } catch (\Exception $e) {
            \Log::error('댓글 신고 삭제 실패', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', '신고 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
            return redirect()->route('admin.cms.board.comment.reports');
        }
    }

    /**
     * Hook: 삭제 전 실행
     * 삭제 조건을 확인하거나 추가 로직을 실행할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param int $id 삭제할 ID
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 삭제 중단
     */
    public function hookDeleting($wire, $id)
    {
        // 신고 존재 여부 확인
        $report = DB::table('site_board_comment_reports')->find($id);

        if (!$report) {
            $wire->dispatch('showError', message: '삭제할 신고를 찾을 수 없습니다.');
            return true; // 삭제 중단
        }

        return false; // 정상 진행
    }

    /**
     * Hook: 삭제 후 실행
     * 삭제 완료 후 추가 처리를 할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param int $id 삭제된 ID
     * @return mixed
     */
    public function hookDeleted($wire, $id)
    {
        // 삭제 로그 기록
        \Log::info('댓글 신고 삭제 완료', [
            'id' => $id,
            'deleted_by' => auth()->id(),
            'deleted_at' => now()
        ]);

        return true;
    }
}