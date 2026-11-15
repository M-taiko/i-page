<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Read-time cascade to sub-channels (ADR-014).
            $table->boolean('cascade')->default(true)->after('channel_id');
            $table->index(['channel_id', 'status', 'published_at']);
        });

        Schema::table('channel_user', function (Blueprint $table) {
            $table->boolean('can_post')->default(false)->after('role');
            $table->boolean('can_edit')->default(false)->after('can_post');
            $table->boolean('can_delete')->default(false)->after('can_edit');
        });

        // Existing moderators/admins keep what their role implied.
        DB::table('channel_user')->where('role', 'admin')->update(['can_post' => true, 'can_edit' => true, 'can_delete' => true]);
        DB::table('channel_user')->where('role', 'moderator')->update(['can_edit' => true, 'can_delete' => true]);
    }

    public function down(): void
    {
        Schema::table('channel_user', fn (Blueprint $table) => $table->dropColumn(['can_post', 'can_edit', 'can_delete']));
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['channel_id', 'status', 'published_at']);
            $table->dropColumn('cascade');
        });
    }
};
