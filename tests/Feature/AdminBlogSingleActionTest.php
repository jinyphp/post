<?php

namespace Jiny\Post\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * AdminBlog Single Action Controllers TDD Test
 *
 * Single Action Controller 방식의 AdminBlog CRUD 시스템을 테스트합니다.
 */
class AdminBlogSingleActionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary tables
        $this->createBlogTables();

        // Create a test admin user
        $this->createAdminUser();
    }

    protected function createBlogTables()
    {
        // Create site_blog table
        DB::statement('
            CREATE TABLE IF NOT EXISTS site_blog (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255),
                content TEXT,
                excerpt TEXT,
                featured_image VARCHAR(255),
                featured_image_alt VARCHAR(255),
                category_slug VARCHAR(255),
                tags TEXT,
                keywords TEXT,
                status VARCHAR(50) DEFAULT "draft",
                allow_comments BOOLEAN DEFAULT 1,
                is_featured BOOLEAN DEFAULT 0,
                is_sticky BOOLEAN DEFAULT 0,
                user_id INTEGER,
                author_name VARCHAR(255),
                author_email VARCHAR(255),
                author_bio TEXT,
                meta_title VARCHAR(255),
                meta_description TEXT,
                meta_keywords TEXT,
                views INTEGER DEFAULT 0,
                likes INTEGER DEFAULT 0,
                shares INTEGER DEFAULT 0,
                published_at DATETIME,
                scheduled_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');

        // Create site_blog_cate table
        DB::statement('
            CREATE TABLE IF NOT EXISTS site_blog_cate (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                description TEXT,
                color VARCHAR(7) DEFAULT "#6c757d",
                icon VARCHAR(255),
                is_active BOOLEAN DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
    }

    protected function createAdminUser()
    {
        // Create users table if not exists
        DB::statement('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                isAdmin BOOLEAN DEFAULT 0,
                utype VARCHAR(50),
                is_blocked BOOLEAN DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');

        // Create admin_user_types table if not exists
        DB::statement('
            CREATE TABLE IF NOT EXISTS admin_user_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                enable BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');

        // Insert admin user type
        DB::table('admin_user_types')->insert([
            'name' => 'Administrator',
            'code' => 'admin',
            'enable' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert admin user
        DB::table('users')->insert([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Mock admin middleware by directly setting the authenticated user
        $adminUser = (object) [
            'id' => 1,
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'isAdmin' => true,
            'utype' => 'Administrator'
        ];

        $this->actingAs($adminUser);
    }

    /** @test */
    public function test_admin_blog_index_displays_blog_list()
    {
        // Create test blog posts
        DB::table('site_blog')->insert([
            [
                'title' => 'Test Blog 1',
                'content' => 'Test content 1',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Test Blog 2',
                'content' => 'Test content 2',
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $response = $this->withoutMiddleware(['admin'])
                         ->get('/admin/cms/blog');

        $response->assertStatus(200);
        $response->assertSee('Test Blog 1');
        $response->assertSee('Test Blog 2');
    }

    /** @test */
    public function test_admin_blog_create_form_displays()
    {
        $response = $this->withoutMiddleware(['admin'])
                         ->get('/admin/cms/blog/create');

        $response->assertStatus(200);
        $response->assertSee('새 블로그 글 작성');
        $response->assertSee('제목');
        $response->assertSee('내용');
    }

    /** @test */
    public function test_admin_blog_create_stores_new_blog()
    {
        Storage::fake('public');

        $blogData = [
            'title' => 'New Test Blog',
            'content' => 'This is a new test blog content.',
            'status' => 'published',
            'excerpt' => 'Test excerpt',
            'allow_comments' => '1',
            'is_featured' => '1'
        ];

        $response = $this->withoutMiddleware(['admin'])
                         ->post('/admin/cms/blog/create', $blogData);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/blog');

        $this->assertDatabaseHas('site_blog', [
            'title' => 'New Test Blog',
            'content' => 'This is a new test blog content.',
            'status' => 'published'
        ]);
    }

    /** @test */
    public function test_admin_blog_create_with_ajax_returns_json()
    {
        $blogData = [
            'title' => 'AJAX Test Blog',
            'content' => 'AJAX test content',
            'status' => 'draft'
        ];

        $response = $this->withoutMiddleware(['admin'])
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->post('/admin/cms/blog/create', $blogData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '블로그 글이 생성되었습니다.'
        ]);

        $this->assertDatabaseHas('site_blog', [
            'title' => 'AJAX Test Blog',
            'status' => 'draft'
        ]);
    }

    /** @test */
    public function test_admin_blog_show_displays_blog_details()
    {
        $blogId = DB::table('site_blog')->insertGetId([
            'title' => 'Test Blog Detail',
            'content' => 'Test content for detail view',
            'status' => 'published',
            'views' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->withoutMiddleware(['admin'])
                         ->get("/admin/cms/blog/{$blogId}");

        $response->assertStatus(200);
        $response->assertSee('Test Blog Detail');
        $response->assertSee('Test content for detail view');

        // Check that views are incremented
        $blog = DB::table('site_blog')->find($blogId);
        $this->assertEquals(1, $blog->views);
    }

    /** @test */
    public function test_admin_blog_edit_form_displays()
    {
        $blogId = DB::table('site_blog')->insertGetId([
            'title' => 'Test Blog Edit',
            'content' => 'Test content for edit',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->withoutMiddleware(['admin'])
                         ->get("/admin/cms/blog/{$blogId}/edit");

        $response->assertStatus(200);
        $response->assertSee('블로그 글 수정');
        $response->assertSee('Test Blog Edit');
        $response->assertSee('Test content for edit');
    }

    /** @test */
    public function test_admin_blog_edit_updates_blog()
    {
        $blogId = DB::table('site_blog')->insertGetId([
            'title' => 'Original Title',
            'content' => 'Original content',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published'
        ];

        $response = $this->withoutMiddleware(['admin'])
                         ->put("/admin/cms/blog/{$blogId}/edit", $updateData);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/blog');

        $this->assertDatabaseHas('site_blog', [
            'id' => $blogId,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published'
        ]);
    }

    /** @test */
    public function test_admin_blog_edit_with_ajax_returns_json()
    {
        $blogId = DB::table('site_blog')->insertGetId([
            'title' => 'AJAX Update Test',
            'content' => 'Original content',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $updateData = [
            'title' => 'AJAX Updated Title',
            'content' => 'AJAX updated content',
            'status' => 'published'
        ];

        $response = $this->withoutMiddleware(['admin'])
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->put("/admin/cms/blog/{$blogId}/edit", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '블로그 글이 수정되었습니다.'
        ]);

        $this->assertDatabaseHas('site_blog', [
            'id' => $blogId,
            'title' => 'AJAX Updated Title',
            'status' => 'published'
        ]);
    }

    /** @test */
    public function test_admin_blog_delete_removes_blog()
    {
        $blogId = DB::table('site_blog')->insertGetId([
            'title' => 'Blog to Delete',
            'content' => 'This blog will be deleted',
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->withoutMiddleware(['admin'])
                         ->delete("/admin/cms/blog/{$blogId}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/blog');

        $this->assertDatabaseMissing('site_blog', [
            'id' => $blogId
        ]);
    }

    /** @test */
    public function test_admin_blog_delete_with_ajax_returns_json()
    {
        $blogId = DB::table('site_blog')->insertGetId([
            'title' => 'AJAX Delete Test',
            'content' => 'This blog will be deleted via AJAX',
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->withoutMiddleware(['admin'])
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->delete("/admin/cms/blog/{$blogId}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '블로그 글이 삭제되었습니다.'
        ]);

        $this->assertDatabaseMissing('site_blog', [
            'id' => $blogId
        ]);
    }

    /** @test */
    public function test_admin_blog_routes_use_single_action_controllers()
    {
        $routes = Route::getRoutes();

        // Index route
        $indexRoute = $routes->getByName('admin.cms.blog');
        $this->assertNotNull($indexRoute);
        $this->assertStringContains('AdminBlogIndex', $indexRoute->getActionName());

        // Create route
        $createRoute = $routes->getByName('admin.cms.blog.create');
        $this->assertNotNull($createRoute);
        $this->assertStringContains('AdminBlogCreate', $createRoute->getActionName());

        // Show route
        $showRoute = $routes->getByName('admin.cms.blog.show');
        $this->assertNotNull($showRoute);
        $this->assertStringContains('AdminBlogShow', $showRoute->getActionName());

        // Edit route
        $editRoute = $routes->getByName('admin.cms.blog.edit');
        $this->assertNotNull($editRoute);
        $this->assertStringContains('AdminBlogEdit', $editRoute->getActionName());

        // Delete route
        $deleteRoute = $routes->getByName('admin.cms.blog.destroy');
        $this->assertNotNull($deleteRoute);
        $this->assertStringContains('AdminBlogDelete', $deleteRoute->getActionName());
    }

    /** @test */
    public function test_validation_errors_are_handled_properly()
    {
        // Test create validation
        $response = $this->withoutMiddleware(['admin'])
                         ->post('/admin/cms/blog/create', []);

        $response->assertSessionHasErrors(['title', 'content']);

        // Test AJAX validation
        $response = $this->withoutMiddleware(['admin'])
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->post('/admin/cms/blog/create', []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors'
        ]);
    }

    /** @test */
    public function test_file_upload_works_correctly()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $blogData = [
            'title' => 'Blog with Image',
            'content' => 'Blog content with image',
            'status' => 'published',
            'featured_image_file' => $file
        ];

        $response = $this->withoutMiddleware(['admin'])
                         ->post('/admin/cms/blog/create', $blogData);

        $response->assertStatus(302);

        $blog = DB::table('site_blog')->where('title', 'Blog with Image')->first();
        $this->assertNotNull($blog->featured_image);
        $this->assertStringContains('/storage/', $blog->featured_image);

        // Check that file was stored
        $imagePath = str_replace('/storage/', '', $blog->featured_image);
        Storage::disk('public')->assertExists($imagePath);
    }

    /** @test */
    public function test_slug_generation_works_correctly()
    {
        $blogData = [
            'title' => 'Test Slug Generation 테스트',
            'content' => 'Content for slug test',
            'status' => 'published'
        ];

        $response = $this->withoutMiddleware(['admin'])
                         ->post('/admin/cms/blog/create', $blogData);

        $response->assertStatus(302);

        $blog = DB::table('site_blog')->where('title', 'Test Slug Generation 테스트')->first();
        $this->assertNotNull($blog->slug);
        $this->assertStringContains('test-slug-generation', $blog->slug);
    }

    /** @test */
    public function test_nonexistent_blog_returns_404()
    {
        $response = $this->withoutMiddleware(['admin'])
                         ->get('/admin/cms/blog/999999');

        $response->assertStatus(302); // Redirect to blog list with error

        $response = $this->withoutMiddleware(['admin'])
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->get('/admin/cms/blog/999999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false
        ]);
    }
}