<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 게시판 목록 표시
 */
class AdminBoardListIndex extends AdminBoardListBase
{
    public function __invoke(Request $request)
    {
        $query = DB::table($this->table);

        // 검색 기능
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('subtitle', 'LIKE', "%{$search}%");
            });
        }

        // 상태 필터
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('enable', 1);
            } elseif ($status === 'inactive') {
                $query->where('enable', 0);
            }
        }

        // 권한 필터 추가
        if ($permission = $request->get('permission')) {
            $query->where('write_permission', $permission);
        }

        $rows = $query->orderBy('created_at', 'desc')->paginate(15);

        // 검색 파라미터를 페이지네이션에 전달
        $rows->appends($request->query());

        // 각 게시판의 게시글 수와 총 조회수 계산
        foreach ($rows as $row) {
            if (isset($row->code)) {
                $tableName = "site_board_" . $row->code;

                if (Schema::hasTable($tableName)) {
                    // 게시글 수 계산
                    $row->post_count = DB::table($tableName)->count();

                    // DB에 저장된 총 조회수가 있으면 사용, 없으면 실시간 계산
                    if (isset($row->total_views) && $row->total_views > 0) {
                        // DB에 저장된 값 사용
                    } else {
                        // 실시간 계산 후 DB 업데이트
                        $calculatedViews = DB::table($tableName)->sum('click') ?? 0;
                        $row->total_views = $calculatedViews;

                        // DB에 업데이트
                        if ($calculatedViews > 0) {
                            DB::table($this->table)
                                ->where('code', $row->code)
                                ->update(['total_views' => $calculatedViews]);
                        }
                    }
                } else {
                    $row->post_count = 0;
                    $row->total_views = 0;
                }
            } else {
                $row->post_count = 0;
                $row->total_views = 0;
            }
        }

        return view("{$this->viewPath}.index", [
            'rows' => $rows,
            'config' => $this->config,
            'actions' => $this->config,
            'permissionOptions' => $this->permissionOptions,
        ]);
    }
}