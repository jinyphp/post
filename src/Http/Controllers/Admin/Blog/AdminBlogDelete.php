<?php

namespace Jiny\Post\Http\Controllers\Admin\Blog;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Single Action Controller for Blog Delete
 *
 * 블로그 삭제를 전담하는 단일 액션 컨트롤러입니다.
 */
class AdminBlogDelete extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_blog';

    /**
     * 블로그 삭제 처리
     */
    public function __invoke(Request $request, $id)
    {
        try {
            \Log::info('Blog delete started', ['id' => $id]);

            // 해당 글이 존재하는지 확인
            $blog = DB::table($this->table)->find($id);
            if (!$blog) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '삭제하려는 블로그 글을 찾을 수 없습니다.'
                    ], 404);
                }

                return redirect()->route('admin.cms.blog')
                    ->with('error', '삭제하려는 블로그 글을 찾을 수 없습니다.');
            }

            // 첨부된 이미지 파일 삭제
            if ($blog->featured_image && str_contains($blog->featured_image, '/storage/')) {
                $imagePath = str_replace('/storage/', '', $blog->featured_image);
                Storage::disk('public')->delete($imagePath);
                \Log::info('Featured image deleted', ['path' => $imagePath]);
            }

            // 데이터베이스에서 삭제
            $deleted = DB::table($this->table)->where('id', $id)->delete();

            if ($deleted === 0) {
                \Log::warning('Blog delete affected 0 rows', ['id' => $id]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '블로그 글 삭제에 실패했습니다. 다시 시도해주세요.'
                    ], 500);
                }

                return redirect()->back()
                    ->with('error', '블로그 글 삭제에 실패했습니다. 다시 시도해주세요.');
            }

            \Log::info('Blog deleted successfully', ['id' => $id, 'title' => $blog->title]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '블로그 글이 삭제되었습니다.',
                    'redirect' => route('admin.cms.blog')
                ]);
            }

            return redirect()->route('admin.cms.blog')
                ->with('success', '블로그 글이 삭제되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Blog delete error', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '시스템 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', '시스템 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}