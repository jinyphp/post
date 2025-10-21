<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Single Action Controller for Blog Edit
 *
 * 블로그 수정을 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogEdit extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 수정 처리 (GET: 폼 표시, POST/PUT: 저장 처리)
     */
    public function __invoke(Request $request, $id)
    {
        if ($request->isMethod('GET')) {
            return $this->showEditForm($request, $id);
        }

        return $this->update($request, $id);
    }

    /**
     * 수정 폼 표시
     */
    protected function showEditForm(Request $request, $id)
    {
        try {
            $blog = DB::table($this->table)->find($id);

            if (!$blog) {
                return redirect()->route('admin.cms.blog')
                    ->with('error', '수정하려는 블로그 글을 찾을 수 없습니다.');
            }

            // 카테고리 목록
            $categories = DB::table('site_blog_cate')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return view('jiny-post::admin.blog.edit', compact('blog', 'categories'));

        } catch (\Exception $e) {
            \Log::error('Blog edit form error', ['id' => $id, 'error' => $e->getMessage()]);

            return redirect()->route('admin.cms.blog')
                ->with('error', '블로그 수정 폼을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 블로그 수정 처리
     */
    protected function update(Request $request, $id)
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
            ]);

            $data = $request->except(['_token', '_method', 'featured_image_file', 'action']);

            // 초안 저장 액션 처리
            if ($request->input('action') === 'draft') {
                $data['status'] = 'draft';
            }

            // 이미지 업로드 처리
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

            // 슬러그 업데이트
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // 슬러그가 비어있지 않은 경우에만 중복 체크
            if (!empty($data['slug'])) {
                $originalSlug = $data['slug'];
                $counter = 1;
                while (DB::table($this->table)
                         ->where('slug', $data['slug'])
                         ->where('id', '!=', $id)
                         ->exists()) {
                    $data['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
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
}