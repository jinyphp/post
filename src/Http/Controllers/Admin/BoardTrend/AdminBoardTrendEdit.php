<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

use Illuminate\Support\Facades\DB;

/**
 * 트렌드글 수정 폼 표시
 */
class AdminBoardTrendEdit extends AdminBoardTrendBase
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