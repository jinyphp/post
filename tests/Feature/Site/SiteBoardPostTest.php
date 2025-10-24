<?php

namespace Jiny\Post\Tests\Feature\Site;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class SiteBoardPostTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $boardCode = 'aaaa';

    protected function setUp(): void
    {
        parent::setUp();

        // 사용자 생성
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'isAdmin' => '1',
            'utype' => 'super'
        ]);

        // 게시판 테이블 생성
        $this->createBoardTable();

        // 게시판 설정 생성
        $this->createBoardConfig();
    }

    protected function createBoardTable()
    {
        $tableName = "site_board_{$this->boardCode}";

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function ($table) {
                $table->id();
                $table->string('code');
                $table->string('title');
                $table->text('content');
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_uuid')->nullable();
                $table->integer('shard_id')->nullable();
                $table->string('uuid')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('level')->default(0);
                $table->integer('click')->default(0);
                $table->timestamps();
            });
        }
    }

    protected function createBoardConfig()
    {
        // site_board 테이블이 없으면 생성
        if (!Schema::hasTable('site_board')) {
            Schema::create('site_board', function ($table) {
                $table->id();
                $table->timestamps();
                $table->string('enable')->nullable();
                $table->string('code');
                $table->string('slug')->nullable();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('image')->nullable();
                $table->text('header')->nullable();
                $table->text('footer')->nullable();
                $table->string('view_layout')->nullable();
                $table->string('view_table')->nullable();
                $table->string('view_list')->nullable();
                $table->string('view_filter')->nullable();
                $table->string('view_create')->nullable();
                $table->string('view_detail')->nullable();
                $table->string('view_edit')->nullable();
                $table->string('view_form')->nullable();
                $table->string('permit_read')->nullable();
                $table->string('permit_create')->nullable();
                $table->string('permit_edit')->nullable();
                $table->string('permit_delete')->nullable();
                $table->text('description')->nullable();
                $table->string('manager')->nullable();
                $table->integer('post')->default(0);
                $table->integer('per_page')->default(10);
                $table->integer('sort_order')->default(0);
                $table->string('category')->nullable();
                $table->boolean('use_comment')->default(true);
                $table->boolean('use_rating')->default(false);
                $table->boolean('use_like')->default(true);
                $table->integer('max_file_size')->default(5120);
                $table->string('allowed_extensions')->default('jpg,jpeg,png,gif,pdf,doc,docx');
                $table->text('extra_fields')->nullable();
                $table->integer('total_views')->default(0);
                $table->datetime('last_post_at')->nullable();
                $table->string('write_permission')->default('guest_allowed');
                $table->string('read_permission')->default('guest_allowed');
                $table->string('comment_permission')->default('guest_allowed');
                $table->boolean('allow_file_upload')->default(true);
                $table->boolean('allow_image_upload')->default(true);
                $table->integer('max_file_count')->default(5);
                $table->text('blocked_extensions')->nullable();
            });
        }

        DB::table('site_board')->insert([
            'code' => $this->boardCode,
            'title' => 'Test Board',
            'description' => 'Test board for file upload',
            'allow_image_upload' => 1,
            'allow_file_upload' => 1,
            'max_file_size' => 5120, // 5MB
            'blocked_extensions' => 'exe,bat,cmd',
            'enable' => '1',
            'max_file_count' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_user_can_create_post_with_images()
    {
        // Storage 디스크 fake
        Storage::fake('public');

        // 테스트 이미지 파일 생성
        $imageFile = UploadedFile::fake()->image('test-image.jpg', 100, 100)->size(1024); // 1MB

        // 로그인
        $this->actingAs($this->user);

        // 포스트 생성 요청
        $response = $this->post(route('board.store', $this->boardCode), [
            'title' => 'Image Test Post',
            'content' => 'This is a test post with image.',
            'images' => [$imageFile]
        ]);

        // 리다이렉션 확인
        $response->assertRedirect(route('board.index', $this->boardCode));
        $response->assertSessionHas('success');

        // 데이터베이스에 포스트가 저장되었는지 확인
        $this->assertDatabaseHas("site_board_{$this->boardCode}", [
            'title' => 'Image Test Post',
            'content' => 'This is a test post with image.',
        ]);

        // 포스트 ID 가져오기
        $post = DB::table("site_board_{$this->boardCode}")
            ->where('title', 'Image Test Post')
            ->first();

        $this->assertNotNull($post, 'Post was not saved to database.');

        // 이미지 테이블이 생성되었는지 확인
        $imageTableName = "site_board_{$this->boardCode}_images";
        $this->assertTrue(Schema::hasTable($imageTableName), 'Image table was not created.');

        // 이미지 데이터가 저장되었는지 확인
        $this->assertDatabaseHas($imageTableName, [
            'post_id' => $post->id,
            'original_name' => 'test-image.jpg',
        ]);

        // 실제 파일이 저장되었는지 확인
        $imageRecord = DB::table($imageTableName)
            ->where('post_id', $post->id)
            ->first();

        $this->assertNotNull($imageRecord, 'Image record not found in database.');

        Storage::disk('public')->assertExists($imageRecord->file_path);

        // 파일 경로가 계층적 구조인지 확인
        $expectedPathPattern = "board/{$this->boardCode}/images/";
        $this->assertStringContainsString($expectedPathPattern, $imageRecord->file_path);
    }

    public function test_user_can_create_post_with_attachments()
    {
        // Storage 디스크 fake
        Storage::fake('public');

        // 테스트 첨부파일 생성
        $attachmentFile = UploadedFile::fake()->create('test-document.pdf', 1024); // 1MB

        // 로그인
        $this->actingAs($this->user);

        // 포스트 생성 요청
        $response = $this->post(route('board.store', $this->boardCode), [
            'title' => 'Attachment Test Post',
            'content' => 'This is a test post with attachment.',
            'attachments' => [$attachmentFile]
        ]);

        // 리다이렉션 확인
        $response->assertRedirect(route('board.index', $this->boardCode));
        $response->assertSessionHas('success');

        // 데이터베이스에 포스트가 저장되었는지 확인
        $this->assertDatabaseHas("site_board_{$this->boardCode}", [
            'title' => 'Attachment Test Post',
            'content' => 'This is a test post with attachment.',
        ]);

        // 포스트 ID 가져오기
        $post = DB::table("site_board_{$this->boardCode}")
            ->where('title', 'Attachment Test Post')
            ->first();

        $this->assertNotNull($post, 'Post was not saved to database.');

        // 첨부파일 테이블이 생성되었는지 확인
        $attachmentTableName = "site_board_{$this->boardCode}_attachments";
        $this->assertTrue(Schema::hasTable($attachmentTableName), 'Attachment table was not created.');

        // 첨부파일 데이터가 저장되었는지 확인
        $this->assertDatabaseHas($attachmentTableName, [
            'post_id' => $post->id,
            'original_name' => 'test-document.pdf',
        ]);

        // 실제 파일이 저장되었는지 확인
        $attachmentRecord = DB::table($attachmentTableName)
            ->where('post_id', $post->id)
            ->first();

        $this->assertNotNull($attachmentRecord, 'Attachment record not found in database.');

        Storage::disk('public')->assertExists($attachmentRecord->file_path);

        // 파일 경로가 계층적 구조인지 확인
        $expectedPathPattern = "board/{$this->boardCode}/attachments/";
        $this->assertStringContainsString($expectedPathPattern, $attachmentRecord->file_path);
    }

    public function test_user_can_create_post_with_both_images_and_attachments()
    {
        // Storage 디스크 fake
        Storage::fake('public');

        // 테스트 파일 생성
        $imageFile = UploadedFile::fake()->image('test-image.png', 200, 200)->size(512);
        $attachmentFile = UploadedFile::fake()->create('test-file.txt', 100);

        // 로그인
        $this->actingAs($this->user);

        // 포스트 생성 요청
        $response = $this->post(route('board.store', $this->boardCode), [
            'title' => 'Combined Files Post',
            'content' => 'This post has both images and attachments.',
            'images' => [$imageFile],
            'attachments' => [$attachmentFile]
        ]);

        // 리다이렉션 확인
        $response->assertRedirect(route('board.index', $this->boardCode));
        $response->assertSessionHas('success');

        // 포스트가 저장되었는지 확인
        $post = DB::table("site_board_{$this->boardCode}")
            ->where('title', 'Combined Files Post')
            ->first();

        $this->assertNotNull($post);

        // 이미지가 저장되었는지 확인
        $imageTableName = "site_board_{$this->boardCode}_images";
        $this->assertDatabaseHas($imageTableName, [
            'post_id' => $post->id,
            'original_name' => 'test-image.png',
        ]);

        // 첨부파일이 저장되었는지 확인
        $attachmentTableName = "site_board_{$this->boardCode}_attachments";
        $this->assertDatabaseHas($attachmentTableName, [
            'post_id' => $post->id,
            'original_name' => 'test-file.txt',
        ]);

        // 실제 파일들이 저장되었는지 확인
        $imageRecord = DB::table($imageTableName)->where('post_id', $post->id)->first();
        $attachmentRecord = DB::table($attachmentTableName)->where('post_id', $post->id)->first();

        Storage::disk('public')->assertExists($imageRecord->file_path);
        Storage::disk('public')->assertExists($attachmentRecord->file_path);
    }

    protected function tearDown(): void
    {
        // 테스트 후 테이블 정리
        $tableNames = [
            "site_board_{$this->boardCode}",
            "site_board_{$this->boardCode}_images",
            "site_board_{$this->boardCode}_attachments",
            "site_board"
        ];

        foreach ($tableNames as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::dropIfExists($tableName);
            }
        }

        parent::tearDown();
    }
}