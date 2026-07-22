<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('name');
            $table->string('industry_key', 60);
            $table->json('default_departments')->nullable();
            $table->json('default_channels')->nullable();
            $table->json('default_roles')->nullable();
            $table->json('default_workflows')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('organization_template_id')->nullable()->after('id')
                ->constrained('organization_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_template_id');
        });

        Schema::dropIfExists('organization_templates');
    }
};
