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
    Route::get('/{code}', \Jiny\Post\Http\Controllers\Site\Board\IndexController::class)
        ->name('index');

    // 게시판 대시보드
    Route::get('/dashboard', \Jiny\Post\Http\Controllers\Site\Board\DashboardController::class)
        ->name('dashboard');

    // 게시글 작성
    Route::get('/{code}/create', \Jiny\Post\Http\Controllers\Site\Board\CreateController::class)
        ->name('create');
    Route::post('/{code}/create', \Jiny\Post\Http\Controllers\Site\Board\StoreController::class)
        ->name('store');

    // 게시글 보기
    Route::get('/{code}/{id}', \Jiny\Post\Http\Controllers\Site\Board\ShowController::class)
        ->name('show');

    // 게시글 수정
    Route::get('/{code}/{id}/edit', \Jiny\Post\Http\Controllers\Site\Board\EditController::class)
        ->name('edit');
    Route::put('/{code}/{id}', \Jiny\Post\Http\Controllers\Site\Board\UpdateController::class)
        ->name('update');

    // 게시글 삭제
    Route::delete('/{code}/{id}', \Jiny\Post\Http\Controllers\Site\Board\DestroyController::class)
        ->name('destroy');

    // 답글 작성
    Route::get('/{code}/{id}/reply', \Jiny\Post\Http\Controllers\Site\Board\CreateChildController::class)
        ->name('reply');

    // 댓글 관리
    Route::post('/{code}/{id}/comment', \Jiny\Post\Http\Controllers\Site\Board\StoreCommentController::class)
        ->name('comment.store');
    Route::put('/{code}/{id}/comment/{commentId}', \Jiny\Post\Http\Controllers\Site\Board\UpdateCommentController::class)
        ->name('comment.update');
    Route::delete('/{code}/{id}/comment/{commentId}', \Jiny\Post\Http\Controllers\Site\Board\DestroyCommentController::class)
        ->name('comment.destroy');

    // 평가 관리
    Route::post('/{code}/{id}/rating', \Jiny\Post\Http\Controllers\Site\Board\StoreRatingController::class)
        ->name('rating.store');
});

/**
 * Blog 라우트
 *
 * @description
 * 블로그 기능을 제공합니다.
 */
Route::middleware('web')->prefix('blog')->name('blog.')->group(function () {
    // 블로그 목록 페이지
    Route::get('/', [\Jiny\Post\Http\Controllers\Site\Blog\BlogController::class, 'index'])
        ->name('index');

    // 카테고리별 블로그 목록
    Route::get('/category/{slug}', [\Jiny\Post\Http\Controllers\Site\Blog\BlogController::class, 'category'])
        ->name('category');

    // 블로그 상세 페이지
    Route::get('/{slug}', [\Jiny\Post\Http\Controllers\Site\Blog\BlogController::class, 'show'])
        ->name('show');

    // 좋아요 기능 (AJAX)
    Route::post('/{slug}/like', [\Jiny\Post\Http\Controllers\Site\Blog\BlogController::class, 'like'])
        ->name('like');

    // 댓글 등록 (AJAX)
    Route::post('/comment', \Jiny\Post\Http\Controllers\BlogCommentStore::class)
        ->name('comment.store');
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

/**
 * Forum 라우트
 *
 * @description
 * 단일 포럼 기능을 제공합니다.
 */
Route::middleware('web')->group(function () {
    // 포럼 목록 페이지
    Route::get('/forum', \Jiny\Post\Http\Controllers\Site\Forum\Forum::class)
        ->name('forum.index');

    // 포럼 글 작성 폼
    Route::get('/forum/create', \Jiny\Post\Http\Controllers\Site\Forum\ForumCreate::class)
        ->name('forum.create');

    // 포럼 글 저장
    Route::post('/forum', \Jiny\Post\Http\Controllers\Site\Forum\ForumStore::class)
        ->name('forum.store');

    // 포럼 상세 페이지
    Route::get('/forum/{id}', \Jiny\Post\Http\Controllers\Site\Forum\ForumShow::class)
        ->name('forum.show')
        ->where('id', '[0-9]+');

    // 포럼 글 수정 폼
    Route::get('/forum/{id}/edit', \Jiny\Post\Http\Controllers\Site\Forum\ForumEdit::class)
        ->name('forum.edit')
        ->where('id', '[0-9]+');

    // 포럼 글 수정 처리
    Route::put('/forum/{id}', \Jiny\Post\Http\Controllers\Site\Forum\ForumUpdate::class)
        ->name('forum.update')
        ->where('id', '[0-9]+');

    // 포럼 글 삭제
    Route::delete('/forum/{id}', \Jiny\Post\Http\Controllers\Site\Forum\ForumDelete::class)
        ->name('forum.destroy')
        ->where('id', '[0-9]+');

    // 포럼 좋아요 AJAX
    Route::post('/forum/{id}/like', \Jiny\Post\Http\Controllers\Site\Forum\ForumLike::class)
        ->name('forum.like')
        ->where('id', '[0-9]+');
});