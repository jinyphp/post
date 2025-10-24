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
        Schema::create('board_attachments', function (Blueprint $table) {
            $table->id();

            // 게시판 정보
            $table->string('board_code')->index(); // 게시판 코드
            $table->unsignedBigInteger('post_id')->index(); // 게시글 ID

            // 파일 정보
            $table->string('original_name'); // 원본 파일명
            $table->string('stored_name'); // 저장된 파일명
            $table->string('file_path'); // 파일 경로
            $table->string('file_extension', 10); // 파일 확장자
            $table->unsignedBigInteger('file_size')->default(0); // 파일 크기 (bytes)
            $table->string('mime_type')->nullable(); // MIME 타입

            // 파일 메타데이터
            $table->text('description')->nullable(); // 파일 설명
            $table->string('file_category', 50)->nullable(); // 파일 카테고리 (document, image, video, etc.)
            $table->json('metadata')->nullable(); // 추가 메타데이터 (JSON)

            // 다운로드 정보
            $table->unsignedInteger('download_count')->default(0); // 다운로드 횟수
            $table->timestamp('last_downloaded_at')->nullable(); // 마지막 다운로드 시간

            // 보안 및 권한
            $table->boolean('is_public')->default(true); // 공개 여부
            $table->string('access_permission', 50)->default('public'); // 접근 권한 (public, member, admin)
            $table->string('download_permission', 50)->default('public'); // 다운로드 권한
            $table->boolean('require_login')->default(false); // 로그인 필수 여부

            // 바이러스 스캔 및 보안
            $table->enum('scan_status', ['pending', 'clean', 'infected', 'error'])->default('pending'); // 스캔 상태
            $table->timestamp('scanned_at')->nullable(); // 스캔 시간
            $table->text('scan_result')->nullable(); // 스캔 결과

            // 정렬 및 상태
            $table->unsignedInteger('sort_order')->default(0); // 정렬 순서
            $table->enum('status', ['active', 'hidden', 'deleted', 'quarantined'])->default('active'); // 상태

            // 업로드 정보
            $table->unsignedBigInteger('uploaded_by')->nullable(); // 업로드한 사용자 ID
            $table->string('upload_ip', 45)->nullable(); // 업로드 IP 주소
            $table->text('upload_user_agent')->nullable(); // 업로드 사용자 에이전트

            // 자동 삭제 설정
            $table->timestamp('expires_at')->nullable(); // 만료 시간
            $table->boolean('auto_delete')->default(false); // 자동 삭제 여부

            $table->timestamps();

            // 인덱스
            $table->index(['board_code', 'post_id']);
            $table->index(['board_code', 'post_id', 'status']);
            $table->index(['uploaded_by']);
            $table->index(['status']);
            $table->index(['scan_status']);
            $table->index(['expires_at']);
            $table->index(['file_category']);
            $table->index(['download_count']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('board_attachments');
    }
};