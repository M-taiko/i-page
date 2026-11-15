<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Brand managers (ADR-015).
        Schema::create('brand_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['admin', 'moderator'])->default('admin');
            $table->timestamps();
            $table->unique(['brand_id', 'user_id']);
        });

        // Brand posts: brand_id set, channel_id NULL, audience all|public|private.
        Schema::table('posts', function (Blueprint $table) {
            $table->string('brand_audience', 10)->nullable()->after('cascade');
            $table->index(['brand_id', 'brand_audience']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['brand_id', 'brand_audience']);
            $table->dropColumn('brand_audience');
        });
        Schema::dropIfExists('brand_user');
    }
};
