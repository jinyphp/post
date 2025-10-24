<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관련글 데이터 저장
 */
class AdminBoardRelatedStore extends AdminBoardRelatedBase
{
    public function __invoke(Request $request)
    {
        $data = $request->except(['_token', '_method']);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table($this->table)->insert($data);

        return redirect()->route('admin.cms.board.related.index')
            ->with('success', '관련글이 생성되었습니다.');
    }
}