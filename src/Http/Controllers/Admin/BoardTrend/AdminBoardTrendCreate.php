<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

/**
 * 트렌드글 생성 폼 표시
 */
class AdminBoardTrendCreate extends AdminBoardTrendBase
{
    public function __invoke()
    {
        return view("{$this->viewPath}.form", [
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }
}