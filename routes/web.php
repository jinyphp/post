<?php

use Illuminate\Support\Facades\Route;

/**
 * Board (게시판) 사용자 페이지 라우트
 *
 * @description
 * 사용자가 접근할 수 있는 게시판 기능을 제공합니다.
 */
Route::middleware('web')->prefix('board')->name('board.')->group(function () {
    // 게시판 목록
    Route::get('/', \Jiny\Post\Http\Controllers\Site\Board\IndexController::class)
        ->name('index');

    // 게시판 대시보드
    Route::get('/dashboard', \Jiny\Post\Http\Controllers\Site\Board\DashboardController::class)
        ->name('dashboard');

    // 게시글 작성
    Route::get('/create', \Jiny\Post\Http\Controllers\Site\Board\CreateController::class)
        ->name('create');
    Route::post('/create', \Jiny\Post\Http\Controllers\Site\Board\StoreController::class)
        ->name('store');

    // 게시글 보기
    Route::get('/{id}', \Jiny\Post\Http\Controllers\Site\Board\ShowController::class)
        ->name('show');

    // 게시글 수정
    Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Site\Board\EditController::class)
        ->name('edit');
    Route::put('/{id}', \Jiny\Post\Http\Controllers\Site\Board\UpdateController::class)
        ->name('update');

    // 게시글 삭제
    Route::delete('/{id}', \Jiny\Post\Http\Controllers\Site\Board\DestroyController::class)
        ->name('destroy');

    // 답글 작성
    Route::get('/{id}/reply', \Jiny\Post\Http\Controllers\Site\Board\CreateChildController::class)
        ->name('reply');

    // 댓글 관리
    Route::post('/{id}/comment', \Jiny\Post\Http\Controllers\Site\Board\StoreCommentController::class)
        ->name('comment.store');
    Route::put('/{id}/comment/{commentId}', \Jiny\Post\Http\Controllers\Site\Board\UpdateCommentController::class)
        ->name('comment.update');
    Route::delete('/{id}/comment/{commentId}', \Jiny\Post\Http\Controllers\Site\Board\DestroyCommentController::class)
        ->name('comment.destroy');

    // 평가 관리
    Route::post('/{id}/rating', \Jiny\Post\Http\Controllers\Site\Board\StoreRatingController::class)
        ->name('rating.store');
});

/**
 * Blog 라우트
 *
 * @description
 * 블로그 기능을 제공합니다.
 */
Route::middleware('web')->prefix('blog')->name('blog.')->group(function () {
    // Blog 관련 라우트는 필요에 따라 추가
    // Route::get('/', BlogIndexController::class)->name('index');
    // Route::get('/{slug}', BlogShowController::class)->name('show');
});

/**
 * Post 라우트
 *
 * @description
 * 일반적인 포스트 기능을 제공합니다.
 */
Route::middleware('web')->prefix('post')->name('post.')->group(function () {
    // Post 관련 라우트는 필요에 따라 추가
    // Route::get('/', PostIndexController::class)->name('index');
    // Route::get('/{slug}', PostShowController::class)->name('show');
});

/**
 * QNA 라우트
 *
 * @description
 * 질문과 답변 기능을 제공합니다.
 */
Route::middleware('web')->prefix('qna')->name('qna.')->group(function () {
    // QNA 관련 라우트는 필요에 따라 추가
    // Route::get('/', QnaIndexController::class)->name('index');
    // Route::get('/{id}', QnaShowController::class)->name('show');
});