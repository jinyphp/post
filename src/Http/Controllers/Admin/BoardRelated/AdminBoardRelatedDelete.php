<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

use Illuminate\Support\Facades\DB;

/**
 * 관련글 데이터 삭제
 */
class AdminBoardRelatedDelete extends AdminBoardRelatedBase
{
    public function __invoke($id)
    {
        DB::table($this->table)->where('id', $id)->delete();

        return redirect()->route('admin.cms.board.related.index')
            ->with('success', '관련글이 삭제되었습니다.');
    }
}