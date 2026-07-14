<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sla_rule_id');
            $table->enum('event_type', ['first_response', 'resolution', 're_open_response'])->default('resolution');
            $table->enum('status', ['on_track', 'at_risk', 'breached'])->default('on_track');
            $table->timestamp('deadline_at');
            $table->timestamp('breached_at')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('sla_rule_id')->references('id')->on('sla_rules')->onDelete('cascade');

            $table->index('ticket_id');
            $table->index('status');
            $table->index('deadline_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_events');
    }
};
