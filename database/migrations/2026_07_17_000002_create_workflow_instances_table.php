<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained()->cascadeOnDelete();
            $table->morphs('workflowable');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('current_step')->default(1);
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};
