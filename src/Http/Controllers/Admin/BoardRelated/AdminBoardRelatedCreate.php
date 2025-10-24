<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

/**
 * 관련글 생성 폼 표시
 */
class AdminBoardRelatedCreate extends AdminBoardRelatedBase
{
    public function __invoke()
    {
        return view("{$this->viewPath}.form", [
            'config' => $this->config,
            'actions' => $this->config,
        ]);
    }
}