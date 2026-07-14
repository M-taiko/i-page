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
            $table->dropForeign('posts_hotel_id_foreign');
            $table->renameColumn('hotel_id', 'organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign('posts_organization_id_foreign');
            $table->renameColumn('organization_id', 'hotel_id');
            $table->foreign('hotel_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }
};
