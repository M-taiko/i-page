<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('branches', 'locations');

        // Rename branch_id to location_id in foreign key tables
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('branch_id', 'location_id');
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'branch_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->renameColumn('branch_id', 'location_id');
            });
        }

        if (Schema::hasTable('channels') && Schema::hasColumn('channels', 'branch_id')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->renameColumn('branch_id', 'location_id');
            });
        }

        if (Schema::hasTable('groups') && Schema::hasColumn('groups', 'branch_id')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->renameColumn('branch_id', 'location_id');
            });
        }

        if (Schema::hasTable('qr_codes') && Schema::hasColumn('qr_codes', 'branch_id')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->renameColumn('branch_id', 'location_id');
            });
        }
    }

    public function down(): void
    {
        // Rename location_id back to branch_id in all tables
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'location_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('location_id', 'branch_id');
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'location_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->renameColumn('location_id', 'branch_id');
            });
        }

        if (Schema::hasTable('channels') && Schema::hasColumn('channels', 'location_id')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->renameColumn('location_id', 'branch_id');
            });
        }

        if (Schema::hasTable('groups') && Schema::hasColumn('groups', 'location_id')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->renameColumn('location_id', 'branch_id');
            });
        }

        if (Schema::hasTable('qr_codes') && Schema::hasColumn('qr_codes', 'location_id')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->renameColumn('location_id', 'branch_id');
            });
        }

        Schema::rename('locations', 'branches');
    }
};
