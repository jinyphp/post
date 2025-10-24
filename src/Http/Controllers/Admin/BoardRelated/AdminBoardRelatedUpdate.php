<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관련글 데이터 수정
 */
class AdminBoardRelatedUpdate extends AdminBoardRelatedBase
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

        return redirect()->route('admin.cms.board.related.index')
            ->with('success', '관련글이 수정되었습니다.');
    }
}