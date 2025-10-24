<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

use Illuminate\Support\Facades\DB;

/**
 * 트렌드글 데이터 삭제
 */
class AdminBoardTrendDelete extends AdminBoardTrendBase
{
    public function __invoke($id)
    {
        DB::table($this->table)->where('id', $id)->delete();

        return redirect()->route('admin.cms.board.trend.index')
            ->with('success', '트렌드글이 삭제되었습니다.');
    }
}