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
        Schema::create('site_blog', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 분류코드
            $table->string('code')->nullable();
            $table->string('slug')->unique(); // SEO를 위한 고유 슬러그

            // 작성자 정보
            $table->unsignedBigInteger('user_id')->nullable(); // 회원 ID
            $table->string('user_uuid')->nullable(); // 회원 UUID (샤딩 지원)
            $table->string('author_name')->nullable(); // 작성자명
            $table->string('author_email')->nullable(); // 작성자 이메일
            $table->string('author_bio')->nullable(); // 작성자 소개

            // 블로그 정보
            $table->string('category_slug')->nullable(); // 카테고리 슬러그
            $table->string('tags')->nullable(); // 태그 (쉼표로 구분)
            $table->string('keywords')->nullable(); // SEO 키워드

            // 제목과 내용
            $table->string('title'); // 제목 (필수)
            $table->text('excerpt')->nullable(); // 요약/발췌
            $table->longText('content')->nullable(); // 본문
            $table->longText('content_html')->nullable(); // HTML 변환된 본문

            // 블로그 이미지
            $table->string('featured_image')->nullable(); // 대표 이미지
            $table->string('featured_image_alt')->nullable(); // 이미지 alt 텍스트

            // 발행 상태
            $table->enum('status', ['draft', 'published', 'private', 'scheduled'])->default('draft');
            $table->timestamp('published_at')->nullable(); // 발행 시간
            $table->timestamp('scheduled_at')->nullable(); // 예약 발행 시간

            // SEO 설정
            $table->string('meta_title')->nullable(); // SEO 제목
            $table->text('meta_description')->nullable(); // SEO 설명
            $table->string('meta_keywords')->nullable(); // SEO 키워드

            // 샤딩 지원
            $table->string('uuid')->nullable(); // 블로그 글 UUID
            $table->unsignedTinyInteger('shard_id')->nullable(); // 샤드 ID

            // 통계
            $table->unsignedBigInteger('views')->default(0); // 조회수
            $table->unsignedBigInteger('likes')->default(0); // 좋아요
            $table->unsignedBigInteger('shares')->default(0); // 공유수
            $table->unsignedBigInteger('comments_count')->default(0); // 댓글수

            // 블로그 설정
            $table->boolean('allow_comments')->default(true); // 댓글 허용
            $table->boolean('is_featured')->default(false); // 추천 글
            $table->boolean('is_sticky')->default(false); // 상단 고정

            // 인덱스
            $table->index(['status', 'published_at']);
            $table->index(['category_slug', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['slug']);
            $table->index(['uuid', 'shard_id']);
            $table->index(['views', 'published_at']); // 인기글 정렬용
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_blog');
    }
};