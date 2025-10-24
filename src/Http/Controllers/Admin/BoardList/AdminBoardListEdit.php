<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Support\Facades\DB;

/**
 * 게시판 수정 폼 표시
 */
class AdminBoardListEdit extends AdminBoardListBase
{
    public function __invoke($id)
    {
        $item = DB::table($this->table)->find($id);

        if (!$item) {
            return redirect()->route('admin.cms.board.list.index')
                ->with('error', '게시판을 찾을 수 없습니다.');
        }

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'config' => $this->config,
            'actions' => $this->config,
            'permissionOptions' => $this->permissionOptions,
        ]);
    }
}