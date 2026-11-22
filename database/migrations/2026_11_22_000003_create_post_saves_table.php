<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Saved posts (the bookmark on every post card) and the new default post template "compact"
 * (text beside a thumbnail) — docs/27 "Compact Post Card & Saved Posts".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
        });

        // "Compact" is the default template now; plain "classic" posts take it.
        DB::statement("ALTER TABLE posts MODIFY card_style VARCHAR(20) NOT NULL DEFAULT 'compact'");
        DB::table('posts')->where('card_style', 'classic')->update(['card_style' => 'compact']);
    }

    public function down(): void
    {
        Schema::dropIfExists('post_saves');
        DB::table('posts')->where('card_style', 'compact')->update(['card_style' => 'classic']);
        DB::statement("ALTER TABLE posts MODIFY card_style VARCHAR(20) NOT NULL DEFAULT 'classic'");
    }
};
