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
        Schema::create('channel_channel', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_channel_id');
            $table->unsignedBigInteger('child_channel_id');

            $table->primary(['parent_channel_id', 'child_channel_id']);
            $table->unique(['parent_channel_id', 'child_channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_channel');
    }
};
