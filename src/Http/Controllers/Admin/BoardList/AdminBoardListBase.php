<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Base class for AdminBoardList controllers
 *
 * 다중 게시판 시스템의 핵심 베이스 클래스입니다.
 * 코드와 slug 기반의 멀티 테이블 관리를 위한 공통 기능을 제공합니다.
 *
 * 주요 기능:
 * - 게시판별 독립적인 테이블 구조 생성 및 관리
 * - 세밀한 권한 관리 시스템 (읽기/쓰기/댓글 권한별 제어)
 * - 통합 평가 시스템 (좋아요/별점) 지원
 * - 샤딩 구조를 통한 확장성 보장
 * - MigrateRatingTableController 기능 통합
 *
 * @package Jiny\Post\Http\Controllers\Admin\BoardList
 * @author Jiny Framework
 * @version 1.0
 */
abstract class AdminBoardListBase extends Controller
{
    /**
     * 테이블명
     */
    protected $table = 'site_board';

    /**
     * 뷰 경로
     */
    protected $viewPath = 'jiny-post::admin.board_list';

    /**
     * 페이지 설정
     */
    protected $config = [
        'title' => '계시판',
        'subtitle' => '복수의 계시판을 관리합니다.',
    ];

    /**
     * 게시판 권한 옵션
     */
    protected $permissionOptions = [
        'admin_only' => '관리자만 글쓰기 허용',
        'member_only' => '회원만 글쓰기 허용',
        'guest_allowed' => '비회원 글쓰기 허용',
    ];

    /**
     * 게시판 테이블 스키마 생성 및 권한 설정
     *
     * 새로운 게시판 생성 시 필요한 모든 테이블을 자동으로 생성합니다.
     * 메인 게시판 테이블, 댓글 테이블, 평가 테이블, 첨부파일 테이블을 한번에 생성하며
     * 권한 설정을 테이블 스키마에 반영합니다.
     *
     * @param string $code 게시판 고유 코드 (테이블명 생성에 사용)
     * @param array $permissions 권한 설정 배열
     *                          ['write_permission' => 'admin_only|member_only|guest_allowed',
     *                           'read_permission' => 'admin_only|member_only|guest_allowed',
     *                           'comment_permission' => 'admin_only|member_only|guest_allowed']
     * @return void
     */
    protected function createBoardTables($code, $permissions = [])
    {
        // 메인 게시판 테이블 생성
        $this->schemaCreate("site_board_" . $code, $permissions);

        // 코멘트 테이블 생성
        $this->createCommentTable("site_board_" . $code . "_comments");

        // 평가 테이블 생성 (MigrateRatingTableController 기능 통합)
        $this->createRatingTable("site_board_" . $code . "_ratings");

        // 첨부파일 테이블 생성
        $this->createAttachmentTable("site_board_" . $code . "_attachments");

        // 이미지 테이블 생성
        $this->createImageTable("site_board_" . $code . "_images");
    }

