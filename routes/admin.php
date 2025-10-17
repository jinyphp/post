<?php

use Illuminate\Support\Facades\Route;

/**
 * Board (게시판) 관리 라우트
 *
 * @description
 * 게시판 시스템을 관리하는 라우트입니다.
 * 게시판 설정, 게시글, 관련글, 트렌드글 등을 관리합니다.
 */
Route::prefix('admin/cms/board')->name('admin.cms.board.')->group(function () {
    // 대시보드
    Route::get('/', \Jiny\Post\Http\Controllers\Admin\Board\AdminSiteBoardDashboard::class)
        ->name('dashboard');

    // 게시판 목록 - RESTful 라우트
    Route::get('/list', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoard::class, 'index'])
        ->name('list');
    Route::get('/list/create', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoard::class, 'create'])
        ->name('list.create');
    Route::post('/list', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoard::class, 'store'])
        ->name('list.store');
    Route::get('/list/{id}/edit', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoard::class, 'edit'])
        ->name('list.edit');
    Route::put('/list/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoard::class, 'update'])
        ->name('list.update');
    Route::delete('/list/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoard::class, 'destroy'])
        ->name('list.destroy');

    // 게시글 관리 - RESTful 라우트
    Route::get('/table', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTable::class, 'index'])
        ->name('table');
    Route::get('/table/create', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTable::class, 'create'])
        ->name('table.create');
    Route::post('/table', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTable::class, 'store'])
        ->name('table.store');
    Route::get('/table/{id}/edit', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTable::class, 'edit'])
        ->name('table.edit');
    Route::put('/table/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTable::class, 'update'])
        ->name('table.update');
    Route::delete('/table/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTable::class, 'destroy'])
        ->name('table.destroy');

    // 관련글 관리 - RESTful 라우트
    Route::get('/related', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardRelated::class, 'index'])
        ->name('related');
    Route::get('/related/create', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardRelated::class, 'create'])
        ->name('related.create');
    Route::post('/related', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardRelated::class, 'store'])
        ->name('related.store');
    Route::get('/related/{id}/edit', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardRelated::class, 'edit'])
        ->name('related.edit');
    Route::put('/related/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardRelated::class, 'update'])
        ->name('related.update');
    Route::delete('/related/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardRelated::class, 'destroy'])
        ->name('related.destroy');

    // 트렌드글 관리 - RESTful 라우트
    Route::get('/trend', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTrend::class, 'index'])
        ->name('trend');
    Route::get('/trend/create', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTrend::class, 'create'])
        ->name('trend.create');
    Route::post('/trend', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTrend::class, 'store'])
        ->name('trend.store');
    Route::get('/trend/{id}/edit', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTrend::class, 'edit'])
        ->name('trend.edit');
    Route::put('/trend/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTrend::class, 'update'])
        ->name('trend.update');
    Route::delete('/trend/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardTrend::class, 'destroy'])
        ->name('trend.destroy');

    // 게시판별 게시글 관리 - RESTful 라우트
    Route::get('/posts/{code}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'index'])
        ->name('posts');
    Route::get('/posts/{code}/create', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'create'])
        ->name('posts.create');
    Route::post('/posts/{code}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'store'])
        ->name('posts.store');
    Route::get('/posts/{code}/{id}/edit', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'edit'])
        ->name('posts.edit');
    Route::put('/posts/{code}/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'update'])
        ->name('posts.update');
    Route::delete('/posts/{code}/{id}', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'destroy'])
        ->name('posts.destroy');
    // 하위 글 작성
    Route::get('/posts/{code}/{id}/child/create', [\Jiny\Post\Http\Controllers\Admin\Board\AdminBoardPost::class, 'createChild'])
        ->name('posts.child.create');

    // 평가 테이블 마이그레이션
    Route::post('/migrate-rating-tables', \Jiny\Post\Http\Controllers\Admin\Board\MigrateRatingTableController::class)
        ->name('migrate.rating.tables');
});