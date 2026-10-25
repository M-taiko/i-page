<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('post_format', 20)->default('standard')->index()->after('post_type');
            $table->string('card_style', 20)->default('classic')->after('post_format');
            $table->string('badge_key', 30)->nullable()->after('card_style');
            $table->string('badge_label', 40)->nullable()->after('badge_key');
            $table->json('options')->nullable()->after('badge_label');
        });

        Schema::create('post_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('label', 150);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('post_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('post_poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['option_id', 'user_id']);
            $table->index(['post_id', 'user_id']);
        });

        Schema::create('post_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 12); // going | maybe | not_going
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
        });

        Schema::create('post_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_ratings');
        Schema::dropIfExists('post_rsvps');
        Schema::dropIfExists('post_poll_votes');
        Schema::dropIfExists('post_poll_options');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['post_format']);
            $table->dropColumn(['post_format', 'card_style', 'badge_key', 'badge_label', 'options']);
        });
    }
};
