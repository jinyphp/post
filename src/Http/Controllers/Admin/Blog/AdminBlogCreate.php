<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Jiny\Post\Http\Controllers\Admin\Blog\BlogPolicyHelper;
use Jiny\Post\Http\Controllers\Admin\Blog\AdminBlogConfig;

/**
 * Single Action Controller for Blog Create
 *
 * 블로그 생성을 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogCreate extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 생성 처리 (GET: 폼 표시, POST: 저장 처리)
     */
    public function __invoke(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->showCreateForm($request);
        }

        return $this->store($request);
    }

    /**
     * 생성 폼 표시
     */
    protected function showCreateForm(Request $request)
    {
        try {
            // 정책 확인 - 블로그 작성 권한 체크
            $user = auth()->user();
            if (!BlogPolicyHelper::canUserWriteBlog($user)) {
                return redirect()->route('admin.cms.blog')
                    ->with('error', '블로그 작성 권한이 없습니다. 정책 설정을 확인해주세요.');
            }

            // 카테고리 목록
            $categories = DB::table('site_blog_cate')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // JSON 설정 값 가져오기
            $config = [
                'default_status' => AdminBlogConfig::getSettingValue('default_status', 'draft'),
                'max_images_per_post' => AdminBlogConfig::getSettingValue('max_images_per_post', 10),
                'auto_excerpt_length' => AdminBlogConfig::getSettingValue('auto_excerpt_length', 200),
                'enable_comments' => AdminBlogConfig::getSettingValue('enable_comments', true),
                'seo_enabled' => AdminBlogConfig::getSettingValue('seo_enabled', true),
            ];

            return view('jiny-post::admin.blog.create', compact('categories', 'config'));

        } catch (\Exception $e) {
            \Log::error('Blog create form error', ['error' => $e->getMessage()]);

            return redirect()->route('admin.cms.blog')
                ->with('error', '블로그 생성 폼을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 블로그 저장 처리
     */
    protected function store(Request $request)
    {
        try {
            \Log::info('Blog create started', ['request_data' => $request->all()]);

            // 정책 확인 - 블로그 작성 권한 체크
            $user = auth()->user();
            if (!BlogPolicyHelper::canUserWriteBlog($user)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '블로그 작성 권한이 없습니다. 정책 설정을 확인해주세요.'
                    ], 403);
                }

                return redirect()->route('admin.cms.blog')
                    ->with('error', '블로그 작성 권한이 없습니다. 정책 설정을 확인해주세요.');
            }

            // JSON 설정에서 최대 이미지 수 가져오기
            $maxImages = AdminBlogConfig::getSettingValue('max_images_per_post', 10);
            $maxImageSizeKB = $maxImages * 1024; // 이미지당 1MB 기준

            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'status' => 'required|in:draft,published,scheduled,pending',
                'slug' => 'nullable|unique:site_blog,slug',
                'featured_image_file' => "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxImageSizeKB}",
            ]);

            $data = $request->except(['_token', '_method', 'featured_image_file', 'action']);

            // JSON 설정에서 기본 상태 가져오기
            $defaultStatus = AdminBlogConfig::getSettingValue('default_status', 'draft');

            // 초안 저장 액션 처리
            if ($request->input('action') === 'draft') {
                $data['status'] = 'draft';
            } else if (!isset($data['status']) || empty($data['status'])) {
                // 상태가 지정되지 않은 경우 기본값 사용
                $data['status'] = $defaultStatus;
            }

            // 정책에 따른 상태 조정
            if ($data['status'] === 'published' && !BlogPolicyHelper::isAutoApproved($user)) {
                $data['status'] = 'pending'; // 승인 대기로 변경
            }

            // 이미지 업로드 처리
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');

                // 계층화된 경로 생성 (년/월/일)
                $date = now();
                $datePath = $date->format('Y/m/d');
                $fileName = $date->format('His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs("blog/images/{$datePath}", $fileName, 'public');
                $data['featured_image'] = Storage::url($path);
            }

            // 슬러그 생성
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // 슬러그 중복 체크
            if (!empty($data['slug'])) {
                $originalSlug = $data['slug'];
                $counter = 1;
                while (DB::table($this->table)->where('slug', $data['slug'])->exists()) {
                    $data['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // 체크박스 처리 - JSON 설정 반영
            $enableCommentsDefault = AdminBlogConfig::getSettingValue('enable_comments', true);

            // allow_comments가 명시적으로 설정되지 않은 경우 JSON 설정의 기본값 사용
            if ($request->has('allow_comments')) {
                $data['allow_comments'] = 1;
            } else if (!$request->has('_method') || $request->method() === 'POST') {
                // 새 글 작성 시에만 기본값 적용
                $data['allow_comments'] = $enableCommentsDefault ? 1 : 0;
            } else {
                $data['allow_comments'] = 0;
            }

            $data['is_featured'] = $request->has('is_featured') && BlogPolicyHelper::canSetFeatured($user) ? 1 : 0;
            $data['is_sticky'] = $request->has('is_sticky') ? 1 : 0;

            // 발행 시간 설정
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            } elseif ($data['status'] === 'scheduled' && !empty($data['scheduled_at'])) {
                $data['scheduled_at'] = Carbon::parse($data['scheduled_at']);
            }

            // 요약 자동 생성 (없을 경우) - JSON 설정에서 길이 가져오기
            if (empty($data['excerpt']) && !empty($data['content'])) {
                $excerptLength = AdminBlogConfig::getSettingValue('auto_excerpt_length', 200);
                $data['excerpt'] = Str::limit(strip_tags($data['content']), $excerptLength);
            }

            // SEO 메타 정보 자동 생성 - JSON 설정에 따라 조건부 처리
            $seoEnabled = AdminBlogConfig::getSettingValue('seo_enabled', true);
            if ($seoEnabled) {
                if (empty($data['meta_title'])) {
                    $data['meta_title'] = $data['title'];
                }
                if (empty($data['meta_description'])) {
                    $data['meta_description'] = $data['excerpt'];
                }
            }

            // 작성자 정보 설정
            $user = auth()->user();
            if ($user) {
                $data['user_id'] = $user->id;
                $data['author_name'] = $user->name;
                $data['author_email'] = $user->email;
            }

            // 타임스탬프 설정
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $id = DB::table($this->table)->insertGetId($data);

            \Log::info('Blog created successfully', ['id' => $id, 'title' => $data['title']]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 글이 생성되었습니다.',
                    'redirect' => route('admin.cms.blog')
                ]);
            }

            return redirect()->route('admin.cms.blog')
                ->with('success', '블로그 글이 생성되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Blog create validation error', ['errors' => $e->errors()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '입력 데이터에 오류가 있습니다.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            \Log::error('Blog create general error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '시스템 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', '시스템 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}