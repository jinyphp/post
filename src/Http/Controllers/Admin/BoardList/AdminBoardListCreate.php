<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

/**
 * 게시판 생성 폼 표시
 */
class AdminBoardListCreate extends AdminBoardListBase
{
    public function __invoke()
    {
        return view("{$this->viewPath}.create", [
            'config' => $this->config,
            'actions' => $this->config,
            'permissionOptions' => $this->permissionOptions,
        ]);
    }
}