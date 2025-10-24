<?php

namespace Jiny\Post\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * AdminBoardPost File Upload Feature Tests
 *
 * TDD test cases for multi-file upload functionality in board posts
 * Tests file security, image uploads, attachments, and drag-and-drop features
 */
class AdminBoardPostFileUploadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup admin authentication for tests
        $this->withoutMiddleware();

        // Create board tables and file upload tables
        $this->createBoardTables();
        $this->createFileUploadTables();

        // Setup test board
        $this->setupTestBoard();

        // Setup storage for testing
        Storage::fake('public');
    }

    /**
     * Create board tables for testing
     */
    private function createBoardTables(): void
    {
        if (!Schema::hasTable('site_board')) {
            Schema::create('site_board', function ($table) {
                $table->id();
                $table->timestamps();
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable();
                $table->string('code')->nullable();
                $table->string('slug')->nullable();
                $table->boolean('enable')->default(true);
                $table->text('description')->nullable();
                $table->string('manager')->nullable();
                $table->integer('post')->default(0);
                $table->integer('total_views')->default(0);
                $table->datetime('last_post_at')->nullable();
                $table->string('write_permission')->default('guest_allowed');
                $table->string('read_permission')->default('guest_allowed');
                $table->string('comment_permission')->default('guest_allowed');
            });
        }

        // Add permission columns if they don't exist
        if (!Schema::hasColumn('site_board', 'write_permission')) {
            Schema::table('site_board', function ($table) {
                $table->string('write_permission')->default('guest_allowed');
                $table->string('read_permission')->default('guest_allowed');
                $table->string('comment_permission')->default('guest_allowed');
            });
        }

        // Add file upload columns if they don't exist
        if (!Schema::hasColumn('site_board', 'allow_file_upload')) {
            Schema::table('site_board', function ($table) {
                $table->boolean('allow_file_upload')->default(true);
                $table->boolean('allow_image_upload')->default(true);
                $table->unsignedInteger('max_file_count')->default(5);
                $table->text('blocked_extensions')->nullable();
            });
        }

        // Create test board post table
        if (!Schema::hasTable('site_board_testupload')) {
            Schema::create('site_board_testupload', function ($table) {
                $table->id();
                $table->timestamps();
                $table->string('title');
                $table->text('content')->nullable();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('categories')->nullable();
                $table->string('keyword')->nullable();
                $table->string('tags')->nullable();
                $table->string('image')->nullable();
                $table->string('slug')->nullable();
                $table->integer('click')->default(0);
                $table->integer('like')->default(0);
                $table->integer('rank')->default(0);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('level')->default(0);
            });
        }
    }

    /**
     * Create file upload tables for testing
     */
    private function createFileUploadTables(): void
    {
        if (!Schema::hasTable('board_images')) {
            Schema::create('board_images', function ($table) {
                $table->id();
                $table->timestamps();
                $table->string('board_code');
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
            });
        }

        if (!Schema::hasTable('board_attachments')) {
            Schema::create('board_attachments', function ($table) {
                $table->id();
                $table->timestamps();
                $table->string('board_code');
                $table->unsignedBigInteger('post_id');
                $table->string('filename');
                $table->string('original_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type');
                $table->unsignedInteger('download_count')->default(0);
                $table->boolean('is_secure')->default(true);
                $table->timestamp('scanned_at')->nullable();
            });
        }
    }

    /**
     * Setup test board with file upload capabilities
     */
    private function setupTestBoard(): void
    {
        DB::table('site_board')->insert([
            'title' => 'Test Upload Board',
            'code' => 'testupload',
            'slug' => '/board/testupload',
            'enable' => true,
            'allow_file_upload' => true,
            'allow_image_upload' => true,
            'max_file_count' => 5,
            'blocked_extensions' => 'exe,bat,cmd,com,pif,scr,vbs,js,jar,php,asp,jsp',
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test post creation form shows file upload sections when enabled
     */
    public function test_post_creation_form_shows_file_upload_sections(): void
    {
        // When: Access post creation form for upload-enabled board
        $response = $this->get('/admin/cms/board/posts/testupload/create');

        // Then: Should display file upload sections
        $response->assertStatus(200);
        $response->assertSee('File Attachments');
        $response->assertSee('Images');
        $response->assertSee('Attachments');
        $response->assertSee('Drag & drop images here');
        $response->assertSee('Drag & drop files here');
        $response->assertSee('Upload Guidelines');
        $response->assertSee('Maximum 5 files per post');
        $response->assertSee('Blocked: exe,bat,cmd,com,pif,scr,vbs,js,jar,php,asp,jsp');
    }

    /**
     * Test successful post creation with image uploads
     */
    public function test_can_create_post_with_image_uploads(): void
    {
        // Given: Valid post data with images
        $image1 = UploadedFile::fake()->image('test1.jpg', 800, 600)->size(1024); // 1MB
        $image2 = UploadedFile::fake()->image('test2.png', 1024, 768)->size(2048); // 2MB

        $postData = [
            'title' => 'Test Post with Images',
            'content' => 'This post contains uploaded images.',
            'name' => 'Test Author',
            'email' => 'test@example.com',
            'images' => [$image1, $image2],
        ];

        // When: Submit post creation request with images
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/board/posts/testupload');
        $response->assertSessionHas('success');

        // And: Post should be saved in database
        $this->assertDatabaseHas('site_board_testupload', [
            'title' => 'Test Post with Images',
            'content' => 'This post contains uploaded images.',
            'name' => 'Test Author',
        ]);

        // And: Images should be saved to board_images table
        $post = DB::table('site_board_testupload')->where('title', 'Test Post with Images')->first();
        $this->assertDatabaseHas('board_images', [
            'board_code' => 'testupload',
            'post_id' => $post->id,
            'original_name' => 'test1.jpg',
        ]);
        $this->assertDatabaseHas('board_images', [
            'board_code' => 'testupload',
            'post_id' => $post->id,
            'original_name' => 'test2.png',
        ]);

        // And: Files should be stored in storage
        $images = DB::table('board_images')->where('post_id', $post->id)->get();
        foreach ($images as $image) {
            Storage::disk('public')->assertExists($image->file_path);
        }
    }

    /**
     * Test successful post creation with file attachments
     */
    public function test_can_create_post_with_file_attachments(): void
    {
        // Given: Valid post data with attachments
        $file1 = UploadedFile::fake()->create('document.pdf', 2048); // 2MB PDF
        $file2 = UploadedFile::fake()->create('spreadsheet.xlsx', 1024); // 1MB Excel

        $postData = [
            'title' => 'Test Post with Files',
            'content' => 'This post contains file attachments.',
            'name' => 'Test Author',
            'email' => 'test@example.com',
            'attachments' => [$file1, $file2],
        ];

        // When: Submit post creation request with files
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/board/posts/testupload');
        $response->assertSessionHas('success');

        // And: Post should be saved in database
        $this->assertDatabaseHas('site_board_testupload', [
            'title' => 'Test Post with Files',
            'content' => 'This post contains file attachments.',
        ]);

        // And: Files should be saved to board_attachments table
        $post = DB::table('site_board_testupload')->where('title', 'Test Post with Files')->first();
        $this->assertDatabaseHas('board_attachments', [
            'board_code' => 'testupload',
            'post_id' => $post->id,
            'original_name' => 'document.pdf',
        ]);
        $this->assertDatabaseHas('board_attachments', [
            'board_code' => 'testupload',
            'post_id' => $post->id,
            'original_name' => 'spreadsheet.xlsx',
        ]);

        // And: Files should be stored in storage
        $attachments = DB::table('board_attachments')->where('post_id', $post->id)->get();
        foreach ($attachments as $attachment) {
            Storage::disk('public')->assertExists($attachment->file_path);
        }
    }

    /**
     * Test post creation with both images and files
     */
    public function test_can_create_post_with_mixed_uploads(): void
    {
        // Given: Valid post data with both images and files
        $image = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(1024);
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $postData = [
            'title' => 'Test Post with Mixed Files',
            'content' => 'This post contains both images and attachments.',
            'name' => 'Test Author',
            'images' => [$image],
            'attachments' => [$file],
        ];

        // When: Submit post creation request
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should redirect successfully
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // And: Both images and attachments should be saved
        $post = DB::table('site_board_testupload')->where('title', 'Test Post with Mixed Files')->first();
        $this->assertDatabaseHas('board_images', [
            'post_id' => $post->id,
            'original_name' => 'photo.jpg',
        ]);
        $this->assertDatabaseHas('board_attachments', [
            'post_id' => $post->id,
            'original_name' => 'document.pdf',
        ]);
    }

    /**
     * Test file security validation blocks dangerous files
     */
    public function test_blocks_dangerous_file_uploads(): void
    {
        // Given: Post data with blocked file types
        $dangerousFile = UploadedFile::fake()->create('virus.exe', 1024, 'application/x-executable');

        $postData = [
            'title' => 'Test Post with Dangerous File',
            'content' => 'This should be blocked.',
            'name' => 'Test Author',
            'attachments' => [$dangerousFile],
        ];

        // When: Submit post creation request with dangerous file
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should redirect with success but blocked file message
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // And: Post should be created but no files saved
        $post = DB::table('site_board_testupload')->where('title', 'Test Post with Dangerous File')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseMissing('board_attachments', [
            'post_id' => $post->id,
        ]);
    }

    /**
     * Test file size limit enforcement
     */
    public function test_enforces_file_size_limits(): void
    {
        // Given: Large file exceeding 5MB limit
        $largeFile = UploadedFile::fake()->create('large.pdf', 6144); // 6MB

        $postData = [
            'title' => 'Test Post with Large File',
            'content' => 'This file is too large.',
            'name' => 'Test Author',
            'attachments' => [$largeFile],
        ];

        // When: Submit post creation request with large file
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should return validation error
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['attachments.0']);
    }

    /**
     * Test maximum file count limit
     */
    public function test_respects_max_file_count_limit(): void
    {
        // Given: More files than allowed (board allows max 5)
        $files = [];
        for ($i = 1; $i <= 7; $i++) {
            $files[] = UploadedFile::fake()->image("image{$i}.jpg", 300, 300)->size(512);
        }

        $postData = [
            'title' => 'Test Post with Too Many Files',
            'content' => 'This has too many files.',
            'name' => 'Test Author',
            'images' => $files,
        ];

        // When: Submit post creation request with too many files
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should redirect successfully but only save first 5 files
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // And: Only 5 files should be saved due to JavaScript validation
        $post = DB::table('site_board_testupload')->where('title', 'Test Post with Too Many Files')->first();
        $imageCount = DB::table('board_images')->where('post_id', $post->id)->count();
        $this->assertLessThanOrEqual(5, $imageCount);
    }

    /**
     * Test file upload when uploads are disabled
     */
    public function test_hides_upload_sections_when_disabled(): void
    {
        // Given: Board with uploads disabled
        DB::table('site_board')->where('code', 'testupload')->update([
            'allow_file_upload' => false,
            'allow_image_upload' => false,
        ]);

        // When: Access post creation form
        $response = $this->get('/admin/cms/board/posts/testupload/create');

        // Then: Should not display file upload sections
        $response->assertStatus(200);
        $response->assertDontSee('File Attachments');
        $response->assertDontSee('Drag & drop images here');
        $response->assertDontSee('Drag & drop files here');
    }

    /**
     * Test file paths are correctly stored
     */
    public function test_stores_correct_file_paths(): void
    {
        // Given: Valid image file
        $image = UploadedFile::fake()->image('test.jpg', 400, 300)->size(1024);

        $postData = [
            'title' => 'Test File Path Storage',
            'content' => 'Testing file path storage.',
            'name' => 'Test Author',
            'images' => [$image],
        ];

        // When: Submit post creation request
        $response = $this->post('/admin/cms/board/posts/testupload', $postData);

        // Then: Should store correct file path structure
        $response->assertStatus(302);

        $post = DB::table('site_board_testupload')->where('title', 'Test File Path Storage')->first();
        $savedImage = DB::table('board_images')->where('post_id', $post->id)->first();

        // Path should follow pattern: board/{code}/images/{filename}
        $this->assertStringStartsWith('board/testupload/images/', $savedImage->file_path);
        $this->assertStringEndsWith('.jpg', $savedImage->filename);
    }

    /**
     * Test unique filename generation
     */
    public function test_generates_unique_filenames(): void
    {
        // Given: Two identical filenames
        $image1 = UploadedFile::fake()->image('duplicate.jpg', 400, 300)->size(1024);
        $image2 = UploadedFile::fake()->image('duplicate.jpg', 400, 300)->size(1024);

        $postData1 = [
            'title' => 'First Post',
            'name' => 'Test Author',
            'images' => [$image1],
        ];

        $postData2 = [
            'title' => 'Second Post',
            'name' => 'Test Author',
            'images' => [$image2],
        ];

        // When: Upload both files
        $this->post('/admin/cms/board/posts/testupload', $postData1);
        $this->post('/admin/cms/board/posts/testupload', $postData2);

        // Then: Should generate unique filenames
        $images = DB::table('board_images')->get();
        $filenames = $images->pluck('filename')->toArray();

        $this->assertCount(2, $filenames);
        $this->assertNotEquals($filenames[0], $filenames[1]);
    }
}