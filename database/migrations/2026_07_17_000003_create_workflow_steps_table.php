<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('role_required', 60);
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 20)->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
