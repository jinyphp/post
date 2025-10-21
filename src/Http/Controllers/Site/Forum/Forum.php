<?php

namespace Jiny\Post\Http\Controllers\Site\Forum;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Jiny\Site\Facades\Header;
use Jiny\Site\Facades\Footer;

/**
 * 포럼 목록 단일 액션 컨트롤러
 */
class Forum extends Controller
{
    protected $viewPath = 'jiny-post::www.forum';

    public function __invoke(Request $request)
    {
        $table = 'site_forum';

        if (!Schema::hasTable($table)) {
            abort(404, '포럼 테이블이 존재하지 않습니다.');
        }

        // 페이지당 게시물 수 (기본값: 10)
        $perPage = $request->get('perPage', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100]) ? $perPage : 10;

        // 기본 쿼리
        $query = DB::table($table);

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // 카테고리 필터
        if ($request->has('category') && $request->category) {
            $query->where('categories', 'like', "%{$request->category}%");
        }

        // 정렬 옵션
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        // 허용된 정렬 필드만 사용
        $allowedSorts = ['created_at', 'title', 'click', 'like', 'rank'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $allowedOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedOrders)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $rows = $query->paginate($perPage)->appends($request->query());

        // 카테고리 목록 조회 (필터용)
        $categories = DB::table($table)
            ->select('categories')
            ->whereNotNull('categories')
            ->where('categories', '!=', '')
            ->distinct()
            ->pluck('categories')
            ->flatMap(function ($category) {
                return array_map('trim', explode(',', $category));
            })
            ->unique()
            ->filter()
            ->sort()
            ->values();

        // 헤더와 푸터 경로 가져오기
        $header = Header::getDefaultHeaderPath();
        $footer = Footer::getDefaultFooterPath();

        return view("{$this->viewPath}.index", [
            'rows' => $rows,
            'categories' => $categories,
            'perPage' => $perPage,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder,
            'currentCategory' => $request->category,
            'currentSearch' => $request->search,
            'header' => $header,
            'footer' => $footer,
        ]);
    }
}