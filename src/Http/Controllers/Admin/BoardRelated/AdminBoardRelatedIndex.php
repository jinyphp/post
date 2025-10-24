<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관련글 목록 표시
 */
class AdminBoardRelatedIndex extends AdminBoardRelatedBase
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