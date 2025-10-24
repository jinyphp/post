<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 트렌드글 데이터 저장
 */
class AdminBoardTrendStore extends AdminBoardTrendBase
{
    public function __invoke(Request $request)
    {
        $data = $request->except(['_token', '_method']);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table($this->table)->insert($data);

        return redirect()->route('admin.cms.board.trend.index')
            ->with('success', '트렌드글이 생성되었습니다.');
    }
}