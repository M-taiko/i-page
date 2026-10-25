<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            // Null = inherit from the parent channel, else the classic channel page (ADR-012 addendum).
            $table->string('design_key', 60)->nullable()->after('brand_id');
            $table->json('design_options')->nullable()->after('design_key');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['design_key', 'design_options']);
        });
    }
};
