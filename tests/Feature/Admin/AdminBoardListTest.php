<?php

namespace Jiny\Post\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * AdminBoardList Feature Tests
 *
 * TDD test cases for multi-board management system
 * Tests code and slug based multi-table board CRUD functionality
 */
class AdminBoardListTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup admin authentication for tests
        $this->withoutMiddleware();

        // Add permission fields to site_board table for testing
        $this->addPermissionFieldsToSiteBoardTable();
    }

    /**
     * Add permission fields to site_board table for testing
     */
    private function addPermissionFieldsToSiteBoardTable(): void
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
            });
        }

        // Add permission fields if they don't exist
        if (!Schema::hasColumn('site_board', 'write_permission')) {
            Schema::table('site_board', function ($table) {
                $table->string('write_permission')->default('guest_allowed');
                $table->string('read_permission')->default('guest_allowed');
                $table->string('comment_permission')->default('guest_allowed');
            });
        }

        // Add file upload fields if they don't exist
        if (!Schema::hasColumn('site_board', 'allow_file_upload')) {
            Schema::table('site_board', function ($table) {
                $table->boolean('allow_file_upload')->default(true);
                $table->boolean('allow_image_upload')->default(true);
                $table->unsignedInteger('max_file_count')->default(5);
                $table->text('blocked_extensions')->nullable();
            });
        }
    }

    /**
     * Test board list page access
     */
    public function test_can_access_board_list_page(): void
    {
        // When: Access board list page
        $response = $this->get('/admin/cms/board/list');

        // Then: Should respond successfully
        $response->assertStatus(200);
        $response->assertViewIs('jiny-post::admin.board_list.index');
    }

    /**
     * Test board create form page access
     */
    public function test_can_access_board_create_form(): void
    {
        // When: Access board create form page
        $response = $this->get('/admin/cms/board/list/create');

        // Then: Should respond successfully
        $response->assertStatus(200);
        $response->assertViewIs('jiny-post::admin.board_list.create');
    }

    /**
     * Test creating board with all new file upload fields
     */
    public function test_can_create_board_with_file_upload_fields(): void
    {
        // Given: Board creation data with all file upload fields
        $boardData = [
            'enable' => 1,
            'code' => 'test_file_board',
            'slug' => '/board/test-file',
            'title' => 'Test File Board',
            'subtitle' => 'Test board with file upload features',
            'category' => 'test',
            'sort_order' => 0,
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'per_page' => 10,
            'permit_read' => 'public',
            'permit_create' => 'member',
            'permit_edit' => 'owner',
            'permit_delete' => 'owner',
            'use_comment' => 1,
            'use_rating' => 0,
            'use_like' => 1,
            // File upload fields that are missing from site_board table
            'allow_file_upload' => 1,
            'allow_image_upload' => 1,
            'max_file_size' => 5120,
            'max_file_count' => 5,
            'allowed_extensions' => 'jpg,jpeg,png,gif,pdf,doc,docx,hwp,txt',
            'blocked_extensions' => 'exe,bat,cmd,com,pif,scr,vbs,js,jar,php,asp,jsp',
            'view_layout' => '',
            'view_create' => '',
            'view_detail' => '',
            'view_edit' => '',
            'view_table' => '',
            'view_list' => '',
            'view_filter' => '',
            'header' => '',
            'footer' => '',
            'manager' => 'Test Manager',
            'description' => 'Test board description'
        ];

        // When: Submit board creation request
        $response = $this->post('/admin/cms/board/list', $boardData);

        // Then: Should redirect successfully without database errors
        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/board/list');
        $response->assertSessionHas('success');

        // And: Board should be saved in database with all fields
        $this->assertDatabaseHas('site_board', [
            'code' => 'test_file_board',
            'title' => 'Test File Board',
            'allow_file_upload' => 1,
            'allow_image_upload' => 1,
            'max_file_count' => 5,
            'blocked_extensions' => 'exe,bat,cmd,com,pif,scr,vbs,js,jar,php,asp,jsp'
        ]);
    }

    /**
     * Test new board creation with auto table generation
     */
    public function test_can_create_new_board_with_auto_table_generation(): void
    {
        // Given: Board creation data
        $boardData = [
            'title' => 'Test Board',
            'subtitle' => 'Test board description',
            'description' => 'Test description',
            'enable' => true,
            'write_permission' => 'member_only',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'member_only',
        ];

        // When: Submit board creation request
        $response = $this->post('/admin/cms/board/list', $boardData);

        // Then: Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/board/list');
        $response->assertSessionHas('success');

        // And: Board should be saved in database
        $this->assertDatabaseHas('site_board', [
            'title' => 'Test Board',
            'subtitle' => 'Test board description',
            'write_permission' => 'member_only',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'member_only',
        ]);

        // And: Auto-generated code should exist
        $board = DB::table('site_board')->where('title', 'Test Board')->first();
        $this->assertNotNull($board->code);
        $this->assertEquals(7, strlen($board->code)); // First 7 chars of MD5 hash

        // And: Related tables should be auto-created
        $this->assertTrue(Schema::hasTable("site_board_" . $board->code));
        $this->assertTrue(Schema::hasTable("site_board_" . $board->code . "_comments"));
        $this->assertTrue(Schema::hasTable("site_board_" . $board->code . "_ratings"));
    }

    /**
     * Test board edit form access
     */
    public function test_can_access_board_edit_form(): void
    {
        // Given: Existing board
        $boardId = DB::table('site_board')->insertGetId([
            'title' => 'Board to Edit',
            'code' => 'test123',
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: Access board edit form
        $response = $this->get("/admin/cms/board/list/{$boardId}/edit");

        // Then: Should respond successfully
        $response->assertStatus(200);
        $response->assertViewIs('jiny-post::admin.board_list.edit');
        $response->assertViewHas('item');
    }

    /**
     * Test board information update
     */
    public function test_can_update_board_information(): void
    {
        // Given: Existing board
        $boardId = DB::table('site_board')->insertGetId([
            'title' => 'Original Title',
            'code' => 'test123',
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'subtitle' => 'Updated Subtitle',
            'write_permission' => 'admin_only',
            'read_permission' => 'member_only',
            'comment_permission' => 'admin_only',
        ];

        // When: Submit board update request
        $response = $this->put("/admin/cms/board/list/{$boardId}", $updateData);

        // Then: Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/board/list');

        // And: Updated data should be reflected in database
        $this->assertDatabaseHas('site_board', [
            'id' => $boardId,
            'title' => 'Updated Title',
            'subtitle' => 'Updated Subtitle',
            'write_permission' => 'admin_only',
            'read_permission' => 'member_only',
            'comment_permission' => 'admin_only',
        ]);
    }

    /**
     * Test board deletion with related tables
     */
    public function test_can_delete_board_with_related_tables(): void
    {
        // Given: Existing board with related tables
        $boardId = DB::table('site_board')->insertGetId([
            'title' => 'Board to Delete',
            'code' => 'delete1',
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create related tables manually (normally auto-created but for testing)
        Schema::create('site_board_delete1', function($table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('site_board_delete1_comments', function($table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('site_board_delete1_ratings', function($table) {
            $table->id();
            $table->timestamps();
        });

        // When: Submit board deletion request
        $response = $this->delete("/admin/cms/board/list/{$boardId}");

        // Then: Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/board/list');
        $response->assertSessionHas('success');

        // And: Board should be deleted from database
        $this->assertDatabaseMissing('site_board', ['id' => $boardId]);

        // And: Related tables should also be deleted
        $this->assertFalse(Schema::hasTable('site_board_delete1'));
        $this->assertFalse(Schema::hasTable('site_board_delete1_comments'));
        $this->assertFalse(Schema::hasTable('site_board_delete1_ratings'));
    }

    /**
     * Test board search functionality
     */
    public function test_can_search_boards_by_title_and_code(): void
    {
        // Given: Multiple boards exist
        DB::table('site_board')->insert([
            [
                'title' => 'Search Test Board',
                'code' => 'search1',
                'write_permission' => 'guest_allowed',
                'read_permission' => 'guest_allowed',
                'comment_permission' => 'guest_allowed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Other Board',
                'code' => 'other1',
                'write_permission' => 'guest_allowed',
                'read_permission' => 'guest_allowed',
                'comment_permission' => 'guest_allowed',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // When: Search by title
        $response = $this->get('/admin/cms/board/list?search=Search');

        // Then: Search results should be displayed correctly
        $response->assertStatus(200);
        $response->assertSeeText('Search Test Board');
        $response->assertDontSeeText('Other Board');
    }

    /**
     * Test filtering boards by permission
     */
    public function test_can_filter_boards_by_permission(): void
    {
        // Given: Boards with different permissions exist
        DB::table('site_board')->insert([
            [
                'title' => 'Admin Only Board',
                'code' => 'admin1',
                'write_permission' => 'admin_only',
                'read_permission' => 'guest_allowed',
                'comment_permission' => 'guest_allowed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Member Only Board',
                'code' => 'member1',
                'write_permission' => 'member_only',
                'read_permission' => 'guest_allowed',
                'comment_permission' => 'guest_allowed',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // When: Filter by admin_only permission
        $response = $this->get('/admin/cms/board/list?permission=admin_only');

        // Then: Only boards with that permission should be displayed
        $response->assertStatus(200);
        $response->assertSeeText('Admin Only Board');
        $response->assertDontSeeText('Member Only Board');
    }

    /**
     * Test board permission management via API
     */
    public function test_can_manage_board_permissions_via_api(): void
    {
        // Given: Board exists
        $boardId = DB::table('site_board')->insertGetId([
            'title' => 'Permission Test Board',
            'code' => 'perm1',
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: Submit bulk permission update request
        $response = $this->post('/admin/cms/board/list/permissions/bulk-update', [
            'board_ids' => [$boardId],
            'write_permission' => 'admin_only',
            'read_permission' => 'member_only',
            'comment_permission' => 'admin_only',
        ]);

        // Then: Should respond successfully
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        // And: Permissions should be updated
        $this->assertDatabaseHas('site_board', [
            'id' => $boardId,
            'write_permission' => 'admin_only',
            'read_permission' => 'member_only',
            'comment_permission' => 'admin_only',
        ]);
    }

    /**
     * Test rating table migration for existing boards
     */
    public function test_can_migrate_rating_tables_for_existing_boards(): void
    {
        // Given: Existing board without rating table
        DB::table('site_board')->insert([
            'title' => 'Migration Test',
            'code' => 'migrate1',
            'write_permission' => 'guest_allowed',
            'read_permission' => 'guest_allowed',
            'comment_permission' => 'guest_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: Execute rating table migration
        $response = $this->post('/admin/cms/board/migrate-rating-tables');

        // Then: Should respond successfully
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        // And: Rating table should be created
        $this->assertTrue(Schema::hasTable('site_board_migrate1_ratings'));
    }

    /**
     * Test graceful handling of nonexistent board access
     */
    public function test_handles_nonexistent_board_access_gracefully(): void
    {
        // When: Try to edit nonexistent board
        $response = $this->get('/admin/cms/board/list/99999/edit');

        // Then: Should handle error appropriately
        $response->assertStatus(302); // Redirect
        $response->assertRedirect('/admin/cms/board/list');
        $response->assertSessionHas('error');
    }
}