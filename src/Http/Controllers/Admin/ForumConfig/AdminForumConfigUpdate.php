<?php

namespace Jiny\Post\Http\Controllers\Admin\ForumConfig;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Post\Facades\ForumConfig;

/**
 * Admin Forum Config Update Controller
 *
 * 포럼 설정 저장을 처리하는 단일 액션 컨트롤러입니다.
 */
class AdminForumConfigUpdate extends Controller
{
    /**
     * 포럼 설정 저장 처리
     */
    public function __invoke(Request $request)
    {
        \Log::info('Forum config save started', ['request_data' => $request->all()]);

        $request->validate([
            'max_images_per_post' => 'nullable|integer|min:1|max:50',
            'auto_excerpt_length' => 'nullable|integer|min:50|max:500',
            'max_tags_per_post' => 'nullable|integer|min:1|max:10',
            'pagination_limit' => 'nullable|integer|min:5|max:50',
            'max_file_size_mb' => 'nullable|integer|min:1|max:20',
        ]);

        try {
            // ForumConfig Facade를 사용하여 설정 로드
            ForumConfig::clearCache();
            $currentConfig = ForumConfig::load();
            \Log::info('Forum config loaded', ['config' => $currentConfig]);

            // 정책 설정 업데이트 (체크박스 값 올바르게 처리)
            $policies = [
                'admin_write' => $request->input('admin_write') === '1' || $request->input('admin_write') === 'on',
                'user_write' => $request->input('user_write') === '1' || $request->input('user_write') === 'on',
                'guest_write' => $request->input('guest_write') === '1' || $request->input('guest_write') === 'on',
                'user_approval' => $request->input('user_approval') === '1' || $request->input('user_approval') === 'on',
                'auto_approve_admin' => $request->input('auto_approve_admin') === '1' || $request->input('auto_approve_admin') === 'on',
                'category_restriction' => $request->input('category_restriction') === '1' || $request->input('category_restriction') === 'on',
                'allow_anonymous' => $request->input('allow_anonymous') === '1' || $request->input('allow_anonymous') === 'on',
            ];

            \Log::info('Forum policies to update', ['policies' => $policies]);
            ForumConfig::updatePolicies($policies);

            // 설정 값 업데이트 (체크박스 값 올바르게 처리)
            $settings = [
                'default_status' => $request->input('default_status', 'published'),
                'max_images_per_post' => (int)$request->input('max_images_per_post', 10),
                'auto_excerpt_length' => (int)$request->input('auto_excerpt_length', 150),
                'enable_comments' => $request->input('enable_comments') === '1' || $request->input('enable_comments') === 'on',
                'comment_approval' => $request->input('comment_approval') === '1' || $request->input('comment_approval') === 'on',
                'enable_voting' => $request->input('enable_voting') === '1' || $request->input('enable_voting') === 'on',
                'enable_tags' => $request->input('enable_tags') === '1' || $request->input('enable_tags') === 'on',
                'max_tags_per_post' => (int)$request->input('max_tags_per_post', 5),
                'pagination_limit' => (int)$request->input('pagination_limit', 15),
                'enable_search' => $request->input('enable_search') === '1' || $request->input('enable_search') === 'on',
                'enable_file_upload' => $request->input('enable_file_upload') === '1' || $request->input('enable_file_upload') === 'on',
                'max_file_size_mb' => (int)$request->input('max_file_size_mb', 5),
            ];

            \Log::info('Forum settings to update', ['settings' => $settings]);
            ForumConfig::updateSettings($settings);

            // 설정 저장
            $result = ForumConfig::save();
            \Log::info('Forum config save result', ['result' => $result]);

            // 저장 후 다시 로드해서 확인
            $savedConfig = ForumConfig::load();
            \Log::info('Forum config after save', ['config' => $savedConfig]);

            \Log::info('Forum config saved successfully');

            return redirect()->route('admin.cms.forum.config')
                ->with('success', '포럼 설정이 저장되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Forum config save error', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', '설정 저장에 실패했습니다: ' . $e->getMessage());
        }
    }
}