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
        Schema::create('site_blog_cate', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 기본 정보
            $table->string('name')->unique(); // 카테고리명
            $table->string('slug')->unique(); // URL 슬러그
            $table->string('color')->default('#6c757d'); // 카테고리 색상
            $table->string('icon')->nullable(); // 아이콘 클래스
            $table->text('description')->nullable(); // 카테고리 설명

            // 블로그 카테고리 특화 필드
            $table->string('featured_image')->nullable(); // 카테고리 대표 이미지
            $table->boolean('show_in_menu')->default(true); // 메뉴 표시 여부
            $table->boolean('is_private')->default(false); // 비공개 카테고리

            // SEO 설정
            $table->string('meta_title')->nullable(); // SEO 제목
            $table->text('meta_description')->nullable(); // SEO 설명
            $table->string('meta_keywords')->nullable(); // SEO 키워드

            // 정렬 및 상태
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->boolean('is_active')->default(true); // 활성화 상태

            // 통계
            $table->integer('post_count')->default(0); // 게시글 수
            $table->integer('published_count')->default(0); // 발행된 글 수
            $table->timestamp('last_post_at')->nullable(); // 마지막 게시글 시간

            // 관리자 정보
            $table->string('created_by')->nullable(); // 생성자
            $table->string('updated_by')->nullable(); // 수정자

            // 인덱스
            $table->index(['is_active', 'sort_order']);
            $table->index(['show_in_menu', 'sort_order']);
            $table->index(['slug']);
            $table->index(['post_count']);
            $table->index(['is_private', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_blog_cate');
    }
};