    /**
     * 게시판 테이블 스키마 생성 (권한 관리 기능 추가)
     *
     * 게시판별 독립적인 테이블을 생성합니다.
     * 권한 필드, 계층형 구조, 샤딩 지원을 포함한 완전한 스키마를 구성합니다.
     *
     * 생성되는 주요 필드:
     * - 권한 관리: write_permission, read_permission, comment_permission
     * - 계층 구조: parent_id, level (답글/댓글 시스템)
     * - 샤딩 지원: uuid, shard_id
     * - 통계 정보: click, like, rank
     *
     * @param string $schema 생성할 테이블명
     * @param array $permissions 권한 설정 (기본값 설정에 사용)
     * @return void
     */
    protected function schemaCreate($schema, $permissions = [])
    {
        if (Schema::hasTable($schema)) {
            return;
        }

        Schema::create($schema, function (Blueprint $table) use ($permissions) {
            $table->id();
            $table->timestamps();

            // 분류코드
            $table->string('code')->nullable();
            $table->string('slug')->nullable();

            // 작성자 정보
            $table->unsignedBigInteger('user_id')->nullable(); // 회원 ID
            $table->string('user_uuid')->nullable(); // 회원 UUID (샤딩 지원)
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable(); // 비회원일 경우 비밀번호 필요

            // 권한 관리 필드 추가
            $table->enum('write_permission', ['admin_only', 'member_only', 'guest_allowed'])
                  ->default($permissions['write_permission'] ?? 'guest_allowed')
                  ->comment('글쓰기 권한: admin_only(관리자만), member_only(회원만), guest_allowed(비회원 허용)');

            $table->enum('read_permission', ['admin_only', 'member_only', 'guest_allowed'])
                  ->default($permissions['read_permission'] ?? 'guest_allowed')
                  ->comment('읽기 권한: admin_only(관리자만), member_only(회원만), guest_allowed(비회원 허용)');

            $table->enum('comment_permission', ['admin_only', 'member_only', 'guest_allowed'])
                  ->default($permissions['comment_permission'] ?? 'guest_allowed')
                  ->comment('댓글 권한: admin_only(관리자만), member_only(회원만), guest_allowed(비회원 허용)');

            // 하위글 구조 (댓글/답글 시스템)
            $table->unsignedBigInteger('parent_id')->nullable(); // 부모 게시글 ID
            $table->string('parent_uuid')->nullable(); // 부모 게시글 UUID (샤딩 지원)
            $table->unsignedTinyInteger('level')->default(0); // 계층 레벨 (0: 원본글, 1: 1단계 답글, ...)

            // post 정보
            $table->string('categories')->nullable();
            $table->string('keyword')->nullable();
            $table->string('tags')->nullable();

            // 제목내용
            $table->string('title')->nullable();
            $table->text('content')->nullable();

            // post 대표 이미지
            $table->string('image')->nullable();

            // 샤딩 지원
            $table->string('uuid')->nullable(); // 게시글 UUID
            $table->unsignedTinyInteger('shard_id')->nullable(); // 샤드 ID

            // 통계
            $table->unsignedBigInteger('click')->default(0); // 조회수
            $table->unsignedBigInteger('like')->default(0); //좋아요
            $table->unsignedBigInteger('rank')->default(0); //랭크

            // 인덱스
            $table->index(['parent_id', 'level', 'created_at']);
            $table->index(['uuid', 'shard_id']);
            $table->index(['user_id']);
            $table->index(['user_uuid', 'shard_id']);
            $table->index(['code', 'created_at']);
            $table->index(['write_permission', 'read_permission']); // 권한 관리용 인덱스
        });
    }

    /**
     * 코멘트 테이블 스키마 생성
     */
    protected function createCommentTable($tableName)
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 게시글 연결
            $table->unsignedBigInteger('post_id'); // 게시글 ID
            $table->string('post_uuid')->nullable(); // 게시글 UUID (샤딩 지원)

            // 작성자 정보
            $table->unsignedBigInteger('user_id')->nullable(); // 회원 ID
            $table->string('user_uuid')->nullable(); // 회원 UUID (샤딩 지원)
            $table->string('name')->nullable(); // 작성자명
            $table->string('email')->nullable(); // 작성자 이메일
            $table->string('password')->nullable(); // 비회원일 경우 비밀번호

            // 코멘트 내용 (짧은 글)
            $table->text('content');

            // 샤딩 지원
            $table->unsignedTinyInteger('shard_id')->nullable();

            // 통계
            $table->unsignedBigInteger('like')->default(0); // 좋아요

