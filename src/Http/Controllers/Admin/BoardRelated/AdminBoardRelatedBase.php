<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardRelated;

use Illuminate\Routing\Controller;

/**
 * Base class for AdminBoardRelated controllers
 *
 * 관련글 관련 공통 기능을 제공하는 베이스 클래스입니다.
 */
abstract class AdminBoardRelatedBase extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_board_related';

    /**
     * 뷰 경로
     */
    protected $viewPath = 'jiny-post::admin.board_related';

    /**
     * 페이지 설정
     */
    protected $config = [
        'title' => '관련글',
        'subtitle' => '계시물과 연관된 관련글을 관리합니다.',
    ];
}