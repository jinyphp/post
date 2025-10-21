<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_forum', function (Blueprint $table) {
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

            // 포럼 정보
            $table->string('categories')->nullable();
            $table->string('keyword')->nullable();
            $table->string('tags')->nullable();

            // 제목내용
            $table->string('title')->nullable();
            $table->text('content')->nullable();

            // 포럼 대표 이미지
            $table->string('image')->nullable();

            // 샤딩 지원
            $table->string('uuid')->nullable(); // 포럼 글 UUID
            $table->unsignedTinyInteger('shard_id')->nullable(); // 샤드 ID

            // 통계
            $table->unsignedBigInteger('click')->default(0); // 조회수
            $table->unsignedBigInteger('like')->default(0); // 좋아요
            $table->unsignedBigInteger('rank')->default(0); // 랭크

            // 인덱스
            $table->index(['categories', 'created_at']);
            $table->index(['uuid', 'shard_id']);
            $table->index(['user_id']);
            $table->index(['user_uuid', 'shard_id']);
            $table->index(['code', 'created_at']);
            $table->index(['click', 'created_at']); // 인기글 정렬용
            $table->index(['like', 'created_at']); // 좋아요 정렬용
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_forum');
    }
};