<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $t) {
            $t->id(); $t->foreignId('organization_id')->constrained()->restrictOnDelete();
            $t->string('number', 40); $t->string('name', 120); $t->unsignedSmallInteger('capacity')->default(2);
            $t->unsignedInteger('base_rate_minor'); $t->char('currency', 3); $t->boolean('is_active')->default(true); $t->text('description')->nullable(); $t->timestamps();
            $t->unique(['organization_id', 'number']);
        });
        Schema::create('hotel_room_rates', function (Blueprint $t) {
            $t->id(); $t->foreignId('room_id')->constrained('hotel_rooms')->cascadeOnDelete();
            $t->date('date'); $t->unsignedInteger('rate_minor'); $t->timestamps(); $t->unique(['room_id', 'date']);
        });
        Schema::create('hotel_bookings', function (Blueprint $t) {
            $t->id(); $t->uuid('reference')->unique();
            $t->foreignId('organization_id')->constrained()->restrictOnDelete();
            $t->foreignId('channel_id')->constrained()->restrictOnDelete();
            $t->foreignId('room_id')->constrained('hotel_rooms')->restrictOnDelete();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->string('guest_name', 160); $t->string('guest_email')->nullable(); $t->string('guest_phone', 40)->nullable();
            $t->date('check_in'); $t->date('check_out'); $t->unsignedSmallInteger('guests');
            $t->string('status', 24)->default('confirmed');
            $t->unsignedInteger('total_minor'); $t->unsignedInteger('paid_minor')->default(0); $t->char('currency', 3);
            $t->json('nightly_rates'); $t->text('notes')->nullable();
            $t->boolean('marketing_consent')->default(false); $t->timestamp('consent_recorded_at')->nullable(); $t->string('consent_source', 255)->nullable();
            $t->timestamps(); $t->index(['organization_id', 'channel_id', 'check_in']);
        });
        Schema::create('hotel_room_nights', function (Blueprint $t) {
            $t->id(); $t->foreignId('room_id')->constrained('hotel_rooms')->restrictOnDelete();
            $t->foreignId('booking_id')->nullable()->constrained('hotel_bookings')->restrictOnDelete();
            $t->date('date'); $t->string('reason', 255)->nullable(); $t->timestamps();
            // A physical room can only be occupied once per night, across all channels.
            $t->unique(['room_id', 'date']);
        });
        Schema::create('hotel_payments', function (Blueprint $t) {
            $t->id(); $t->foreignId('booking_id')->constrained('hotel_bookings')->restrictOnDelete();
            $t->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $t->uuid('submission_key')->unique(); $t->string('type', 16); $t->unsignedInteger('amount_minor');
            $t->string('method', 24); $t->string('reference', 120)->nullable(); $t->text('notes')->nullable(); $t->timestamp('recorded_at'); $t->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['hotel_payments','hotel_room_nights','hotel_bookings','hotel_room_rates','hotel_rooms'] as $table) { Schema::dropIfExists($table); }
    }
};
