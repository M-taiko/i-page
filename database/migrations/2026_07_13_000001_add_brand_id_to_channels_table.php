<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Establishes the Brand → Channels hierarchy.
     * A channel belongs to a brand (nullable so org-level channels remain valid).
     */
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->after('organization_id');

            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->index('brand_id');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropIndex(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
