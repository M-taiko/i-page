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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->enum('type', ['public', 'private']);
            $table->enum('audience_profile', ['business', 'public', 'private']);
            $table->unsignedInteger('audience_count')->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->unsignedBigInteger('admin_user_id');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->string('qr_path', 255)->nullable();
            $table->string('share_url', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('admin_user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
