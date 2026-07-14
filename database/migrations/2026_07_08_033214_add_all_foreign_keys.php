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
        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('channel_id')->references('id')->on('channels')->nullableOnDelete();
        });

        Schema::table('reactions', function (Blueprint $table) {
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('group_user', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('channel_user', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('channels')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('channel_channel', function (Blueprint $table) {
            $table->foreign('parent_channel_id')->references('id')->on('channels')->cascadeOnDelete();
            $table->foreign('child_channel_id')->references('id')->on('channels')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channel_channel', function (Blueprint $table) {
            $table->dropForeign(['parent_channel_id', 'child_channel_id']);
        });

        Schema::table('channel_user', function (Blueprint $table) {
            $table->dropForeign(['channel_id', 'user_id']);
        });

        Schema::table('group_user', function (Blueprint $table) {
            $table->dropForeign(['group_id', 'user_id']);
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('reactions', function (Blueprint $table) {
            $table->dropForeign(['post_id', 'user_id']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['author_id', 'channel_id']);
        });
    }
};
