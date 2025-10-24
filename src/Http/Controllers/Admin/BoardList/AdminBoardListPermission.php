<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 게시판 권한 관리
 */
class AdminBoardListPermission extends AdminBoardListBase
{
    public function __invoke(Request $request, $boardCode = null)
    {
        // 특정 게시판의 권한 정보 조회
        if ($boardCode) {
            $board = DB::table($this->table)->where('code', $boardCode)->first();

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'error' => '게시판을 찾을 수 없습니다.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'board' => $board,
                'permissions' => [
                    'write_permission' => $board->write_permission ?? 'guest_allowed',
                    'read_permission' => $board->read_permission ?? 'guest_allowed',
                    'comment_permission' => $board->comment_permission ?? 'guest_allowed',
                ],
                'permission_options' => $this->permissionOptions,
            ]);
        }

        // 모든 게시판의 권한 통계
        $permissionStats = DB::table($this->table)
            ->select(
                'write_permission',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('write_permission')
            ->get();

        $stats = [];
        foreach ($this->permissionOptions as $key => $label) {
            $stats[$key] = [
                'label' => $label,
                'count' => $permissionStats->where('write_permission', $key)->first()->count ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'permission_stats' => $stats,
            'permission_options' => $this->permissionOptions,
        ]);
    }

    /**
     * 게시판 권한 일괄 업데이트
     */
    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'board_ids' => 'required|array',
            'board_ids.*' => 'integer|exists:site_board,id',
            'write_permission' => 'required|in:admin_only,member_only,guest_allowed',
            'read_permission' => 'required|in:admin_only,member_only,guest_allowed',
            'comment_permission' => 'required|in:admin_only,member_only,guest_allowed',
        ]);

        try {
            $updated = DB::table($this->table)
                ->whereIn('id', $data['board_ids'])
                ->update([
                    'write_permission' => $data['write_permission'],
                    'read_permission' => $data['read_permission'],
                    'comment_permission' => $data['comment_permission'],
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => $updated . '개 게시판의 권한이 업데이트되었습니다.',
                'updated_count' => $updated,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '권한 업데이트 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}