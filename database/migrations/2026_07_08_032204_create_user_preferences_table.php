<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->enum('color_scheme', ['navy', 'dark', 'light'])->default('navy');
            $table->enum('font_size', ['small', 'medium', 'large'])->default('medium');
            $table->enum('language', ['en', 'ar', 'fr'])->default('en');
            $table->boolean('compact_mode')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('notify_new_guest')->default(true);
            $table->boolean('notify_channel_updates')->default(true);
            $table->boolean('notify_system_alerts')->default(false);
            $table->boolean('notify_weekly_report')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
