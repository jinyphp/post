<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_comments', function (Blueprint $table) {
            $table->id();

            // 게시판 정보
            $table->string('board_code')->index(); // 게시판 코드
            $table->unsignedBigInteger('post_id')->index(); // 게시글 ID

            // 댓글 계층 구조
            $table->unsignedBigInteger('parent_id')->nullable()->index(); // 부모 댓글 ID (대댓글용)
            $table->unsignedInteger('depth')->default(0); // 댓글 깊이 (0: 원댓글, 1: 대댓글, ...)
            $table->string('thread_id', 100)->nullable(); // 댓글 스레드 ID (계층 구조 관리용)

            // 작성자 정보
            $table->unsignedBigInteger('user_id')->nullable(); // 로그인 사용자 ID
            $table->string('author_name', 100)->nullable(); // 작성자명 (비회원 댓글용)
            $table->string('author_email', 255)->nullable(); // 작성자 이메일 (비회원용)
            $table->string('author_password')->nullable(); // 비회원 댓글 비밀번호 (해시)
            $table->string('author_ip', 45)->nullable(); // 작성자 IP

            // 댓글 내용
            $table->text('content'); // 댓글 내용
            $table->text('content_html')->nullable(); // HTML 변환된 내용
            $table->boolean('is_html')->default(false); // HTML 사용 여부

            // 상태 및 관리
            $table->enum('status', ['published', 'pending', 'spam', 'deleted'])->default('published'); // 댓글 상태
            $table->boolean('is_secret')->default(false); // 비밀댓글 여부
            $table->boolean('is_pinned')->default(false); // 고정댓글 여부
            $table->boolean('is_reported')->default(false); // 신고된 댓글 여부

            // 좋아요/싫어요
            $table->unsignedInteger('like_count')->default(0); // 좋아요 수
            $table->unsignedInteger('dislike_count')->default(0); // 싫어요 수

            // 관리자 정보
            $table->unsignedBigInteger('approved_by')->nullable(); // 승인한 관리자 ID
            $table->timestamp('approved_at')->nullable(); // 승인 시간
            $table->unsignedBigInteger('deleted_by')->nullable(); // 삭제한 관리자 ID
            $table->timestamp('deleted_at')->nullable(); // 삭제 시간
            $table->text('delete_reason')->nullable(); // 삭제 사유

            // 알림 설정
            $table->boolean('notify_replies')->default(false); // 답글 알림 여부
            $table->string('notify_email')->nullable(); // 알림받을 이메일

            // 메타데이터
            $table->string('user_agent')->nullable(); // 사용자 에이전트
            $table->json('metadata')->nullable(); // 추가 메타데이터

            $table->timestamps();

            // 인덱스
            $table->index(['board_code', 'post_id']);
            $table->index(['board_code', 'post_id', 'status']);
            $table->index(['board_code', 'post_id', 'parent_id']);
            $table->index(['user_id']);
            $table->index(['parent_id']);
            $table->index(['status']);
            $table->index(['is_pinned']);
            $table->index(['is_reported']);
            $table->index(['created_at']);

            // 외래 키 제약조건
            $table->foreign('parent_id')->references('id')->on('board_comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('board_comments');
    }
};