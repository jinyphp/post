<?php

namespace Jiny\Post\Http\Controllers\Admin\BoardPost;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Board Post Index Controller
 *
 * Displays list of posts for a specific board
 * Handles search, filtering, and pagination
 */
class AdminBoardPostIndex extends Controller
{
    /**
     * Display posts for specific board
     */
    public function __invoke(Request $request, $code)
    {
        // Get board information
        $board = DB::table('site_board')->where('code', $code)->first();

        if (!$board) {
            return redirect()->route('admin.cms.board.list.index')
                ->with('error', 'Board not found.');
        }

        $tableName = "site_board_" . $code;

        // Check if board table exists
        if (!Schema::hasTable($tableName)) {
            return redirect()->route('admin.cms.board.list.index')
                ->with('error', 'Board table does not exist.');
        }

        // Build query for posts
        $query = DB::table($tableName);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->where('categories', 'LIKE', "%{$category}%");
        }

        // Hierarchical ordering: parent posts first, then children
        $rows = $query->orderByRaw('COALESCE(parent_id, id) ASC, parent_id IS NOT NULL, id ASC')
                      ->paginate(15);

        // Add search parameters to pagination
        $rows->appends($request->query());

        // Calculate child post counts for each post
        $childCounts = [];
        foreach ($rows as $row) {
            $childCounts[$row->id] = DB::table($tableName)
                ->where('parent_id', $row->id)
                ->count();
        }

        // Calculate attachment counts for each post
        $attachmentCounts = [];
        $imageCounts = [];
        $attachmentTableName = "site_board_" . $code . "_attachments";
        $imageTableName = "site_board_" . $code . "_images";

        foreach ($rows as $row) {
            // Count attachments
            if (Schema::hasTable($attachmentTableName)) {
                $attachmentCounts[$row->id] = DB::table($attachmentTableName)
                    ->where('post_id', $row->id)
                    ->count();
            } else {
                $attachmentCounts[$row->id] = 0;
            }

            // Count images
            if (Schema::hasTable($imageTableName)) {
                $imageCounts[$row->id] = DB::table($imageTableName)
                    ->where('post_id', $row->id)
                    ->count();
            } else {
                $imageCounts[$row->id] = 0;
            }
        }

        // Get board statistics
        $stats = [
            'total_posts' => DB::table($tableName)->count(),
            'total_views' => DB::table($tableName)->sum('click') ?? 0,
            'recent_posts' => DB::table($tableName)->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('jiny-post::admin.board_post.index', [
            'board' => $board,
            'rows' => $rows,
            'code' => $code,
            'childCounts' => $childCounts,
            'attachmentCounts' => $attachmentCounts,
            'imageCounts' => $imageCounts,
            'stats' => $stats,
            'search' => $search,
            'category' => $category,
        ]);
    }
}