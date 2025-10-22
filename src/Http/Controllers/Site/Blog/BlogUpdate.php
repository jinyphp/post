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
 * Blog Update Controller
 *
 * 블로그 글 수정 처리를 처리하는 컨트롤러입니다.
 */
class BlogUpdate extends Controller
{
    /**
     * 블로그 글 수정 처리
     */
    public function __invoke(Request $request, $slug)
    {
        $table = 'site_blog';

        if (!Schema::hasTable($table)) {
            abort(404, '블로그 테이블이 존재하지 않습니다.');
        }

        // 블로그 글 조회
        $blog = DB::table($table)->where('slug', $slug)->first();

        if (!$blog) {
            abort(404, '존재하지 않는 글입니다.');
        }

        // 현재 사용자 정보 가져오기
        $user = Auth::user();

        // 수정 권한 확인
        if (!$this->canEditBlog($blog, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '이 글을 수정할 권한이 없습니다.',
                ], 403);
            } else {
                return redirect()->route('blog.show', $slug)
                    ->with('error', '이 글을 수정할 권한이 없습니다.');
            }
        }

        // 입력값 검증
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_slug' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'remove_image_ids' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'allow_comments' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_sticky' => 'nullable|boolean',
            'excerpt' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ], [
            'title.required' => '제목을 입력해주세요.',
            'title.max' => '제목은 255자를 초과할 수 없습니다.',
            'content.required' => '내용을 입력해주세요.',
            'category_slug.max' => '카테고리는 255자를 초과할 수 없습니다.',
            'tags.max' => '태그는 255자를 초과할 수 없습니다.',
            'images.*.image' => '이미지 파일만 업로드 가능합니다.',
            'images.*.mimes' => '지원되는 이미지 형식: jpeg, png, jpg, gif, webp',
            'images.*.max' => '이미지 파일 크기는 5MB를 초과할 수 없습니다.',
            'excerpt.max' => '요약은 500자를 초과할 수 없습니다.',
            'meta_title.max' => 'SEO 제목은 255자를 초과할 수 없습니다.',
            'meta_description.max' => 'SEO 설명은 500자를 초과할 수 없습니다.',
        ]);

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

            // 제목이 변경된 경우 슬러그 재생성
            $newSlug = $slug;
            if ($request->title !== $blog->title) {
                $newSlug = $this->generateUniqueSlug($request->title, $blog->id);
            }

            // 데이터 준비
            $data = [
                'title' => $request->title,
                'slug' => $newSlug,
                'content' => $request->content,
                'excerpt' => $request->excerpt ?? Str::limit(strip_tags($request->content), 200),
                'category_slug' => $request->category_slug,
                'tags' => $request->tags,
                'allow_comments' => $request->has('allow_comments') ? 1 : 0,
                'is_featured' => $request->has('is_featured') ? 1 : 0,
                'is_sticky' => $request->has('is_sticky') ? 1 : 0,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'updated_at' => now(),
            ];

            // 상태 변경 시 발행일 설정
            if ($request->filled('status')) {
                $data['status'] = $request->status;

                // 초안에서 발행으로 변경하는 경우
                if ($request->status === 'published' && $blog->status === 'draft') {
                    $data['published_at'] = now();
                }
                // 발행에서 초안으로 변경하는 경우
                elseif ($request->status === 'draft' && $blog->status === 'published') {
                    $data['published_at'] = null;
                }
            }

            // 기존 이미지 삭제 처리
            $removedImageIds = [];
            if ($request->filled('remove_image_ids')) {
                $removedImageIds = array_filter(explode(',', $request->remove_image_ids));
                if (!empty($removedImageIds)) {
                    $this->removeBlogImages($removedImageIds);
                }
            }

            // 새 이미지 업로드 처리
            if ($request->hasFile('images')) {
                $this->saveBlogImages($blog->id, $request->file('images'));
            }

            // 대표 이미지 업데이트 (이미지가 있는 경우)
            $firstImage = DB::table('site_blog_images')
                ->where('blog_id', $blog->id)
                ->orderBy('sort_order', 'asc')
                ->first();

            if ($firstImage) {
                $data['featured_image'] = $firstImage->url;
            } elseif (!empty($removedImageIds)) {
                // 모든 이미지가 삭제된 경우
                $data['featured_image'] = null;
            }

            // 메인 데이터베이스 업데이트
            DB::table($table)->where('id', $blog->id)->update($data);

            DB::commit();

            // 성공 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 글이 성공적으로 수정되었습니다.',
                    'blog_id' => $blog->id,
                    'slug' => $newSlug,
                ]);
            } else {
                return redirect()->route('blog.show', $newSlug)
                    ->with('success', '블로그 글이 성공적으로 수정되었습니다.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Blog update error: ' . $e->getMessage(), [
                'blog_id' => $blog->id,
                'user_id' => $user->id ?? null,
                'request_data' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '블로그 글 수정 중 오류가 발생했습니다.',
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', '블로그 글 수정 중 오류가 발생했습니다.')
                    ->withInput();
            }
        }
    }

    /**
     * 고유한 슬러그 생성 (수정 시 현재 ID 제외)
     */
    protected function generateUniqueSlug($title, $excludeId = null)
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        $query = DB::table('site_blog')->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;

            $query = DB::table('site_blog')->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * 블로그 글 수정 권한 확인
     */
    protected function canEditBlog($blog, $user)
    {
        // 로그인하지 않은 경우
        if (!$user) {
            return false;
        }

        // 관리자는 모든 글 수정 가능
        if ($this->isAdmin($user)) {
            return true;
        }

        // 본인이 작성한 글인지 확인
        if (isset($blog->user_id) && $user->id == $blog->user_id) {
            return true;
        }

        // UUID로 확인 (샤딩 사용자인 경우)
        if (isset($blog->user_uuid) && isset($user->uuid) && $user->uuid == $blog->user_uuid) {
            return true;
        }

        return false;
    }

    /**
     * 관리자 권한 확인
     */
    protected function isAdmin($user)
    {
        // isAdmin 플래그 확인
        if (isset($user->isAdmin) && $user->isAdmin) {
            return true;
        }

        // 관리자 역할 확인
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super-admin');
        }

        // role 필드 직접 확인
        if (isset($user->role) && in_array($user->role, ['admin', 'super-admin'])) {
            return true;
        }

        // utype 필드 확인 (jiny/admin 패키지 방식)
        if (isset($user->utype) && $user->utype === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * 블로그 이미지 삭제 처리
     */
    protected function removeBlogImages($imageIds)
    {
        // 삭제할 이미지 정보 조회
        $images = DB::table('site_blog_images')
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            try {
                // 물리적 파일 삭제
                if ($image->path && Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                    \Log::info('Blog image file deleted: ' . $image->path);
                }

                // 데이터베이스에서 삭제
                DB::table('site_blog_images')->where('id', $image->id)->delete();

            } catch (\Exception $e) {
                \Log::error('Failed to delete blog image: ' . $e->getMessage(), [
                    'image_id' => $image->id,
                    'path' => $image->path ?? 'no path',
                ]);
            }
        }
    }

    /**
     * 새 블로그 이미지 저장
     */
    protected function saveBlogImages($blogId, $images)
    {
        // 현재 이미지 개수 확인
        $currentImageCount = DB::table('site_blog_images')
            ->where('blog_id', $blogId)
            ->count();

        // 최대 정렬 순서 확인
        $maxSortOrder = DB::table('site_blog_images')
            ->where('blog_id', $blogId)
            ->max('sort_order') ?? 0;

        \Log::info('Blog image upload debug:', [
            'blog_id' => $blogId,
            'image_count' => count($images),
            'current_db_count' => $currentImageCount,
            'max_sort_order' => $maxSortOrder
        ]);

        foreach ($images as $index => $image) {
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

                // 파일 저장 (public 디스크 사용)
                $imagePath = $image->storeAs($hierarchicalPath, $filename, 'public');

                if ($imagePath) {
                    // site_blog_images 테이블에 이미지 정보 저장
                    $inserted = DB::table('site_blog_images')->insert([
                        'blog_id' => $blogId,
                        'filename' => $filename,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $imagePath,
                        'url' => asset('storage/' . $imagePath),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'sort_order' => $maxSortOrder + $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    \Log::info('Blog image saved successfully:', [
                        'blog_id' => $blogId,
                        'index' => $index,
                        'filename' => $filename,
                        'original_name' => $image->getClientOriginalName(),
                        'path' => $imagePath,
                        'size' => $image->getSize(),
                        'inserted' => $inserted,
                        'sort_order' => $maxSortOrder + $index + 1,
                    ]);
                } else {
                    \Log::error('Blog image path is empty:', [
                        'blog_id' => $blogId,
                        'index' => $index,
                        'original_name' => $image->getClientOriginalName(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Blog image save failed:', [
                    'blog_id' => $blogId,
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_name' => $image->getClientOriginalName(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e; // Re-throw to trigger rollback
            }
        }

        // 저장 완료 후 최종 확인
        $finalImageCount = DB::table('site_blog_images')
            ->where('blog_id', $blogId)
            ->count();

        \Log::info('Blog image upload completed:', [
            'blog_id' => $blogId,
            'initial_count' => $currentImageCount,
            'uploaded_count' => count($images),
            'final_count' => $finalImageCount,
            'expected_count' => $currentImageCount + count($images)
        ]);
    }
}