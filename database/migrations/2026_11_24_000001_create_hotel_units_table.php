<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('hotel_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('hotel_units')->restrictOnDelete();
            $table->foreignId('channel_id')->unique()->constrained()->restrictOnDelete();
            $table->string('kind', 16);
            $table->string('name', 120);
            $table->string('country', 80);
            $table->string('city', 120)->nullable();
            $table->string('timezone', 80)->default('UTC');
            $table->char('currency', 3)->default('USD');
            $table->timestamps();
            $table->index(['organization_id','parent_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('hotel_units'); }
};
