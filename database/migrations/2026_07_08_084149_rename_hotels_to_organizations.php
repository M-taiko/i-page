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
        // Rename hotels table to organizations
        Schema::rename('hotels', 'organizations');

        // Add organization_id to branches (Branch becomes child of Organization)
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove organization_id from branches
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeignIdFor('Organization');
            $table->dropColumn('organization_id');
        });

        // Rename organizations table back to hotels
        Schema::rename('organizations', 'hotels');
    }
};
