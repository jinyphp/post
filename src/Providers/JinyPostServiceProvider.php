<?php

namespace Jiny\Post\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class JinyPostServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load package views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'jiny-post');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../../databases/migrations');

        // Load package routes
        $this->loadRoutes();

        // Livewire 컴포넌트 등록 (AppServiceProvider에서 처리됨)
        // Livewire::component('jiny-post::board-comment', \Jiny\Post\Http\Livewire\BoardComment::class);

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/post.php' => config_path('post.php'),
            ], 'jiny-post-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/jiny-post'),
            ], 'jiny-post-views');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../../databases/migrations' => database_path('migrations'),
            ], 'jiny-post-migrations');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/post.php', 'post');

        // Register ForumConfig service
        $this->app->singleton('forum.config', function ($app) {
            return new \Jiny\Post\Services\ForumConfigManager();
        });

        // Register ForumConfig alias
        $this->app->alias('forum.config', \Jiny\Post\Services\ForumConfigManager::class);

        // Register BlogConfig service
        $this->app->singleton('blog.config', function ($app) {
            return new \Jiny\Post\Services\BlogConfigManager();
        });

        // Register BlogConfig alias
        $this->app->alias('blog.config', \Jiny\Post\Services\BlogConfigManager::class);

    }

    /**
     * Load package routes
     */
    protected function loadRoutes(): void
    {
        // Load admin routes with admin middleware
        Route::middleware(['web', 'admin'])
            ->group(__DIR__.'/../../routes/admin.php');

        // Load web routes
        Route::middleware(['web'])
            ->group(__DIR__.'/../../routes/web.php');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
