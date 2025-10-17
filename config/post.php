<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jiny Post Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Jiny Post package
    | which provides board, blog, post, and QNA functionality.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Package Settings
    |--------------------------------------------------------------------------
    */
    'enable' => env('JINY_POST_ENABLE', true),

    /*
    |--------------------------------------------------------------------------
    | Board Settings
    |--------------------------------------------------------------------------
    */
    'board' => [
        'enable' => env('JINY_BOARD_ENABLE', true),
        'pagination' => env('JINY_BOARD_PAGINATION', 15),
        'allow_anonymous' => env('JINY_BOARD_ALLOW_ANONYMOUS', true),
        'require_approval' => env('JINY_BOARD_REQUIRE_APPROVAL', false),
        'enable_comments' => env('JINY_BOARD_ENABLE_COMMENTS', true),
        'enable_ratings' => env('JINY_BOARD_ENABLE_RATINGS', true),
        'enable_file_upload' => env('JINY_BOARD_ENABLE_FILE_UPLOAD', true),
        'max_file_size' => env('JINY_BOARD_MAX_FILE_SIZE', 10240), // KB
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Settings
    |--------------------------------------------------------------------------
    */
    'blog' => [
        'enable' => env('JINY_BLOG_ENABLE', true),
        'pagination' => env('JINY_BLOG_PAGINATION', 10),
        'allow_comments' => env('JINY_BLOG_ALLOW_COMMENTS', true),
        'require_approval' => env('JINY_BLOG_REQUIRE_APPROVAL', false),
        'enable_seo' => env('JINY_BLOG_ENABLE_SEO', true),
        'enable_tags' => env('JINY_BLOG_ENABLE_TAGS', true),
        'enable_categories' => env('JINY_BLOG_ENABLE_CATEGORIES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Post Settings
    |--------------------------------------------------------------------------
    */
    'post' => [
        'enable' => env('JINY_POST_GENERAL_ENABLE', true),
        'pagination' => env('JINY_POST_PAGINATION', 15),
        'allow_anonymous' => env('JINY_POST_ALLOW_ANONYMOUS', false),
        'require_approval' => env('JINY_POST_REQUIRE_APPROVAL', true),
        'enable_voting' => env('JINY_POST_ENABLE_VOTING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | QNA Settings
    |--------------------------------------------------------------------------
    */
    'qna' => [
        'enable' => env('JINY_QNA_ENABLE', true),
        'pagination' => env('JINY_QNA_PAGINATION', 15),
        'allow_anonymous' => env('JINY_QNA_ALLOW_ANONYMOUS', true),
        'require_approval' => env('JINY_QNA_REQUIRE_APPROVAL', false),
        'enable_best_answer' => env('JINY_QNA_ENABLE_BEST_ANSWER', true),
        'enable_voting' => env('JINY_QNA_ENABLE_VOTING', true),
        'auto_close_days' => env('JINY_QNA_AUTO_CLOSE_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'enable_csrf' => env('JINY_POST_ENABLE_CSRF', true),
        'enable_honeypot' => env('JINY_POST_ENABLE_HONEYPOT', true),
        'enable_rate_limiting' => env('JINY_POST_ENABLE_RATE_LIMITING', true),
        'rate_limit' => env('JINY_POST_RATE_LIMIT', '10,1'), // 10 requests per minute
        'enable_word_filter' => env('JINY_POST_ENABLE_WORD_FILTER', true),
        'banned_words' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enable' => env('JINY_POST_CACHE_ENABLE', true),
        'ttl' => env('JINY_POST_CACHE_TTL', 3600), // 1 hour
        'tags' => ['jiny-post', 'board', 'blog', 'qna'],
    ],

    /*
    |--------------------------------------------------------------------------
    | View Settings
    |--------------------------------------------------------------------------
    */
    'views' => [
        'theme' => env('JINY_POST_THEME', 'default'),
        'layout' => env('JINY_POST_LAYOUT', 'app'),
        'per_page_options' => [10, 15, 20, 25, 50],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'enable' => env('JINY_POST_NOTIFICATIONS_ENABLE', true),
        'new_post' => env('JINY_POST_NOTIFY_NEW_POST', true),
        'new_comment' => env('JINY_POST_NOTIFY_NEW_COMMENT', true),
        'new_reply' => env('JINY_POST_NOTIFY_NEW_REPLY', true),
        'post_approved' => env('JINY_POST_NOTIFY_POST_APPROVED', true),
    ],
];