            // 인덱스
            $table->index(['post_id', 'created_at']);
            $table->index(['post_uuid', 'shard_id']);
            $table->index(['user_id']);
            $table->index(['user_uuid', 'shard_id']);
        });
    }

    /**
     * 평가(좋아요/별점) 테이블 스키마 생성
     * (MigrateRatingTableController 기능 통합)
     */
    protected function createRatingTable($tableName)
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($tableName) {
            $table->id();
            $table->timestamps();

            // 게시글 연결
            $table->unsignedBigInteger('post_id'); // 게시글 ID
            $table->string('post_uuid')->nullable(); // 게시글 UUID (샤딩 지원)

            // 작성자 정보
            $table->unsignedBigInteger('user_id')->nullable(); // 회원 ID
            $table->string('user_uuid')->nullable(); // 회원 UUID (샤딩 지원)
            $table->string('name')->nullable(); // 작성자명
            $table->string('email')->nullable(); // 작성자 이메일
            $table->ipAddress('ip_address')->nullable(); // IP 주소 (비회원 중복 방지)

            // 평가 유형
            $table->enum('type', ['like', 'rating']); // 좋아요 또는 별점

            // 평가 값
            $table->boolean('is_like')->default(false); // 좋아요 여부 (type='like'일 때)
            $table->unsignedTinyInteger('rating')->nullable(); // 별점 (1-5, type='rating'일 때)

            // 샤딩 지원
            $table->unsignedTinyInteger('shard_id')->nullable();

            // 인덱스
            $table->index(['post_id', 'type', 'created_at']);
            $table->index(['post_uuid', 'shard_id']);
            $table->index(['user_id', 'type']);
            $table->index(['user_uuid', 'shard_id']);
            $table->index(['ip_address', 'type']); // 비회원 중복 체크용

            // 유니크 제약 조건 (사용자당 게시글당 평가 유형당 하나씩만)
            // 테이블별로 고유한 인덱스 이름 생성
            $tableCode = str_replace(['site_board_', '_ratings'], '', $tableName);
            $table->unique(['post_id', 'user_id', 'type'], 'unique_user_post_rating_' . $tableCode);
            $table->unique(['post_id', 'ip_address', 'type'], 'unique_ip_post_rating_' . $tableCode);
        });
    }

    /**
     * 기존 게시판에 평가 테이블 추가 (MigrateRatingTableController 기능 통합)
     */
    protected function migrateRatingTables()
    {
        $boards = DB::table($this->table)->get();
        $createdTables = [];
        $skippedTables = [];

        foreach ($boards as $board) {
            if (isset($board->code) && $board->code) {
                $ratingTableName = "site_board_" . $board->code . "_ratings";

                // 이미 존재하는지 확인
                if (Schema::hasTable($ratingTableName)) {
                    $skippedTables[] = $ratingTableName;
                    continue;
                }

                // 평가 테이블 생성
                $this->createRatingTable($ratingTableName);
                $createdTables[] = $ratingTableName;
            }
        }

        return [
            'created' => $createdTables,
            'skipped' => $skippedTables,
            'created_count' => count($createdTables),
            'skipped_count' => count($skippedTables),
        ];
    }

    /**
     * 게시판 권한 검증
     *
     * 사용자의 게시판 접근 권한을 검증합니다.
     * 권한 유형(읽기/쓰기/댓글)과 사용자 상태(관리자/회원/비회원)를 기반으로 판단합니다.
     *
     * @param string $boardCode 게시판 코드
     * @param string $permissionType 권한 유형 ('write'|'read'|'comment')
     * @param int|null $userId 사용자 ID (null이면 비회원)
     * @param bool $isAdmin 관리자 여부
     * @return bool 권한 허용 여부
     */
    protected function checkBoardPermission($boardCode, $permissionType = 'write', $userId = null, $isAdmin = false)
    {
        $board = DB::table($this->table)->where('code', $boardCode)->first();

        if (!$board) {
            return false;
        }

        $permission = $board->{$permissionType . '_permission'} ?? 'guest_allowed';

        switch ($permission) {
            case 'admin_only':
                return $isAdmin;
            case 'member_only':
                return $userId !== null;
            case 'guest_allowed':
                return true;
            default:
                return false;
        }
    }

    /**
     * 게시판 통계 업데이트
     */
    protected function updateBoardStats($boardId)
    {
        $board = DB::table($this->table)->find($boardId);

        if (!$board || !isset($board->code)) {
            return;
        }

        $tableName = "site_board_" . $board->code;

        if (!Schema::hasTable($tableName)) {
            return;
        }

        // 게시글 수 계산
        $postCount = DB::table($tableName)->count();

        // 총 조회수 계산
        $totalViews = DB::table($tableName)->sum('click') ?? 0;

        // 마지막 게시글 작성일
        $lastPost = DB::table($tableName)->orderBy('created_at', 'desc')->first();
        $lastPostAt = $lastPost ? $lastPost->created_at : null;

        // 게시판 정보 업데이트
        DB::table($this->table)
            ->where('id', $boardId)
            ->update([
                'post' => $postCount,
                'total_views' => $totalViews,
                'last_post_at' => $lastPostAt,
                'updated_at' => now(),
            ]);
    }

    /**
     * 첨부파일 테이블 스키마 생성
     */
    protected function createAttachmentTable($tableName)
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
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

    /**
     * 이미지 테이블 스키마 생성
     */
    protected function createImageTable($tableName)
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
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
}