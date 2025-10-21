<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Support\Facades\DB;

/**
 * Blog Policy Helper
 *
 * 블로그 정책 관련 헬퍼 함수들을 제공합니다.
 */
class BlogPolicyHelper
{
    /**
     * 기본 정책 설정 초기화
     */
    public static function initializeDefaultPolicies()
    {
        $defaultPolicies = [
            'blog_policy_admin_write' => [
                'value' => 'true',
                'description' => '관리자 블로그 작성 허용'
            ],
            'blog_policy_user_write' => [
                'value' => 'false',
                'description' => '일반 사용자 블로그 작성 허용'
            ],
            'blog_policy_guest_write' => [
                'value' => 'false',
                'description' => '비회원 블로그 작성 허용'
            ],
            'blog_policy_user_approval' => [
                'value' => 'true',
                'description' => '사용자 글 승인 필요'
            ],
            'blog_policy_auto_approve_admin' => [
                'value' => 'true',
                'description' => '관리자 글 자동 승인'
            ],
            'blog_policy_featured_admin_only' => [
                'value' => 'true',
                'description' => '추천글은 관리자만'
            ],
            'blog_policy_category_restriction' => [
                'value' => 'false',
                'description' => '카테고리 제한'
            ]
        ];

        foreach ($defaultPolicies as $key => $config) {
            DB::table('site_config')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $config['value'],
                    'description' => $config['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }

    /**
     * 특정 정책 값 조회
     */
    public static function getPolicyValue($key, $default = 'false')
    {
        // JSON 설정에서 정책 값 조회
        $policyKey = str_replace('blog_policy_', '', $key);
        $enabled = \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue($policyKey, false);

        return $enabled ? 'true' : 'false';
    }

    /**
     * 사용자가 블로그를 작성할 수 있는지 확인
     */
    public static function canUserWriteBlog($user = null)
    {
        // 관리자인 경우
        if ($user && $user->isAdmin) {
            return \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue('admin_write', true);
        }

        // 로그인한 일반 사용자인 경우
        if ($user) {
            return \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue('user_write', false);
        }

        // 비회원인 경우
        return \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue('guest_write', false);
    }

    /**
     * 사용자 글이 자동 승인되는지 확인
     */
    public static function isAutoApproved($user = null)
    {
        // 관리자인 경우
        if ($user && $user->isAdmin) {
            return \Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue('auto_approve_admin', true);
        }

        // 일반 사용자는 승인 필요 여부에 따라 결정
        return !\Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue('user_approval', true);
    }

    /**
     * 추천글 설정이 가능한지 확인
     */
    public static function canSetFeatured($user = null)
    {
        if (\Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig::getPolicyValue('featured_admin_only', true)) {
            return $user && $user->isAdmin;
        }

        return true;
    }
}