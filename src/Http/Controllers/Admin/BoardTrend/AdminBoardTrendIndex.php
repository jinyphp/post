<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 트렌드글 목록 표시
 */
class AdminBoardTrendIndex extends AdminBoardTrendBase
{
    public function __invoke(Request $request)
    {
        $rows = DB::table($this->table)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view("{$this->viewPath}.list", [
            'rows' => $rows,
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }
}