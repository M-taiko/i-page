<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Scope posts to specific brand/location
            $table->unsignedBigInteger('brand_id')->nullable()->after('organization_id');
            $table->unsignedBigInteger('location_id')->nullable()->after('brand_id');

            // Content metadata
            $table->string('title')->nullable()->after('location_id');
            $table->text('summary')->nullable()->after('title');
            $table->enum('post_type', ['announcement', 'news', 'offer', 'emergency', 'feedback_request', 'survey'])->default('announcement')->after('summary');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->after('post_type');
            $table->string('language', 10)->default('en')->after('priority');

            // Engagement requirements
            $table->boolean('requires_acknowledgment')->default(false)->after('language');
            $table->boolean('is_emergency')->default(false)->after('requires_acknowledgment');

            // Status workflow (expanded from current simple enum)
            $table->dropColumn('status');
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'scheduled', 'published', 'expired', 'archived', 'rejected', 'cancelled'])->default('draft')->after('is_emergency');

            // Approval tracking
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // Scheduling
            $table->timestamp('scheduled_for')->nullable()->after('approved_at');

            // Foreign keys
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->index('status');
            $table->index('brand_id');
            $table->index('location_id');
            $table->index('scheduled_for');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeignKey(['brand_id']);
            $table->dropForeignKey(['location_id']);
            $table->dropForeignKey(['approved_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['brand_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['scheduled_for']);
            $table->dropColumn([
                'brand_id', 'location_id', 'title', 'summary', 'post_type',
                'priority', 'language', 'requires_acknowledgment', 'is_emergency',
                'approved_by', 'approved_at', 'scheduled_for'
            ]);
            $table->enum('status', ['published', 'archived'])->default('published');
        });
    }
};
