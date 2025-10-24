<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 트렌드글 데이터 수정
 */
class AdminBoardTrendUpdate extends AdminBoardTrendBase
{
    public function __invoke(Request $request, $id)
    {
        $data = $request->except(['_token', '_method']);
        $data['updated_at'] = now();

        // 코드는 변경이 불가능합니다.
        unset($data['code']);

        DB::table($this->table)
            ->where('id', $id)
            ->update($data);

        return redirect()->route('admin.cms.board.trend.index')
            ->with('success', '트렌드글이 수정되었습니다.');
    }
}