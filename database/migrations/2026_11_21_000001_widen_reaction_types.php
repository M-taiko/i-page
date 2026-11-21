<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * reactions.type was an enum of like/love only, but posts offer every reaction in
 * config('posts.reactions') (haha, wow, sad, celebrate…), so saving those failed.
 * A short string lets the configuration decide which reactions exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reactions MODIFY type VARCHAR(20) NOT NULL");
    }

    public function down(): void
    {
        DB::table('reactions')->whereNotIn('type', ['like', 'love'])->update(['type' => 'like']);
        DB::statement("ALTER TABLE reactions MODIFY type ENUM('like','love') NOT NULL");
    }
};
