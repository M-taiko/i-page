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
        // Update FK constraints on groups table (drop old, recreate new)
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->foreign('hotel_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // Update FK constraints on channels table
        Schema::table('channels', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->foreign('hotel_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // Update FK constraints on posts table
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->foreign('hotel_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // Update FK on organizations table if it has default_channel_id
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'default_channel_id')) {
                $table->dropForeign(['default_channel_id']);
                $table->foreign('default_channel_id')->references('id')->on('channels')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert FK constraints on groups table
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });

        // Revert FK constraints on channels table
        Schema::table('channels', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });

        // Revert FK constraints on posts table
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });

        // Revert FK on organizations table
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'default_channel_id')) {
                $table->dropForeign(['default_channel_id']);
                $table->foreign('default_channel_id')->references('id')->on('channels')->onDelete('set null');
            }
        });
    }
};
