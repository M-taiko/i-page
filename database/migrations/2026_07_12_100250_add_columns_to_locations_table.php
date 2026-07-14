<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->after('organization_id');
            $table->string('location_type')->nullable()->after('country');
            $table->decimal('latitude', 10, 8)->nullable()->after('location_type');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->date('opening_date')->nullable()->after('longitude');

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['brand_id']);
            $table->dropColumn(['brand_id', 'location_type', 'latitude', 'longitude', 'opening_date']);
        });
    }
};
