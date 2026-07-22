<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('module', 60);
            $table->string('name');
            $table->json('steps');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_definitions');
    }
};
