<?php

namespace Jiny\Post\Http\Controllers\Admin\BlogConfig;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Post\Facades\BlogConfig;

/**
 * Admin Blog Config Update Controller
 *
 * 블로그 설정 저장을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogConfigUpdate extends Controller
{
    /**
     * 블로그 설정 저장 처리
     */
    public function __invoke(Request $request)
    {
        \Log::info('Blog config save started', ['request_data' => $request->all()]);

        $request->validate([
            'max_images_per_post' => 'nullable|integer|min:1|max:20',
            'auto_excerpt_length' => 'nullable|integer|min:50|max:500',
        ]);

        try {
            // BlogConfig Facade를 사용하여 설정 로드
            $currentConfig = BlogConfig::load();
            \Log::info('Blog config loaded', ['config' => $currentConfig]);

            // 정책 설정 업데이트 (체크박스 값 올바르게 처리)
            $policies = [
                'admin_write' => $request->input('admin_write') === '1' || $request->input('admin_write') === 'on',
                'user_write' => $request->input('user_write') === '1' || $request->input('user_write') === 'on',
                'guest_write' => $request->input('guest_write') === '1' || $request->input('guest_write') === 'on',
                'user_approval' => $request->input('user_approval') === '1' || $request->input('user_approval') === 'on',
                'auto_approve_admin' => $request->input('auto_approve_admin') === '1' || $request->input('auto_approve_admin') === 'on',
                'featured_admin_only' => $request->input('featured_admin_only') === '1' || $request->input('featured_admin_only') === 'on',
                'category_restriction' => $request->input('category_restriction') === '1' || $request->input('category_restriction') === 'on',
            ];

            \Log::info('Blog policies to update', ['policies' => $policies]);
            BlogConfig::updatePolicies($policies);

            // 설정 값 업데이트 (체크박스 값 올바르게 처리)
            $settings = [
                'default_status' => $request->input('default_status', 'draft'),
                'max_images_per_post' => (int)$request->input('max_images_per_post', 5),
                'auto_excerpt_length' => (int)$request->input('auto_excerpt_length', 200),
                'enable_comments' => $request->input('enable_comments') === '1' || $request->input('enable_comments') === 'on',
                'comment_approval' => $request->input('comment_approval') === '1' || $request->input('comment_approval') === 'on',
                'seo_enabled' => $request->input('seo_enabled') === '1' || $request->input('seo_enabled') === 'on',
            ];

            \Log::info('Blog settings to update', ['settings' => $settings]);
            BlogConfig::updateSettings($settings);

            // 설정 저장
            $result = BlogConfig::save();
            \Log::info('Blog config save result', ['result' => $result]);

            // 저장 후 다시 로드해서 확인
            $savedConfig = BlogConfig::load();
            \Log::info('Blog config after save', ['config' => $savedConfig]);

            \Log::info('Blog config saved successfully');

            return redirect()->route('admin.cms.blog.config')
                ->with('success', '블로그 설정이 저장되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Blog config save error', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', '설정 저장에 실패했습니다: ' . $e->getMessage());
        }
    }
}