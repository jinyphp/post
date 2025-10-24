<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 게시판 데이터 삭제
 */
class AdminBoardListDelete extends AdminBoardListBase
{
    public function __invoke($id)
    {
        $row = DB::table($this->table)->find($id);

        if (!$row) {
            return redirect()->route('admin.cms.board.list.index')
                ->with('error', '게시판을 찾을 수 없습니다.');
        }

        if ($row && isset($row->code)) {
            // 연결된 게시판 테이블과 코멘트 테이블, 평가 테이블 삭제
            Schema::dropIfExists("site_board_" . $row->code);
            Schema::dropIfExists("site_board_" . $row->code . "_comments");
            Schema::dropIfExists("site_board_" . $row->code . "_ratings");
        }

        DB::table($this->table)->where('id', $id)->delete();

        return redirect()->route('admin.cms.board.list.index')
            ->with('success', '게시판이 삭제되었습니다.');
    }
}