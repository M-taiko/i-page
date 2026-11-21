<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-016: brands are removed; the tenant (organization) is the command center.
 * Tenant posts (channel_id NULL) choose their placement: the tenant page, every channel,
 * or selected channels (post_channel pivot, optionally cascading to sub-channels).
 */
return new class extends Migration
{
    private const BRAND_COLUMNS = ['channels', 'posts', 'locations', 'departments', 'tickets', 'sla_rules'];

    public function up(): void
    {
        Schema::dropIfExists('brand_user');
        Schema::dropIfExists('brand_followers');

        if (Schema::hasColumn('posts', 'brand_audience')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex(['brand_id', 'brand_audience']);
                $table->dropColumn('brand_audience');
            });
        }

        foreach (self::BRAND_COLUMNS as $tableName) {
            if (! Schema::hasColumn($tableName, 'brand_id')) {
                continue;
            }
            $this->dropForeignKeysOn($tableName, 'brand_id');
            $this->dropIndexesOn($tableName, 'brand_id');
            Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('brand_id'));
        }

        Schema::dropIfExists('brands');

        Schema::table('posts', function (Blueprint $table) {
            $table->string('placement', 10)->nullable()->after('cascade');   // page | all | selected
            $table->boolean('show_on_page')->default(false)->after('placement');
            $table->index(['organization_id', 'placement']);
        });

        Schema::create('post_channel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->unique(['post_id', 'channel_id']);
            $table->index('channel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_channel');
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'placement']);
            $table->dropColumn(['placement', 'show_on_page']);
        });
        // Brands are not recreated: ADR-016 removed the concept for good.
    }

    private function dropForeignKeysOn(string $table, string $column): void
    {
        $keys = DB::select(
            'SELECT CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );
        foreach ($keys as $key) {
            DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `{$key->name}`");
        }
    }

    private function dropIndexesOn(string $table, string $column): void
    {
        $indexes = DB::select(
            'SELECT DISTINCT INDEX_NAME AS name FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND INDEX_NAME <> "PRIMARY"',
            [$table, $column]
        );
        foreach ($indexes as $index) {
            DB::statement("ALTER TABLE `$table` DROP INDEX `{$index->name}`");
        }
    }
};
