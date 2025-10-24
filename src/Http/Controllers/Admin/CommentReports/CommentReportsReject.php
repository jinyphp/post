<?php

namespace Jiny\Post\Http\Controllers\Admin\CommentReports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CommentReports 거부 컨트롤러
 *
 * 댓글 신고를 거부하는 기능을 제공합니다.
 *
 * @package Jiny\Post\Http\Controllers\Admin\CommentReports
 * @since   1.0.0
 */
class CommentReportsReject extends Controller
{
    /**
     * CommentReports 거부 처리
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  거부할 신고 ID
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

            if ($report->status !== 'pending') {
                session()->flash('error', '대기중인 신고만 처리할 수 있습니다.');
                return redirect()->route('admin.cms.board.comment.reports.index');
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

            session()->flash('success', '신고가 거부되었습니다.');
            return redirect()->route('admin.cms.board.comment.reports.index');

        } catch (\Exception $e) {
            \Log::error('댓글 신고 거부 실패', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', '신고 거부 실패: ' . $e->getMessage());
            return redirect()->route('admin.cms.board.comment.reports.index');
        }
    }
}