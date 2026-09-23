<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * departments.slug was globally unique, but department names (and thus
     * slugs) like "Front Desk" are expected to repeat across organizations —
     * every org created from the same template gets the same default
     * department names. Scope uniqueness to the organization instead,
     * matching brands/channels.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_slug_unique');
            $table->unique(['organization_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'slug']);
            $table->unique('slug');
        });
    }
};
