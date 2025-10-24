<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

use Illuminate\Support\Facades\DB;

/**
 * 관련글 수정 폼 표시
 */
class AdminBoardRelatedEdit extends AdminBoardRelatedBase
{
    public function __invoke($id)
    {
        $item = DB::table($this->table)->find($id);

        return view("{$this->viewPath}.form", [
            'item' => $item,
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }
}