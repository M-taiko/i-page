<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Null design_key = classic public page (see ADR-012).
            $table->string('design_key', 60)->nullable()->index()->after('organization_template_id');
            $table->json('design_options')->nullable()->after('design_key');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['design_key']);
            $table->dropColumn(['design_key', 'design_options']);
        });
    }
};
