<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_collection_channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id');
            $table->unsignedBigInteger('channel_id');
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('user_collections')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            $table->unique(['collection_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_collection_channels');
    }
};
