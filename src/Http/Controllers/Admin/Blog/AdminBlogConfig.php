<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Jiny\Post\Services\BlogConfigManager;

/**
 * Admin Blog Config Helper
 *
 * BlogConfigManager를 래핑하여 Admin Blog 컨트롤러에서 쉽게 사용할 수 있도록 하는 헬퍼 클래스입니다.
 */
class AdminBlogConfig
{
    /**
     * BlogConfigManager 인스턴스
     */
    protected static $configManager = null;

    /**
     * BlogConfigManager 인스턴스 가져오기
     */
    protected static function getConfigManager()
    {
        if (self::$configManager === null) {
            self::$configManager = new BlogConfigManager();
        }

        return self::$configManager;
    }

    /**
     * 설정 값 가져오기
     */
    public static function getSettingValue($key, $default = null)
    {
        return self::getConfigManager()->getSetting($key, $default);
    }

    /**
     * 정책 값 가져오기
     */
    public static function getPolicyValue($key, $default = false)
    {
        return self::getConfigManager()->getPolicy($key, $default);
    }

    /**
     * 설정 값 설정하기
     */
    public static function setSettingValue($key, $value)
    {
        $manager = self::getConfigManager();
        $manager->setSetting($key, $value);
        $manager->save();

        return true;
    }

    /**
     * 정책 값 설정하기
     */
    public static function setPolicyValue($key, $enabled)
    {
        $manager = self::getConfigManager();
        $manager->setPolicy($key, $enabled);
        $manager->save();

        return true;
    }

    /**
     * 모든 설정 가져오기
     */
    public static function getAllSettings()
    {
        return self::getConfigManager()->get('settings', []);
    }

    /**
     * 모든 정책 가져오기
     */
    public static function getAllPolicies()
    {
        return self::getConfigManager()->get('policies', []);
    }

    /**
     * 전체 설정 가져오기
     */
    public static function getAll()
    {
        return self::getConfigManager()->all();
    }

    /**
     * 설정 초기화
     */
    public static function reset()
    {
        $manager = self::getConfigManager();
        $manager->reset();
        $manager->save();

        return true;
    }

    /**
     * 캐시 클리어
     */
    public static function clearCache()
    {
        self::$configManager = null;
        return true;
    }
}