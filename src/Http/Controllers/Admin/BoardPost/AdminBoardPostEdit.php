<?php

namespace Jiny\Post\Http\Controllers\Admin\BoardPost;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Jiny\Post\Services\FileSecurityValidator;

/**
 * Board Post Edit Controller
 *
 * Handles editing and updating of existing posts
 * Supports updating post content, metadata, and deletion
 */
class AdminBoardPostEdit extends Controller
{
    /**
     * Show edit form or handle post update/deletion
     */
    public function __invoke(Request $request, $code, $id)
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

        // Get post data
        $post = DB::table($tableName)->find($id);

        if (!$post) {
            return redirect()->route('admin.cms.board.posts.index', $code)
                ->with('error', 'Post not found.');
        }

        // Handle different request methods
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            return $this->update($request, $code, $id, $tableName);
        } elseif ($request->isMethod('delete')) {
            return $this->destroy($request, $code, $id, $tableName);
        }

        // Get existing attachments and images
        $attachments = [];
        $images = [];

        $attachmentTableName = "site_board_" . $code . "_attachments";
        $imageTableName = "site_board_" . $code . "_images";

        if (Schema::hasTable($attachmentTableName)) {
            $attachments = DB::table($attachmentTableName)
                ->where('post_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if (Schema::hasTable($imageTableName)) {
            $images = DB::table($imageTableName)
                ->where('post_id', $id)
                ->orderBy('sort_order', 'asc')
                ->get();
        }

        // Show edit form (GET request)
        return view('jiny-post::admin.board_post.edit', [
            'board' => $board,
            'post' => $post,
            'code' => $code,
            'attachments' => $attachments,
            'images' => $images,
            'config' => [
                'title' => 'Edit Post: ' . $post->title,
                'subtitle' => $board->title,
            ]
        ]);
    }

    /**
     * Update post
     */
    private function update(Request $request, $code, $id, $tableName)
    {
        // Get board information for file upload settings
        $board = DB::table('site_board')->where('code', $code)->first();

        // Validate input (exclude file fields from main validation)
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'categories' => 'nullable|string|max:100',
            'keyword' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'image' => 'nullable|url|max:255',
            'click' => 'nullable|integer|min:0',
            'like' => 'nullable|integer|min:0',
            'rank' => 'nullable|integer|min:0',
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

        // Prepare data for update
        $data = $validated;
        $data['updated_at'] = now();

        // Set default values for statistics if not provided
        $data['click'] = $data['click'] ?? 0;
        $data['like'] = $data['like'] ?? 0;
        $data['rank'] = $data['rank'] ?? 0;

        // Update slug if title changed
        $existingPost = DB::table($tableName)->find($id);
        if ($existingPost && $existingPost->title !== $data['title']) {
            if (empty($existingPost->slug) || $existingPost->slug === \Str::slug($existingPost->title)) {
                $data['slug'] = \Str::slug($data['title']) . '-' . time();
            }
        }

        // Handle file deletions
        $this->handleFileDeletions($request, $code, $id);

        // Handle new file uploads
        $uploadResults = $this->handleFileUploads($request, $board, $code, $id);

        // Update post
        DB::table($tableName)->where('id', $id)->update($data);

        // Update board statistics
        $this->updateBoardStats($code);

        // Prepare success message
        $message = 'Post updated successfully.';
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
     * Delete post
     */
    private function destroy(Request $request, $code, $id, $tableName)
    {
        // Check if post has children
        $childCount = DB::table($tableName)->where('parent_id', $id)->count();

        if ($childCount > 0) {
            return redirect()->route('admin.cms.board.posts.index', $code)
                ->with('error', 'Cannot delete post with replies. Please delete replies first.');
        }

        // Delete post
        DB::table($tableName)->where('id', $id)->delete();

        // Update board statistics
        $this->updateBoardStats($code);

        return redirect()->route('admin.cms.board.posts.index', $code)
            ->with('success', 'Post deleted successfully.');
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
     * Handle file deletions for post edit
     */
    private function handleFileDeletions(Request $request, $code, $postId)
    {
        $removedImages = $this->parseDeletedIds($request->input('remove_image_ids', ''));
        $removedAttachments = $this->parseDeletedIds($request->input('remove_attachment_ids', ''));

        $this->deleteFiles($removedImages, "site_board_{$code}_images", $postId);
        $this->deleteFiles($removedAttachments, "site_board_{$code}_attachments", $postId);
    }

    /**
     * Parse comma-separated ID string to array of numeric IDs
     */
    private function parseDeletedIds($input)
    {
        if (empty($input)) {
            return [];
        }

        return array_filter(
            explode(',', $input),
            function($id) {
                return !empty($id) && is_numeric($id);
            }
        );
    }

    /**
     * Delete files from storage and database
     */
    private function deleteFiles($fileIds, $tableName, $postId)
    {
        if (empty($fileIds) || !Schema::hasTable($tableName)) {
            return;
        }

        foreach ($fileIds as $fileId) {
            $file = DB::table($tableName)
                ->where('id', $fileId)
                ->where('post_id', $postId)
                ->first();

            if ($file) {
                // Delete physical file
                Storage::disk('public')->delete($file->file_path);
                // Delete database record
                DB::table($tableName)->where('id', $fileId)->delete();
            }
        }
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