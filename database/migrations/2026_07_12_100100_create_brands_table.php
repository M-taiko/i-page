<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('colors')->nullable(); // Primary, secondary, accent colors
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['organization_id', 'slug']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
