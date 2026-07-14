<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Layer 3: personal folders a user creates to organize their own
     * subscribed channels. Never shared, never affect permissions.
     */
    public function up(): void
    {
        Schema::create('user_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('icon')->default('📁');
            $table->string('color')->default('#4557f5');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_collections');
    }
};
