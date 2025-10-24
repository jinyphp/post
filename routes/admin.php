<?php

use Illuminate\Support\Facades\Route;

/**
 * ====================================
 * CMS 관리자 라우트 계층 구조
 * ====================================
 *
 * admin/cms/
 * ├── board/          (게시판 시스템)
 * │   ├── dashboard   (대시보드)
 * │   ├── list/       (게시판 목록 관리)
 * │   ├── posts/      (게시글 관리)
 * │   ├── related/    (관련글 관리)
 * │   └── trend/      (트렌드글 관리)
 * ├── forum/          (포럼 시스템)
 * │   ├── config/     (포럼 설정)
 * │   ├── category/   (카테고리 관리)
 * │   └── posts/      (포럼 글 관리)
 * └── blog/           (블로그 시스템)
 *     ├── config/     (블로그 설정)
 *     ├── category/   (카테고리 관리)
 *     ├── posts/      (블로그 글 관리)
 *     └── comment/    (댓글 관리)
 */

// =====================================
// 공통 관리자 기능
// =====================================

// CSRF 토큰 새로고침 엔드포인트
Route::middleware(['admin'])->get('/admin/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

// =====================================
// CMS 관리자 라우트 그룹
// =====================================
Route::middleware(['admin'])->prefix('admin/cms')->name('admin.cms.')->group(function () {

    // =====================================
    // Board (게시판) 관리 시스템
    // =====================================
    Route::prefix('board')->name('board.')->group(function () {

        // 대시보드
        Route::get('/', \Jiny\Post\Http\Controllers\Admin\BoardDashboard\AdminSiteBoardDashboard::class)
            ->name('dashboard');

        // 게시판 목록 관리 (Single Action Controllers with Permission Management)
        Route::prefix('list')->name('list.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListIndex::class)
                ->name('index');
            Route::get('/create', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListCreate::class)
                ->name('create');
            Route::post('/', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListStore::class)
                ->name('store');
            Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListEdit::class)
                ->name('edit');
            Route::put('/{id}', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListUpdate::class)
                ->name('update');
            Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListDelete::class)
                ->name('destroy');

            // 권한 관리 기능
            Route::get('/permissions/{boardCode?}', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListPermission::class)
                ->name('permissions');
            Route::post('/permissions/bulk-update', [\Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListPermission::class, 'bulkUpdate'])
                ->name('permissions.bulk-update');
        });

        // 게시글 관리 (Unified Controllers: index, create, edit)
        Route::prefix('posts')->name('posts.')->group(function () {
            // List posts for a board
            Route::get('/{code}', \Jiny\Post\Http\Controllers\Admin\BoardPost\AdminBoardPostIndex::class)
                ->name('index');

            // Create new post or reply (handles both GET and POST)
            Route::match(['GET', 'POST'], '/{code}/create', \Jiny\Post\Http\Controllers\Admin\BoardPost\AdminBoardPostCreate::class)
                ->name('create');

            // Edit/Update/Delete post (handles GET, PUT, DELETE)
            Route::match(['GET', 'PUT', 'DELETE'], '/{code}/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BoardPost\AdminBoardPostEdit::class)
                ->name('edit');

            // Alternative routes for standard REST operations
            Route::post('/{code}', \Jiny\Post\Http\Controllers\Admin\BoardPost\AdminBoardPostCreate::class)
                ->name('store');
            Route::put('/{code}/{id}', \Jiny\Post\Http\Controllers\Admin\BoardPost\AdminBoardPostEdit::class)
                ->name('update');
            Route::delete('/{code}/{id}', \Jiny\Post\Http\Controllers\Admin\BoardPost\AdminBoardPostEdit::class)
                ->name('destroy');
        });

        // 관련글 관리 (Single Action Controllers)
        Route::prefix('related')->name('related.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\BoardRelated\AdminBoardRelatedIndex::class)
                ->name('index');
            Route::get('/create', \Jiny\Post\Http\Controllers\Admin\BoardRelated\AdminBoardRelatedCreate::class)
                ->name('create');
            Route::post('/', \Jiny\Post\Http\Controllers\Admin\BoardRelated\AdminBoardRelatedStore::class)
                ->name('store');
            Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BoardRelated\AdminBoardRelatedEdit::class)
                ->name('edit');
            Route::put('/{id}', \Jiny\Post\Http\Controllers\Admin\BoardRelated\AdminBoardRelatedUpdate::class)
                ->name('update');
            Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\BoardRelated\AdminBoardRelatedDelete::class)
                ->name('destroy');
        });

        // 트렌드글 관리 (Single Action Controllers)
        Route::prefix('trend')->name('trend.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\BoardTrend\AdminBoardTrendIndex::class)
                ->name('index');
            Route::get('/create', \Jiny\Post\Http\Controllers\Admin\BoardTrend\AdminBoardTrendCreate::class)
                ->name('create');
            Route::post('/', \Jiny\Post\Http\Controllers\Admin\BoardTrend\AdminBoardTrendStore::class)
                ->name('store');
            Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BoardTrend\AdminBoardTrendEdit::class)
                ->name('edit');
            Route::put('/{id}', \Jiny\Post\Http\Controllers\Admin\BoardTrend\AdminBoardTrendUpdate::class)
                ->name('update');
            Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\BoardTrend\AdminBoardTrendDelete::class)
                ->name('destroy');
        });

        // 댓글 신고 관리 (Comment Reports)
        Route::prefix('comment')->name('comment.')->group(function () {
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', \Jiny\Post\Http\Controllers\Admin\CommentReports\CommentReports::class)
                    ->name('index');
                Route::get('/{id}', \Jiny\Post\Http\Controllers\Admin\CommentReports\CommentReportsShow::class)
                    ->name('show');
                Route::post('/{id}/approve', \Jiny\Post\Http\Controllers\Admin\CommentReports\CommentReportsApprove::class)
                    ->name('approve');
                Route::post('/{id}/reject', \Jiny\Post\Http\Controllers\Admin\CommentReports\CommentReportsReject::class)
                    ->name('reject');
                Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\CommentReports\CommentReportsDelete::class)
                    ->name('delete');
            });
        });

        // 기타 기능 (MigrateRatingTableController 통합)
        Route::post('/migrate-rating-tables', \Jiny\Post\Http\Controllers\Admin\BoardList\AdminBoardListMigrateRating::class)
            ->name('migrate.rating.tables');
    });

    // =====================================
    // Forum (포럼) 관리 시스템
    // =====================================
    Route::prefix('forum')->name('forum.')->group(function () {

        // 포럼 메인 페이지
        Route::get('/', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumIndex::class)
            ->name('index');

        // 포럼 설정 관리
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\ForumConfig\AdminForumConfigIndex::class)
                ->name('index');
            Route::match(['POST', 'PUT'], '/', \Jiny\Post\Http\Controllers\Admin\ForumConfig\AdminForumConfigUpdate::class)
                ->name('update');
        });

        // 포럼 카테고리 관리
        Route::prefix('category')->name('category.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryIndex::class)
                ->name('index');
            Route::get('/create', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryCreate::class)
                ->name('create');
            Route::post('/', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryCreate::class)
                ->name('store');
            Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryEdit::class)
                ->name('edit')->where('id', '[0-9]+');
            Route::match(['POST', 'PUT'], '/{id}', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryEdit::class)
                ->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryDelete::class)
                ->name('destroy')->where('id', '[0-9]+');
            Route::post('/order', \Jiny\Post\Http\Controllers\Admin\ForumCategory\AdminForumCategoryOrder::class)
                ->name('order');
        });

        // 포럼 글 관리 (CRUD)
        Route::get('/create', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumCreate::class)
            ->name('create');
        Route::post('/', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumStore::class)
            ->name('store');
        Route::get('/{id}', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumShow::class)
            ->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumEdit::class)
            ->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumUpdate::class)
            ->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\Forum\AdminForumDelete::class)
            ->name('destroy')->where('id', '[0-9]+');
    });

    // =====================================
    // Blog (블로그) 관리 시스템
    // =====================================
    Route::prefix('blog')->name('blog.')->group(function () {

        // 블로그 메인 페이지
        Route::get('/', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogIndex::class)
            ->name('index');

        // 블로그 설정 관리
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\BlogConfig\AdminBlogConfigIndex::class)
                ->name('index');
            Route::match(['POST', 'PUT'], '/', \Jiny\Post\Http\Controllers\Admin\BlogConfig\AdminBlogConfigUpdate::class)
                ->name('update');
        });

        // 블로그 카테고리 관리
        Route::prefix('category')->name('category.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryIndex::class)
                ->name('index');
            Route::get('/create', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryCreate::class)
                ->name('create');
            Route::post('/', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryCreate::class)
                ->name('store');
            Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryEdit::class)
                ->name('edit')->where('id', '[0-9]+');
            Route::match(['POST', 'PUT'], '/{id}', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryEdit::class)
                ->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryDelete::class)
                ->name('destroy')->where('id', '[0-9]+');
            Route::post('/order', \Jiny\Post\Http\Controllers\Admin\BlogCategory\AdminBlogCategoryOrder::class)
                ->name('order');
        });

        // 블로그 댓글 관리
        Route::prefix('comment')->name('comment.')->group(function () {
            Route::get('/', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentIndex::class)
                ->name('index');
            Route::post('/store', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentStore::class)
                ->name('store');
            Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentEdit::class)
                ->name('edit')->where('id', '[0-9]+');
            Route::post('/{id}/update', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentUpdate::class)
                ->name('update')->where('id', '[0-9]+');
            Route::post('/{id}/approve', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentApprove::class)
                ->name('approve')->where('id', '[0-9]+');
            Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\BlogComment\AdminBlogCommentDelete::class)
                ->name('destroy')->where('id', '[0-9]+');
        });

        // 블로그 글 관리 (CRUD) - 가장 마지막에 정의하여 {id} 충돌 방지
        Route::get('/create', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogCreate::class)
            ->name('create');
        Route::post('/', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogStore::class)
            ->name('store');
        Route::get('/{id}', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogShow::class)
            ->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogEdit::class)
            ->name('edit')->where('id', '[0-9]+');
        Route::match(['POST', 'PUT'], '/{id}/edit', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogUpdate::class)
            ->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogDelete::class)
            ->name('destroy')->where('id', '[0-9]+');
    });
});

// =====================================
// 프론트엔드 공개 라우트
// =====================================

/**
 * 댓글 제출 (프론트엔드용 - 인증 불필요)
 */
Route::post('/blog/{id}/comment', \Jiny\Post\Http\Controllers\BlogCommentStore::class)
    ->name('blog.comment.store')->where('id', '[0-9]+');
