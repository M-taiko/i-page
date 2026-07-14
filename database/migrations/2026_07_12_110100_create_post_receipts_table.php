<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('delivered_at')->nullable(); // When post appeared in user's feed
            $table->timestamp('first_viewed_at')->nullable(); // When user first opened/viewed post
            $table->timestamp('read_at')->nullable(); // When user marked as read
            $table->timestamp('acknowledged_at')->nullable(); // When user acknowledged (if requires_acknowledgment=true)
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['post_id', 'user_id']);
            $table->index('user_id');
            $table->index('delivered_at');
            $table->index('first_viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_receipts');
    }
};
