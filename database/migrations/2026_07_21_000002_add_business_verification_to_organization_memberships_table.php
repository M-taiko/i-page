<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_memberships', function (Blueprint $table) {
            $table->timestamp('business_verified_at')->nullable()->after('job_title');
            $table->foreignId('business_verified_by')->nullable()->after('business_verified_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organization_memberships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_verified_by');
            $table->dropColumn('business_verified_at');
        });
    }
};
