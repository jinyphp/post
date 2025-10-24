<?php

namespace Jiny\Post\Http\Controllers\Admin\BoardPost;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Jiny\Post\Services\FileSecurityValidator;

/**
 * Board Post Create Controller
 *
 * Handles creation of new posts and child posts (replies)
 * Supports both regular posts and reply posts
 */
class AdminBoardPostCreate extends Controller
{
    /**
     * Show create form or handle post creation
     */
    public function __invoke(Request $request, $code, $parentId = null)
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

        // Handle form submission (POST request)
        if ($request->isMethod('post')) {
            return $this->store($request, $code, $board, $tableName, $parentId);
        }

        // Handle reply creation
        $parent = null;
        if ($parentId) {
            $parent = DB::table($tableName)->find($parentId);
            if (!$parent) {
                return redirect()->route('admin.cms.board.posts.index', $code)
                    ->with('error', 'Parent post not found.');
            }
        }

        // Show create form (GET request)
        return view('jiny-post::admin.board_post.create', [
            'board' => $board,
            'code' => $code,
            'parent' => $parent,
            'config' => [
                'title' => $parent ? 'Reply to: ' . $parent->title : 'Create New Post',
                'subtitle' => $board->title,
            ]
        ]);
    }

    /**
     * Store new post
     */
    private function store(Request $request, $code, $board, $tableName, $parentId = null)
    {
        // Validate input (exclude file fields from main validation)
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'password' => 'nullable|string|max:100',
            'categories' => 'nullable|string|max:100',
            'keyword' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'image' => 'nullable|url|max:255',
        ];

        // Validate file uploads separately if uploads are enabled
        if ($board->allow_image_upload && $request->hasFile('images')) {
            $request->validate([
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB
            ]);
        }

        if ($board->allow_file_upload && $request->hasFile('attachments')) {
            $request->validate([
                'attachments.*' => 'file|max:5120' // 5MB
            ]);
        }

        $validated = $request->validate($rules);

        // Prepare data for insertion (only basic post fields)
        $data = $validated;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Set default values
        $data['click'] = 0;
        $data['like'] = 0;
        $data['rank'] = 0;

        // Handle parent/child relationship
        if ($parentId) {
            $parent = DB::table($tableName)->find($parentId);
            if ($parent) {
                $data['parent_id'] = $parentId;
                $data['level'] = ($parent->level ?? 0) + 1;
            }
        } else {
            $data['parent_id'] = null;
            $data['level'] = 0;
        }

        // Generate unique slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['title']) . '-' . time();
        }

        // Insert post
        $postId = DB::table($tableName)->insertGetId($data);

        // Handle file uploads
        $uploadResults = $this->handleFileUploads($request, $board, $code, $postId);

        // Update board statistics
        $this->updateBoardStats($code);

        // Prepare success message
        $message = $parentId ? 'Reply created successfully.' : 'Post created successfully.';
        if (!empty($uploadResults['uploaded'])) {
            $message .= ' ' . count($uploadResults['uploaded']) . ' files uploaded.';
        }
        if (!empty($uploadResults['failed'])) {
            $message .= ' ' . count($uploadResults['failed']) . ' files failed security check.';
        }

        return redirect()->route('admin.cms.board.posts.index', $code)
            ->with('success', $message);
    }

    /**
     * Handle file uploads for a post
     */
    private function handleFileUploads(Request $request, $board, $code, $postId)
    {
        $results = ['uploaded' => [], 'failed' => []];
        $fileValidator = new FileSecurityValidator();

        // Handle image uploads
        if ($board->allow_image_upload && $request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $result = $this->processImageUpload($image, $fileValidator, $board, $code, $postId);
                if ($result['success']) {
                    $results['uploaded'][] = $result;
                } else {
                    $results['failed'][] = $result;
                }
            }
        }

        // Handle file attachments
        if ($board->allow_file_upload && $request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $result = $this->processFileUpload($attachment, $fileValidator, $board, $code, $postId);
                if ($result['success']) {
                    $results['uploaded'][] = $result;
                } else {
                    $results['failed'][] = $result;
                }
            }
        }

        return $results;
    }

    /**
     * Process individual image upload
     */
    private function processImageUpload($image, $fileValidator, $board, $code, $postId)
    {
        try {
            // Validate file security
            $validation = $fileValidator->validateFile($image, [
                'blocked_extensions' => $board->blocked_extensions ?? '',
                'max_file_size' => 5120, // 5MB in KB
                'allowed_types' => ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
            ]);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'filename' => $image->getClientOriginalName(),
                    'error' => $validation['error']
                ];
            }

            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Create hierarchical path: board/{code}/images/{year}/{month}/{day}/{postId}/
            $now = now();
            $path = "board/{$code}/images/{$now->year}/{$now->month}/{$now->day}/{$postId}";

            // Store file
            $filePath = $image->storeAs($path, $filename, 'public');

            // Create board-specific images table if it doesn't exist
            $imagesTable = "site_board_{$code}_images";
            if (!Schema::hasTable($imagesTable)) {
                $this->createBoardImagesTable($imagesTable);
            }

            // Save to board-specific images table
            DB::table($imagesTable)->insert([
                'post_id' => $postId,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'alt_text' => null,
                'caption' => null,
                'sort_order' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'path' => $filePath
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'filename' => $image->getClientOriginalName(),
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process individual file upload
     */
    private function processFileUpload($file, $fileValidator, $board, $code, $postId)
    {
        try {
            // Validate file security
            $validation = $fileValidator->validateFile($file, [
                'blocked_extensions' => $board->blocked_extensions ?? '',
                'max_file_size' => 5120, // 5MB in KB
            ]);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $validation['error']
                ];
            }

            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Create hierarchical path: board/{code}/attachments/{year}/{month}/{day}/{postId}/
            $now = now();
            $path = "board/{$code}/attachments/{$now->year}/{$now->month}/{$now->day}/{$postId}";

            // Store file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Create board-specific attachments table if it doesn't exist
            $attachmentsTable = "site_board_{$code}_attachments";
            if (!Schema::hasTable($attachmentsTable)) {
                $this->createBoardAttachmentsTable($attachmentsTable);
            }

            // Save to board-specific attachments table
            DB::table($attachmentsTable)->insert([
                'post_id' => $postId,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'download_count' => 0,
                'is_secure' => true,
                'scanned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path' => $filePath
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'filename' => $file->getClientOriginalName(),
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update board statistics
     */
    private function updateBoardStats($code)
    {
        $tableName = "site_board_" . $code;

        // Calculate post count
        $postCount = DB::table($tableName)->count();

        // Calculate total views
        $totalViews = DB::table($tableName)->sum('click') ?? 0;

        // Get last post date
        $lastPost = DB::table($tableName)->orderBy('created_at', 'desc')->first();
        $lastPostAt = $lastPost ? $lastPost->created_at : null;

        // Update board table
        DB::table('site_board')
            ->where('code', $code)
            ->update([
                'post' => $postCount,
                'total_views' => $totalViews,
                'last_post_at' => $lastPostAt,
                'updated_at' => now(),
            ]);
    }

    /**
     * Create board-specific images table
     */
    private function createBoardImagesTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);

            $table->index('post_id');
        });
    }

    /**
     * Create board-specific attachments table
     */
    private function createBoardAttachmentsTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_secure')->default(true);
            $table->timestamp('scanned_at')->nullable();

            $table->index('post_id');
        });
    }
}