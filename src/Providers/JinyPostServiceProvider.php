<?php

namespace Jiny\Post\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

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

        // Register Blade components
        $this->loadBladeComponents();

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
     * Load Blade components
     */
    protected function loadBladeComponents(): void
    {
        Blade::componentNamespace('Jiny\\Post\\View\\Components', 'jiny-post');

        // Register individual components
        Blade::component('jiny-post::components.switch', 'switch');
        Blade::component('jiny-post::components.btn-save', 'btn-save');
        Blade::component('jiny-post::components.input-number', 'input-number');
        Blade::component('jiny-post::components.help', 'help');
        Blade::component('jiny-post::components.help-title', 'help-title');
        Blade::component('jiny-post::components.content', 'content');
        Blade::component('jiny-post::components.content-main', 'content-main');
        Blade::component('jiny-post::components.content-side', 'content-side');
        Blade::component('jiny-post::components.form-post', 'form-post');
        Blade::component('jiny-post::components.form-put', 'form-put');
        Blade::component('jiny-post::components.form-patch', 'form-patch');
        Blade::component('jiny-post::components.form-delete', 'form-delete');
        Blade::component('jiny-post::components.card', 'card');
        Blade::component('jiny-post::components.card-header', 'card-header');
        Blade::component('jiny-post::components.card-body', 'card-body');
        Blade::component('jiny-post::components.alert-success', 'alert-success');
        Blade::component('jiny-post::components.alert-danger', 'alert-danger');
        Blade::component('jiny-post::components.input-text', 'input-text');
        Blade::component('jiny-post::components.textarea', 'textarea');
        Blade::component('jiny-post::components.btn-create', 'btn-create');
        Blade::component('jiny-post::components.btn-back-to-list', 'btn-back-to-list');
        Blade::component('jiny-post::components.btn-delete', 'btn-delete');
        Blade::component('jiny-post::components.btn-reset', 'btn-reset');
        Blade::component('jiny-post::components.heading', 'heading');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}