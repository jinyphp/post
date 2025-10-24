<?php
namespace Jiny\Post\Http\Controllers\Site\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Blog Store Controller
 *
 * 블로그 글 저장을 처리하는 컨트롤러입니다.
 */
class BlogStore extends Controller
{
    /**
     * 블로그 글 저장
     */
    public function __invoke(Request $request)
    {
        $table = 'site_blog';

        if (!Schema::hasTable($table)) {
            abort(404, '블로그 테이블이 존재하지 않습니다.');
        }

        // 인증된 사용자인지 확인
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '로그인이 필요합니다.',
                ], 401);
            } else {
                return redirect()->route('login')
                    ->with('error', '로그인이 필요합니다.');
            }
        }

        // 동적 검증 규칙 생성
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_slug' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'images' => 'array|max:10', // 최대 10개의 이미지
            'status' => 'nullable|in:draft,published',
            'allow_comments' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_sticky' => 'nullable|boolean',
            'excerpt' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ];

        $messages = [
            'title.required' => '제목을 입력해주세요.',
            'title.max' => '제목은 255자를 초과할 수 없습니다.',
            'content.required' => '내용을 입력해주세요.',
            'category_slug.max' => '카테고리는 255자를 초과할 수 없습니다.',
            'tags.max' => '태그는 255자를 초과할 수 없습니다.',
            'images.*.image' => '이미지 파일만 업로드 가능합니다.',
            'images.*.mimes' => '지원되는 이미지 형식: jpeg, png, jpg, gif, webp',
            'images.*.max' => '이미지 파일 크기는 5MB를 초과할 수 없습니다.',
            'images.max' => '최대 10개의 이미지만 업로드할 수 있습니다.',
            'excerpt.max' => '요약은 500자를 초과할 수 없습니다.',
            'meta_title.max' => 'SEO 제목은 255자를 초과할 수 없습니다.',
            'meta_description.max' => 'SEO 설명은 500자를 초과할 수 없습니다.',
        ];

        // 입력값 검증
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '입력값이 올바르지 않습니다.',
                    'errors' => $validator->errors(),
                ], 422);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // 현재 사용자 정보 가져오기
            $user = Auth::user();

            // 슬러그 생성
            $slug = $this->generateUniqueSlug($request->title);

            // 기본 데이터 준비
            $data = [
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'excerpt' => $request->excerpt ?? Str::limit(strip_tags($request->content), 200),
                'status' => $request->status ?? 'draft',
                'user_id' => $user->id,
                'author_name' => $user->name ?? 'Anonymous',
                'author_email' => $user->email ?? null,
                'category_slug' => $request->category_slug,
                'tags' => $request->tags,
                'allow_comments' => $request->has('allow_comments') ? 1 : 0,
                'is_featured' => $request->has('is_featured') ? 1 : 0,
                'is_sticky' => $request->has('is_sticky') ? 1 : 0,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'views' => 0,
                'likes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 발행 상태인 경우 발행일 설정
            if ($data['status'] === 'published') {
                $data['published_at'] = now();
            }

            // UUID 추가 (컬럼이 존재하는 경우)
            if (Schema::hasColumn($table, 'uuid')) {
                $data['uuid'] = Str::uuid();
            }

            // 사용자 UUID 추가 (컬럼이 존재하는 경우)
            if (Schema::hasColumn($table, 'user_uuid') && isset($user->uuid)) {
                $data['user_uuid'] = $user->uuid;
            }

            // IP 주소 저장 (컬럼이 존재하는 경우)
            if (Schema::hasColumn($table, 'ip_address')) {
                $data['ip_address'] = $request->ip();
            }

            // 이미지 업로드 처리
            $uploadedImages = [];
            if ($request->hasFile('images')) {
                $uploadedImages = $this->handleImageUploads($request->file('images'), 10);

                if (!empty($uploadedImages)) {
                    // 첫 번째 이미지를 대표 이미지로 설정
                    $data['featured_image'] = $uploadedImages[0]['url'] ?? null;
                }
            }

            \Log::info('Blog store data:', $data);

            // 데이터베이스에 저장
            $blogId = DB::table($table)->insertGetId($data);

            // 다중 이미지를 별도 테이블에 저장
            if (!empty($uploadedImages)) {
                // 이미지를 temp에서 실제 경로로 이동
                $uploadedImages = $this->moveImagesToFinalPath($uploadedImages, $blogId);
                $this->saveBlogImages($blogId, $uploadedImages);
            }

            DB::commit();

            \Log::info('Blog created successfully:', ['blog_id' => $blogId]);

            // 성공 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 글이 성공적으로 작성되었습니다.',
                    'blog_id' => $blogId,
                    'slug' => $slug,
                ]);
            } else {
                return redirect()->route('blog.show', $slug)
                    ->with('success', '블로그 글이 성공적으로 작성되었습니다.');
            }

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Blog store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
                'request_data' => $request->all(),
                'table' => $table,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 글 작성 중 오류가 발생했습니다: ' . $e->getMessage(),
                    'debug' => config('app.debug') ? $e->getTraceAsString() : null,
                ], 500);
            } else {
                $errorMessage = config('app.debug')
                    ? '블로그 글 작성 중 오류가 발생했습니다: ' . $e->getMessage()
                    : '블로그 글 작성 중 오류가 발생했습니다.';

                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }
        }
    }

    /**
     * 고유한 슬러그 생성
     */
    protected function generateUniqueSlug($title)
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (DB::table('site_blog')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * 이미지 업로드 처리 (Forum과 동일한 구조)
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
                // 계층화된 경로 생성: blog/{year}/{month}/{day}/temp/
                // 나중에 실제 blogId로 이동됩니다
                $now = now();
                $hierarchicalPath = "blog/{$now->year}/{$now->month}/{$now->day}/temp";

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

                    \Log::info('Blog image uploaded successfully:', [
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $image->getSize(),
                    ]);
                    $count++; // 성공한 경우에만 카운트 증가
                }
            } catch (\Exception $e) {
                \Log::error('Blog image upload failed:', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        return $uploadedImages;
    }

    /**
     * 이미지를 temp 디렉토리에서 실제 경로로 이동
     */
    protected function moveImagesToFinalPath($uploadedImages, $blogId)
    {
        $movedImages = [];
        $now = now();

        foreach ($uploadedImages as $image) {
            try {
                $oldPath = $image['path'];

                // 새 경로 생성: blog/{year}/{month}/{day}/{blogId}/
                $newDir = "blog/{$now->year}/{$now->month}/{$now->day}/{$blogId}";
                $newPath = $newDir . '/' . $image['filename'];

                // 디렉토리 생성
                if (!Storage::disk('public')->exists($newDir)) {
                    Storage::disk('public')->makeDirectory($newDir);
                }

                // 파일 이동
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);

                    // 경로 정보 업데이트
                    $image['path'] = $newPath;
                    $image['url'] = asset('storage/' . $newPath);

                    \Log::info('Blog image moved successfully:', [
                        'from' => $oldPath,
                        'to' => $newPath,
                        'blog_id' => $blogId
                    ]);
                }

                $movedImages[] = $image;

            } catch (\Exception $e) {
                \Log::error('Failed to move blog image:', [
                    'error' => $e->getMessage(),
                    'blog_id' => $blogId,
                    'image' => $image
                ]);
                // 이동 실패해도 원래 경로로 추가
                $movedImages[] = $image;
            }
        }

        return $movedImages;
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
            \Log::info('Blog images saved:', [
                'blog_id' => $blogId,
                'image_count' => count($imageData)
            ]);
        }
    }
}