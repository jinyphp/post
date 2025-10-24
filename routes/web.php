<?php

use Illuminate\Support\Facades\Route;

/**
 * Board (게시판) 사용자 페이지 라우트
 *
 * @description
 * 사용자가 접근할 수 있는 게시판 기능을 제공합니다.
 */
Route::middleware('web')->prefix('board')->name('board.')->group(function () {
    // 게시판 대시보드 (루트 경로)
    Route::get('/', \Jiny\Post\Http\Controllers\Site\Board\DashboardController::class)
        ->name('dashboard');

    // 게시판 목록
    Route::get('/{code}', \Jiny\Post\Http\Controllers\Site\BoardPost\IndexController::class)
        ->name('index');

    // 게시글 작성
    Route::get('/{code}/create', \Jiny\Post\Http\Controllers\Site\BoardPost\CreateController::class)
        ->name('create');
    Route::post('/{code}', \Jiny\Post\Http\Controllers\Site\BoardPost\StoreController::class)
        ->name('store');

    // 게시글 보기
    Route::get('/{code}/{id}', \Jiny\Post\Http\Controllers\Site\BoardPost\ShowController::class)
        ->name('show');

    // 게시글 수정
    Route::get('/{code}/{id}/edit', \Jiny\Post\Http\Controllers\Site\BoardPost\EditController::class)
        ->name('edit');
    Route::put('/{code}/{id}', \Jiny\Post\Http\Controllers\Site\BoardPost\UpdateController::class)
        ->name('update');

    // 게시글 삭제
    Route::delete('/{code}/{id}', \Jiny\Post\Http\Controllers\Site\BoardPost\DestroyController::class)
        ->name('destroy');

    // 답글 작성
    Route::get('/{code}/{id}/reply', \Jiny\Post\Http\Controllers\Site\BoardPost\CreateController::class)
        ->name('reply');

    // 댓글 관리는 Livewire 컴포넌트로 처리됨

    // 평가 관리
    Route::post('/{code}/{id}/rating', \Jiny\Post\Http\Controllers\Site\BoardPost\StoreRatingController::class)
        ->name('rating.store');

    // 파일 다운로드
    Route::get('/{code}/{id}/download/{attachmentId}', \Jiny\Post\Http\Controllers\Site\BoardPost\DownloadController::class)
        ->name('download')
        ->where(['id' => '[0-9]+', 'attachmentId' => '[0-9]+']);
});

/**
 * Blog 라우트 - Single Action Controller (SAC) 방식
 *
 * @description
 * 블로그 기능을 제공합니다. (SAC 패턴 적용)
 */
Route::middleware('web')->prefix('blog')->name('blog.')->group(function () {
    // 블로그 목록 페이지
    Route::get('/', \Jiny\Post\Http\Controllers\Site\Blog\BlogIndex::class)
        ->name('index');

    // 블로그 글 작성 폼
    Route::get('/create', \Jiny\Post\Http\Controllers\Site\Blog\BlogCreate::class)
        ->name('create');

    // 블로그 글 저장
    Route::post('/', \Jiny\Post\Http\Controllers\Site\Blog\BlogStore::class)
        ->name('store');

    // 카테고리별 블로그 목록
    Route::get('/category/{slug}', \Jiny\Post\Http\Controllers\Site\Blog\BlogCategory::class)
        ->name('category');

    // 블로그 상세 페이지
    Route::get('/{slug}', \Jiny\Post\Http\Controllers\Site\Blog\BlogShow::class)
        ->name('show')
        ->where('slug', '[a-zA-Z0-9\-_]+');

    // 블로그 글 수정 폼
    Route::get('/{slug}/edit', \Jiny\Post\Http\Controllers\Site\Blog\BlogEdit::class)
        ->name('edit')
        ->where('slug', '[a-zA-Z0-9\-_]+');

    // 블로그 글 수정 처리
    Route::put('/{slug}', \Jiny\Post\Http\Controllers\Site\Blog\BlogUpdate::class)
        ->name('update')
        ->where('slug', '[a-zA-Z0-9\-_]+');

    // 블로그 글 삭제
    Route::delete('/{slug}', \Jiny\Post\Http\Controllers\Site\Blog\BlogDelete::class)
        ->name('destroy')
        ->where('slug', '[a-zA-Z0-9\-_]+');

    // 좋아요 기능 (AJAX)
    Route::post('/{slug}/like', \Jiny\Post\Http\Controllers\Site\Blog\BlogLike::class)
        ->name('like')
        ->where('slug', '[a-zA-Z0-9\-_]+');

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
