<?php

namespace Jiny\Post\Services;

/**
 * Blog Configuration Manager
 *
 * 블로그 설정 관리를 위한 서비스 클래스입니다.
 */
class BlogConfigManager
{
    /**
     * 설정 파일 경로
     */
    protected $configPath;

    /**
     * 로드된 설정 데이터
     */
    protected $config = null;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->configPath = __DIR__ . '/../../config/blog.json';
    }

    /**
     * 설정 파일 로드
     */
    public function load()
    {
        if (file_exists($this->configPath)) {
            $content = file_get_contents($this->configPath);
            $this->config = json_decode($content, true);

            // JSON 파싱 실패 시 기본 설정 사용
            if (!is_array($this->config)) {
                $this->config = $this->getDefaultConfig();
            }
        } else {
            $this->config = $this->getDefaultConfig();
        }

        // 필수 키 구조 보장
        if (!isset($this->config['policies'])) {
            $this->config['policies'] = $this->getDefaultConfig()['policies'];
        }
        if (!isset($this->config['settings'])) {
            $this->config['settings'] = $this->getDefaultConfig()['settings'];
        }

        return $this->config;
    }

    /**
     * 설정 파일 저장
     */
    public function save(array $config = null)
    {
        if ($config !== null) {
            $this->config = $config;
        }

        if ($this->config === null) {
            throw new \Exception('설정 데이터가 없습니다. 먼저 load() 또는 set()을 호출하세요.');
        }

        // 업데이트 메타 정보 추가
        $this->config['updated_at'] = now()->toISOString();
        $user = auth()->user();
        $this->config['updated_by'] = $user ? $user->name : 'System';

        // JSON 파일에 저장
        $content = json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($this->configPath, $content) === false) {
            throw new \Exception('설정 파일 저장에 실패했습니다.');
        }

        // 캐시 클리어 - 다음번 load()에서 파일에서 새로 읽도록
        $this->clearCache();

        return true;
    }

    /**
     * 특정 설정 값 가져오기
     */
    public function get($key = null, $default = null)
    {
        if ($this->config === null) {
            $this->load();
        }

        if ($key === null) {
            return $this->config;
        }

        // 점 표기법 지원 (예: 'policies.admin_write.enabled')
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * 특정 설정 값 설정하기
     */
    public function set($key, $value)
    {
        if ($this->config === null) {
            $this->load();
        }

        // 점 표기법 지원 (예: 'policies.admin_write.enabled')
        $keys = explode('.', $key);
        $current = &$this->config;

        for ($i = 0; $i < count($keys) - 1; $i++) {
            $k = $keys[$i];
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current[end($keys)] = $value;

        return $this;
    }

    /**
     * 정책 값 가져오기
     */
    public function getPolicy($key, $default = false)
    {
        return $this->get("policies.{$key}.enabled", $default);
    }

    /**
     * 정책 값 설정하기
     */
    public function setPolicy($key, $enabled)
    {
        if ($this->config === null) {
            $this->load();
        }

        if (!isset($this->config['policies'][$key])) {
            $this->config['policies'][$key] = [
                'enabled' => false,
                'description' => $this->getPolicyDescription($key)
            ];
        }

        $this->config['policies'][$key]['enabled'] = $enabled;

        return $this;
    }

    /**
     * 설정 값 가져오기
     */
    public function getSetting($key, $default = null)
    {
        return $this->get("settings.{$key}", $default);
    }

    /**
     * 설정 값 설정하기
     */
    public function setSetting($key, $value)
    {
        return $this->set("settings.{$key}", $value);
    }

    /**
     * 여러 정책을 한번에 업데이트
     */
    public function updatePolicies(array $policies)
    {
        foreach ($policies as $key => $enabled) {
            $this->setPolicy($key, $enabled);
        }

        return $this;
    }

    /**
     * 여러 설정을 한번에 업데이트
     */
    public function updateSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($key, $value);
        }

        return $this;
    }

    /**
     * 설정 초기화
     */
    public function reset()
    {
        $this->config = $this->getDefaultConfig();
        return $this;
    }

    /**
     * 현재 로드된 설정 반환
     */
    public function all()
    {
        if ($this->config === null) {
            $this->load();
        }

        return $this->config;
    }

    /**
     * 캐시 클리어
     */
    public function clearCache()
    {
        $this->config = null;
        return $this;
    }

    /**
     * 기본 설정 반환
     */
    protected function getDefaultConfig()
    {
        return [
            'policies' => [
                'admin_write' => ['enabled' => true, 'description' => 'Admin blog write permission'],
                'user_write' => ['enabled' => false, 'description' => 'User blog write permission'],
                'guest_write' => ['enabled' => false, 'description' => 'Guest blog write permission'],
                'user_approval' => ['enabled' => true, 'description' => 'User post approval required'],
                'auto_approve_admin' => ['enabled' => true, 'description' => 'Auto approve admin posts'],
                'featured_admin_only' => ['enabled' => true, 'description' => 'Featured posts admin only'],
                'category_restriction' => ['enabled' => false, 'description' => 'Category restriction']
            ],
            'settings' => [
                'default_status' => 'draft',
                'max_images_per_post' => 5,
                'auto_excerpt_length' => 200,
                'enable_comments' => true,
                'comment_approval' => true,
                'seo_enabled' => true,
                'pagination_limit' => 10,
                'enable_tags' => true,
                'max_tags_per_post' => 10,
                'enable_search' => true,
                'enable_rss' => true
            ],
            'updated_at' => now()->toISOString(),
            'updated_by' => 'system'
        ];
    }

    /**
     * 정책 설명 반환
     */
    protected function getPolicyDescription($key)
    {
        $descriptions = [
            'admin_write' => 'Admin blog write permission',
            'user_write' => 'User blog write permission',
            'guest_write' => 'Guest blog write permission',
            'user_approval' => 'User post approval required',
            'auto_approve_admin' => 'Auto approve admin posts',
            'featured_admin_only' => 'Featured posts admin only',
            'category_restriction' => 'Category restriction'
        ];

        return $descriptions[$key] ?? 'Unknown policy';
    }
}