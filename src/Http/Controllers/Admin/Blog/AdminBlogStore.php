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
 * Single Action Controller for Blog Store
 *
 * 블로그 저장 처리를 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogStore extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 저장 처리
     */
    public function __invoke(Request $request)
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
            $maxImageSizeKB = 5120; // 5MB per image

            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'status' => 'required|in:draft,published,scheduled,pending',
                'slug' => 'nullable|unique:site_blog,slug',
                'featured_image_file' => "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxImageSizeKB}",
                'images.*' => "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxImageSizeKB}",
                'images' => "array|max:{$maxImages}",
            ]);

            $data = $request->except(['_token', '_method', 'featured_image_file', 'images', 'action']);

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

            // 대표 이미지 업로드 처리
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');

                // 계층화된 경로 생성 (년/월/일)
                $date = now();
                $datePath = $date->format('Y/m/d');
                $fileName = $date->format('His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs("blog/images/{$datePath}", $fileName, 'public');
                $data['featured_image'] = Storage::url($path);
            }

            // 다중 이미지 업로드 처리
            $uploadedImages = [];
            if ($request->hasFile('images')) {
                $uploadedImages = $this->handleImageUploads($request->file('images'), $maxImages);

                // 대표 이미지가 없고 다중 이미지가 있는 경우 첫 번째 이미지를 대표 이미지로 설정
                if (empty($data['featured_image']) && !empty($uploadedImages)) {
                    $data['featured_image'] = $uploadedImages[0]['url'] ?? null;
                }
            }

            // 슬러그 생성 및 처리
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = Str::slug($data['title']);

                // slug가 빈 문자열이면 기본값 생성 (한글/특수문자 제목 대응)
                if (empty($data['slug'])) {
                    $data['slug'] = 'blog-' . date('YmdHis') . '-' . Str::random(6);
                }
            }

            // 슬러그가 여전히 비어있으면 기본값 설정
            if (empty($data['slug'])) {
                $data['slug'] = 'blog-' . date('YmdHis') . '-' . Str::random(6);
            }

            // 슬러그 중복 체크 및 해결
            $originalSlug = $data['slug'];
            $counter = 1;
            while (DB::table($this->table)->where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
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

            // 다중 이미지를 별도 테이블에 저장
            if (!empty($uploadedImages)) {
                $this->saveBlogImages($id, $uploadedImages);
            }

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

    /**
     * 다중 이미지 업로드 처리
     */
    protected function handleImageUploads($images, $maxCount = 10)
    {
        $uploadedImages = [];
        $count = 0;

        foreach ($images as $index => $image) {
            if ($count >= $maxCount) {
                break; // 설정된 최대 개수 초과 시 중단
            }
            try {
                // 계층화된 경로 생성: blog/YYYY/MM/DD/HH/
                $now = now();
                $hierarchicalPath = sprintf(
                    'blog/%04d/%02d/%02d/%02d',
                    $now->year,
                    $now->month,
                    $now->day,
                    $now->hour
                );

                // UUID 기반 파일명 생성
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

                // 저장 경로
                $path = $image->storeAs($hierarchicalPath, $filename, 'public');

                if ($path) {
                    $uploadedImages[] = [
                        'filename' => $filename,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'uploaded_at' => now()->toISOString(),
                    ];

                    \Log::info('Admin Blog image uploaded successfully:', [
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $image->getSize(),
                    ]);
                    $count++; // 성공한 경우에만 카운트 증가
                }
            } catch (\Exception $e) {
                \Log::error('Admin Blog image upload failed:', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        return $uploadedImages;
    }

    /**
     * 블로그 이미지들을 별도 테이블에 저장
     */
    protected function saveBlogImages($blogId, $uploadedImages)
    {
        $imageData = [];

        foreach ($uploadedImages as $index => $image) {
            $imageData[] = [
                'blog_id' => $blogId,
                'filename' => $image['filename'],
                'original_name' => $image['original_name'],
                'path' => $image['path'],
                'url' => $image['url'],
                'size' => $image['size'],
                'mime_type' => $image['mime_type'],
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($imageData)) {
            DB::table('site_blog_images')->insert($imageData);
            \Log::info('Admin Blog images saved:', [
                'blog_id' => $blogId,
                'image_count' => count($imageData)
            ]);
        }
    }
}