<?php

use App\Models\User;
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
        $tables = ['organizations', 'branches', 'departments', 'groups', 'channels', 'posts'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table_schema) use ($table) {
                if (!Schema::hasColumn($table, 'created_by')) {
                    $table_schema->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn($table, 'updated_by')) {
                    $table_schema->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn($table, 'deleted_by')) {
                    $table_schema->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn($table, 'deleted_at')) {
                    $table_schema->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['organizations', 'branches', 'departments', 'groups', 'channels', 'posts'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table_schema) {
                if (Schema::hasColumn($table, 'created_by')) {
                    $table_schema->dropForeignIdFor(User::class, 'created_by');
                    $table_schema->dropColumn('created_by');
                }
                if (Schema::hasColumn($table, 'updated_by')) {
                    $table_schema->dropForeignIdFor(User::class, 'updated_by');
                    $table_schema->dropColumn('updated_by');
                }
                if (Schema::hasColumn($table, 'deleted_by')) {
                    $table_schema->dropForeignIdFor(User::class, 'deleted_by');
                    $table_schema->dropColumn('deleted_by');
                }
                if (Schema::hasColumn($table, 'deleted_at')) {
                    $table_schema->dropSoftDeletes();
                }
            });
        }
    }
};
