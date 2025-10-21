<?php

use Illuminate\Support\Facades\Route;

// CSRF 토큰 새로고침 엔드포인트
Route::middleware(['admin'])->get('/admin/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

/**
 * Board (게시판) 관리 라우트
 *
 * @description
 * 다중 게시판 시스템을 관리하는 라우트입니다.
 * 게시판 설정, 게시글, 관련글, 트렌드글 등을 관리합니다.
 */
Route::middleware(['admin'])->prefix('admin/cms/board')->name('admin.cms.board.')->group(function () {
    // 대시보드
    Route::get('/', \Jiny\Post\Http\Controllers\Admin\Board\AdminSiteBoardDashboard::class)
        ->name('dashboard');

    // 게시판 목록 - RESTful 라우트 (다중 게시판 관리)
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

/**
 * Forum (포럼) 관리 라우트
 *
 * @description
 * 단일 포럼 시스템을 관리하는 라우트입니다.
 * 포럼 글 작성, 수정, 삭제 등을 관리합니다.
 */
Route::middleware(['admin'])->prefix('admin/cms/forum')->name('admin.cms.forum')->group(function () {

    // 포럼 설정 (JSON 기반) - Single Action Controllers (/{id} 라우트보다 먼저 정의)
    Route::get('/config', \Jiny\Post\Http\Controllers\Admin\ForumConfig\AdminForumConfigIndex::class)
        ->name('.config');
    Route::match(['POST', 'PUT'], '/config', \Jiny\Post\Http\Controllers\Admin\ForumConfig\AdminForumConfigUpdate::class)
        ->name('.config.update');

    // 포럼 글 목록 - Single Action Controllers (단일 포럼 관리)
    Route::get('/', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumIndex::class)
        ->name('');
    Route::match(['GET', 'POST'], '/create', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumCreate::class)
        ->name('.create');

    // 포럼 카테고리 관리 - Single Action Controllers (/{id} 라우트보다 먼저 정의)
    Route::get('/category', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryIndex::class)
        ->name('.category');
    Route::get('/category/create', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryCreate::class)
        ->name('.category.create');
    Route::post('/category', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryCreate::class)
        ->name('.category.store');
    Route::get('/category/{id}/edit', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryEdit::class)
        ->name('.category.edit')->where('id', '[0-9]+');
    Route::match(['POST', 'PUT'], '/category/{id}', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryEdit::class)
        ->name('.category.update')->where('id', '[0-9]+');
    Route::delete('/category/{id}', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryDelete::class)
        ->name('.category.destroy')->where('id', '[0-9]+');
    Route::post('/category/order', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryOrder::class)
        ->name('.category.order');

    // 포럼 글 개별 조작 라우트 - Single Action Controllers (가장 마지막에 정의 - {id} 매개변수가 다른 경로와 충돌하지 않도록)
    Route::match(['GET', 'POST', 'PUT'], '/{id}/edit', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumEdit::class)
        ->name('.edit')->where('id', '[0-9]+');
    Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumDelete::class)
        ->name('.destroy')->where('id', '[0-9]+');
});


/**
 * Blog (블로그) 관리 라우트
 *
 * @description
 * 블로그 시스템을 관리하는 라우트입니다.
 * Single Action Controller 방식으로 구현되어 있습니다.
 * 블로그 글 작성, 수정, 삭제 및 카테고리 관리를 제공합니다.
 */
Route::middleware(['admin'])->prefix('admin/cms/blog')->name('admin.cms.blog')->group(function () {
    // 블로그 글 목록
    Route::get('/', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogIndex::class)
        ->name('');

    // 블로그 설정 (JSON 기반) - Single Action Controllers
    Route::get('/config', \Jiny\Post\Http\Controllers\Admin\BlogConfig\AdminBlogConfigIndex::class)
        ->name('.config');
    Route::match(['POST', 'PUT'], '/config', \Jiny\Post\Http\Controllers\Admin\BlogConfig\AdminBlogConfigUpdate::class)
        ->name('.config.update');

    // 블로그 글 생성
    Route::match(['GET', 'POST'], '/create', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogCreate::class)
        ->name('.create');

    // 일괄 작업 (/{id} 라우트보다 먼저 정의)
    // Route::post('/bulk/status', [\Jiny\Post\Http\Controllers\Admin\Blog\AdminBlog::class, 'bulkStatus'])
    //     ->name('.bulk.status');

    // 블로그 카테고리 관리 - Single Action Controllers (/{id} 라우트보다 먼저 정의)
    Route::get('/category', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryIndex::class)
        ->name('.category');
    Route::get('/category/create', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryCreate::class)
        ->name('.category.create');
    Route::post('/category', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryCreate::class)
        ->name('.category.store');
    Route::get('/category/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryEdit::class)
        ->name('.category.edit')->where('id', '[0-9]+');
    Route::match(['POST', 'PUT'], '/category/{id}', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryEdit::class)
        ->name('.category.update')->where('id', '[0-9]+');
    Route::delete('/category/{id}', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryDelete::class)
        ->name('.category.destroy')->where('id', '[0-9]+');
    Route::post('/category/order', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryOrder::class)
        ->name('.category.order');

    // 블로그 글 개별 조작 라우트 (가장 마지막에 정의 - {id} 매개변수가 다른 경로와 충돌하지 않도록)
    Route::get('/{id}', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogShow::class)
        ->name('.show')->where('id', '[0-9]+');
    Route::match(['GET', 'POST', 'PUT'], '/{id}/edit', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogEdit::class)
        ->name('.edit')->where('id', '[0-9]+');
    Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogDelete::class)
        ->name('.destroy')->where('id', '[0-9]+');

    // 블로그 댓글 관리
    Route::get('/comment', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentIndex::class)
        ->name('.comment');
    Route::post('/comment/store', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentStore::class)
        ->name('.comment.store');
    Route::get('/comment/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentEdit::class)
        ->name('.comment.edit')->where('id', '[0-9]+');
    Route::post('/comment/{id}/update', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentUpdate::class)
        ->name('.comment.update')->where('id', '[0-9]+');
    Route::post('/comment/{id}/approve', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentApprove::class)
        ->name('.comment.approve')->where('id', '[0-9]+');
    Route::delete('/comment/{id}', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentDelete::class)
        ->name('.comment.destroy')->where('id', '[0-9]+');
});

/**
 * 댓글 제출 (프론트엔드용 - 인증 불필요)
 */
Route::post('/blog/{id}/comment', \Jiny\Post\Http\Controllers\BlogCommentStore::class)
    ->name('blog.comment.store')->where('id', '[0-9]+');
