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
        Schema::create('board_images', function (Blueprint $table) {
            $table->id();

            // 게시판 정보
            $table->string('board_code')->index(); // 게시판 코드
            $table->unsignedBigInteger('post_id')->index(); // 게시글 ID

            // 이미지 정보
            $table->string('original_name'); // 원본 파일명
            $table->string('stored_name'); // 저장된 파일명
            $table->string('file_path'); // 파일 경로
            $table->string('file_extension', 10); // 파일 확장자
            $table->unsignedInteger('file_size')->default(0); // 파일 크기 (bytes)
            $table->string('mime_type')->nullable(); // MIME 타입

            // 이미지 메타데이터
            $table->unsignedInteger('width')->nullable(); // 이미지 너비
            $table->unsignedInteger('height')->nullable(); // 이미지 높이
            $table->text('alt_text')->nullable(); // 대체 텍스트
            $table->text('caption')->nullable(); // 이미지 설명

            // 정렬 및 상태
            $table->unsignedInteger('sort_order')->default(0); // 정렬 순서
            $table->boolean('is_thumbnail')->default(false); // 썸네일 여부
            $table->boolean('is_featured')->default(false); // 대표 이미지 여부
            $table->enum('status', ['active', 'hidden', 'deleted'])->default('active'); // 상태

            // 업로드 정보
            $table->unsignedBigInteger('uploaded_by')->nullable(); // 업로드한 사용자 ID
            $table->string('upload_ip', 45)->nullable(); // 업로드 IP 주소

            $table->timestamps();

            // 인덱스
            $table->index(['board_code', 'post_id']);
            $table->index(['board_code', 'post_id', 'is_thumbnail']);
            $table->index(['board_code', 'post_id', 'is_featured']);
            $table->index(['uploaded_by']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('board_images');
    }
};