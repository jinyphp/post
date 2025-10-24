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
        Schema::table('site_board', function (Blueprint $table) {
            // 파일 업로드 설정
            if (!Schema::hasColumn('site_board', 'allow_file_upload')) {
                $table->boolean('allow_file_upload')->default(true)->comment('첨부파일 업로드 허용 여부');
            }

            if (!Schema::hasColumn('site_board', 'allow_image_upload')) {
                $table->boolean('allow_image_upload')->default(true)->comment('다중 이미지 업로드 허용 여부');
            }

            if (!Schema::hasColumn('site_board', 'max_file_count')) {
                $table->unsignedInteger('max_file_count')->default(5)->comment('최대 파일 업로드 개수');
            }

            if (!Schema::hasColumn('site_board', 'blocked_extensions')) {
                $table->text('blocked_extensions')->nullable()->comment('금지된 파일 확장자 (쉼표로 구분)');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_board', function (Blueprint $table) {
            $table->dropColumn([
                'allow_file_upload',
                'allow_image_upload',
                'max_file_count',
                'blocked_extensions'
            ]);
        });
    }
};