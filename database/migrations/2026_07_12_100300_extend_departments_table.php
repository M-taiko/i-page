<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Add brand_id and parent_department_id if they don't exist
            if (!Schema::hasColumn('departments', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('organization_id');
                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            }

            if (!Schema::hasColumn('departments', 'parent_department_id')) {
                $table->unsignedBigInteger('parent_department_id')->nullable()->after('location_id');
                $table->foreign('parent_department_id')->references('id')->on('departments')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'parent_department_id')) {
                $table->dropForeignKey(['parent_department_id']);
                $table->dropColumn('parent_department_id');
            }

            if (Schema::hasColumn('departments', 'brand_id')) {
                $table->dropForeignKey(['brand_id']);
                $table->dropColumn('brand_id');
            }
        });
    }
};
