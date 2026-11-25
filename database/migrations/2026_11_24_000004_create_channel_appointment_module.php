<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('channel_user',fn(Blueprint $t)=>$t->boolean('can_manage_bookings')->default(false)->after('can_delete'));
        Schema::create('appointment_services',function(Blueprint $t){
            $t->id();$t->foreignId('organization_id')->constrained()->restrictOnDelete();$t->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $t->string('name',120);$t->string('kind',30);$t->text('description')->nullable();$t->unsignedSmallInteger('duration_minutes')->default(60);$t->unsignedSmallInteger('slot_capacity')->default(1);$t->boolean('is_active')->default(true);$t->timestamps();
            $t->index(['channel_id','is_active']);
        });
        Schema::create('appointment_bookings',function(Blueprint $t){
            $t->id();$t->uuid('reference')->unique();$t->uuid('submission_key')->unique();$t->foreignId('organization_id')->constrained()->restrictOnDelete();$t->foreignId('channel_id')->constrained()->restrictOnDelete();$t->foreignId('service_id')->constrained('appointment_services')->restrictOnDelete();$t->foreignId('customer_id')->constrained('users')->restrictOnDelete();$t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->string('customer_name',160);$t->string('customer_email',180);$t->string('customer_phone',40)->nullable();$t->dateTime('starts_at');$t->dateTime('ends_at');$t->unsignedSmallInteger('guests')->default(1);$t->string('status',30)->default('requested');$t->text('customer_notes')->nullable();$t->boolean('marketing_consent')->default(false);$t->timestamp('consent_recorded_at')->nullable();$t->timestamps();
            $t->index(['channel_id','starts_at','status']);$t->index(['customer_id','created_at']);
        });
        Schema::create('appointment_events',function(Blueprint $t){
            $t->id();$t->foreignId('booking_id')->constrained('appointment_bookings')->cascadeOnDelete();$t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();$t->string('event',40);$t->string('from_status',30)->nullable();$t->string('to_status',30)->nullable();$t->text('message')->nullable();$t->boolean('customer_visible')->default(true);$t->timestamps();
            $t->index(['booking_id','created_at']);
        });
    }
    public function down(): void {Schema::dropIfExists('appointment_events');Schema::dropIfExists('appointment_bookings');Schema::dropIfExists('appointment_services');Schema::table('channel_user',fn(Blueprint $t)=>$t->dropColumn('can_manage_bookings'));}
};
