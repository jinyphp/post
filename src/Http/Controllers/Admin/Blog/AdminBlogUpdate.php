<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Single Action Controller for Blog Update
 *
 * 블로그 수정 처리를 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogUpdate extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        try {
            \Log::info('Blog update started', ['id' => $id, 'request_data' => $request->all()]);

            // 해당 글이 존재하는지 확인
            $existingItem = DB::table($this->table)->find($id);
            if (!$existingItem) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '수정하려는 블로그 글을 찾을 수 없습니다.'
                    ], 404);
                }

                return redirect()->route('admin.cms.blog')
                    ->with('error', '수정하려는 블로그 글을 찾을 수 없습니다.');
            }

            $request->validate([
                'title' => 'required|max:255',
                'slug' => 'nullable|unique:site_blog,slug,' . $id,
                'featured_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'images' => 'array|max:10',
                'remove_image_ids' => 'nullable|string',
            ]);

            $data = $request->except(['_token', '_method', 'featured_image_file', 'images', 'remove_image_ids', 'action']);

            // 초안 저장 액션 처리
            if ($request->input('action') === 'draft') {
                $data['status'] = 'draft';
            }

            // 기존 이미지 삭제 처리
            $removedImageIds = [];
            if ($request->filled('remove_image_ids')) {
                $removedImageIds = array_filter(explode(',', $request->remove_image_ids));
                if (!empty($removedImageIds)) {
                    $this->removeBlogImages($removedImageIds);
                }
            }

            // 대표 이미지 업로드 처리
            if ($request->hasFile('featured_image_file')) {
                // 기존 이미지 삭제
                if ($existingItem->featured_image && str_contains($existingItem->featured_image, '/storage/')) {
                    $oldPath = str_replace('/storage/', '', $existingItem->featured_image);
                    Storage::disk('public')->delete($oldPath);
                }

                $file = $request->file('featured_image_file');

                // 계층화된 경로 생성 (년/월/일)
                $date = now();
                $datePath = $date->format('Y/m/d');
                $fileName = $date->format('His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs("blog/images/{$datePath}", $fileName, 'public');
                $data['featured_image'] = Storage::url($path);
            }

            // 새 다중 이미지 업로드 처리
            if ($request->hasFile('images')) {
                $this->saveBlogImages($id, $request->file('images'));
            }

            // 대표 이미지 업데이트 (이미지가 있는 경우)
            if (empty($data['featured_image'])) {
                $firstImage = DB::table('site_blog_images')
                    ->where('blog_id', $id)
                    ->orderBy('sort_order', 'asc')
                    ->first();

                if ($firstImage) {
                    $data['featured_image'] = $firstImage->url;
                } elseif (!empty($removedImageIds)) {
                    // 모든 이미지가 삭제된 경우
                    $data['featured_image'] = null;
                }
            }

            // 슬러그 업데이트 및 처리
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
            while (DB::table($this->table)
                     ->where('slug', $data['slug'])
                     ->where('id', '!=', $id)
                     ->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            // 체크박스 처리
            $data['allow_comments'] = $request->has('allow_comments') ? 1 : 0;
            $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
            $data['is_sticky'] = $request->has('is_sticky') ? 1 : 0;

            // 발행 상태 변경 시 발행 시간 설정
            if ($data['status'] === 'published' && $existingItem->status !== 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            } elseif ($data['status'] === 'scheduled' && !empty($data['scheduled_at'])) {
                $data['scheduled_at'] = Carbon::parse($data['scheduled_at']);
            }

            // 요약 자동 생성 (없을 경우)
            if (empty($data['excerpt']) && !empty($data['content'])) {
                $data['excerpt'] = Str::limit(strip_tags($data['content']), 200);
            }

            // SEO 메타 정보 자동 생성
            if (empty($data['meta_title'])) {
                $data['meta_title'] = $data['title'];
            }
            if (empty($data['meta_description'])) {
                $data['meta_description'] = $data['excerpt'];
            }

            // 타임스탬프 업데이트
            $data['updated_at'] = now();

            $affected = DB::table($this->table)
                ->where('id', $id)
                ->update($data);

            if ($affected === 0) {
                \Log::warning('Blog update affected 0 rows', ['id' => $id]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '데이터 업데이트에 실패했습니다. 다시 시도해주세요.'
                    ], 500);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', '데이터 업데이트에 실패했습니다. 다시 시도해주세요.');
            }

            \Log::info('Blog updated successfully', ['id' => $id, 'status' => $data['status'] ?? 'undefined', 'affected_rows' => $affected]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 글이 수정되었습니다.',
                    'redirect' => route('admin.cms.blog')
                ]);
            }

            return redirect()->route('admin.cms.blog')
                ->with('success', '블로그 글이 수정되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Blog update validation error', ['id' => $id, 'errors' => $e->errors()]);

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
            \Log::error('Blog update general error', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

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
                    \Log::info('Admin Blog image file deleted: ' . $image->path);
                }

                // 데이터베이스에서 삭제
                DB::table('site_blog_images')->where('id', $image->id)->delete();

            } catch (\Exception $e) {
                \Log::error('Failed to delete admin blog image: ' . $e->getMessage(), [
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

        \Log::info('Admin Blog image upload debug:', [
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

                    \Log::info('Admin Blog image saved successfully:', [
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
                    \Log::error('Admin Blog image path is empty:', [
                        'blog_id' => $blogId,
                        'index' => $index,
                        'original_name' => $image->getClientOriginalName(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Admin Blog image save failed:', [
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

        \Log::info('Admin Blog image upload completed:', [
            'blog_id' => $blogId,
            'initial_count' => $currentImageCount,
            'uploaded_count' => count($images),
            'final_count' => $finalImageCount,
            'expected_count' => $currentImageCount + count($images)
        ]);
    }
}