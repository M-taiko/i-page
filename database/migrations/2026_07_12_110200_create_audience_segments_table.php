<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audience_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rules'); // Validated rules: {scope: 'brand'|'location'|'department'|'role'|'language', value: string|string[]}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['organization_id', 'name']);
        });

        Schema::create('post_audiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('segment_id')->nullable(); // NULL if using inline rules
            $table->json('inline_rules')->nullable(); // Inline rules if not using segment
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('segment_id')->references('id')->on('audience_segments')->onDelete('set null');
            $table->unique('post_id'); // One audience target per post
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_audiences');
        Schema::dropIfExists('audience_segments');
    }
};
