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
        Schema::create('site_forum_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forum_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('url');
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('forum_id')->references('id')->on('site_forum')->onDelete('cascade');
            $table->index(['forum_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_forum_images');
    }
};