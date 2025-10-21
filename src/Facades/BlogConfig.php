<?php

namespace Jiny\Post\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Blog Config Facade
 *
 * @method static array load() 설정 파일 로드
 * @method static bool save(array $config = null) 설정 파일 저장
 * @method static mixed get(string $key = null, mixed $default = null) 설정 값 가져오기
 * @method static \Jiny\Post\Services\BlogConfigManager set(string $key, mixed $value) 설정 값 설정
 * @method static bool getPolicy(string $key, bool $default = false) 정책 값 가져오기
 * @method static \Jiny\Post\Services\BlogConfigManager setPolicy(string $key, bool $enabled) 정책 값 설정
 * @method static mixed getSetting(string $key, mixed $default = null) 설정 값 가져오기
 * @method static \Jiny\Post\Services\BlogConfigManager setSetting(string $key, mixed $value) 설정 값 설정
 * @method static \Jiny\Post\Services\BlogConfigManager updatePolicies(array $policies) 여러 정책 업데이트
 * @method static \Jiny\Post\Services\BlogConfigManager updateSettings(array $settings) 여러 설정 업데이트
 * @method static \Jiny\Post\Services\BlogConfigManager reset() 설정 초기화
 * @method static array all() 모든 설정 반환
 *
 * @see \Jiny\Post\Services\BlogConfigManager
 */
class BlogConfig extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'blog.config';
    }
}