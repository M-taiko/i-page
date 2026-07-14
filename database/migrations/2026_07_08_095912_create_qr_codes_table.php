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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('ownable_type')->comment('Organization, Branch, Department, or Channel');
            $table->unsignedBigInteger('ownable_id');
            $table->string('code')->unique()->comment('QR code unique identifier');
            $table->string('label')->nullable()->comment('Human-readable label');
            $table->string('url')->nullable()->comment('Target URL when scanned');
            $table->integer('scan_count')->default(0);
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_branded')->default(false)->comment('Custom logo/colors');
            $table->json('metadata')->nullable()->comment('Custom data: color, logo_path, etc');
            $table->timestamps();
            $table->index(['organization_id', 'is_active']);
            $table->index(['ownable_type', 'ownable_id']);
            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
