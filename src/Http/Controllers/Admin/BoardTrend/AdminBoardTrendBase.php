<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardTrend;

use Illuminate\Routing\Controller;

/**
 * Base class for AdminBoardTrend controllers
 *
 * 트렌드글 관련 공통 기능을 제공하는 베이스 클래스입니다.
 */
abstract class AdminBoardTrendBase extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_board_trend';

    /**
     * 뷰 경로
     */
    protected $viewPath = 'jiny-post::admin.board_trend';

    /**
     * 페이지 설정
     */
    protected $config = [
        'title' => '트렌드글',
        'subtitle' => '계시물과 연관된 트렌드글을 관리합니다.',
    ];
}