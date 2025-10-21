<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 계시판 목록
     */
    public function up(): void
    {
        Schema::create('site_board', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            ## 활성화
            $table->string('enable')->nullable();

            ## 분류코드
            $table->string('code')->nullable();
            $table->string('slug')->nullable(); // url 임시코드

            //
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();

            // board 대표 이미지
            $table->string('image')->nullable();

            $table->text('header')->nullable();
            $table->text('footer')->nullable();

            // Blade
            //$table->string('blade')->nullable();
            $table->string('view_layout')->nullable();
            $table->string('view_table')->nullable();
            $table->string('view_list')->nullable();
            $table->string('view_filter')->nullable();
            $table->string('view_create')->nullable();
            $table->string('view_detail')->nullable();
            $table->string('view_edit')->nullable();
            $table->string('view_form')->nullable();

            ## 권환
            $table->string('permit_read')->nullable();
            $table->string('permit_create')->nullable();
            $table->string('permit_edit')->nullable();
            $table->string('permit_delete')->nullable();

            ## 설명
            $table->text('description')->nullable();

            ## 관리자
            $table->string('manager')->nullable();

            ## 글갯수
            $table->unsignedBigInteger('post')->default(0);

            ## 페이지당 게시물 수
            $table->integer('per_page')->default(10);

            ## 추가 설정
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->string('category')->nullable(); // 카테고리
            $table->boolean('use_comment')->default(true); // 댓글 사용
            $table->boolean('use_rating')->default(false); // 평가 사용
            $table->boolean('use_like')->default(true); // 좋아요 사용
            $table->integer('max_file_size')->default(5120); // 최대 파일 크기(KB)
            $table->string('allowed_extensions')->default('jpg,jpeg,png,gif,pdf,doc,docx'); // 허용 확장자
            $table->json('extra_fields')->nullable(); // 추가 필드 설정

            ## 통계 정보
            $table->integer('total_views')->default(0); // 총 조회수
            $table->timestamp('last_post_at')->nullable(); // 마지막 게시글 작성 시간

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_board');
    }
};
