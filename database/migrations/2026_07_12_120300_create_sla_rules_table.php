<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->text('description')->nullable();

            // Scope
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('priority')->nullable(); // Specific priority or null for all

            // SLA times (in minutes)
            $table->integer('first_response_time')->nullable(); // Time for first response
            $table->integer('resolution_time')->nullable(); // Time to resolve
            $table->integer('re_open_response_time')->nullable(); // Time if ticket reopened

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('ticket_categories')->onDelete('set null');

            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_rules');
    }
};
