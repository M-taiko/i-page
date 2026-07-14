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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->enum('audience', ['all', 'in_house', 'team', 'channel'])->default('channel');
            $table->text('body');
            $table->string('image_path', 255)->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('pinned_until')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('channel_id');
            $table->index('author_id');
            $table->index('published_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
