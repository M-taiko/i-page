<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a channel membership was created (docs/27 "Channel Bell & Organization Follow Subscriptions"):
 * "self" when the visitor joined the channel, "follow" when it came from following the organization.
 * Follow memberships are muted and are removed again on unfollow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_user', function (Blueprint $table) {
            $table->string('source', 10)->default('self')->after('status');
            $table->index(['user_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::table('channel_user', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'source']);
            $table->dropColumn('source');
        });
    }
};
