<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the new organization_memberships table
        Schema::create('organization_memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('member'); // organization_manager, member, etc.
            $table->string('employee_id')->nullable(); // Job ID/Employee Number
            $table->string('job_title')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor', 'volunteer'])->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable(); // Direct manager (self-referential)
            $table->unsignedBigInteger('primary_location_id')->nullable(); // Primary work location
            $table->enum('status', ['active', 'inactive', 'suspended', 'invited'])->default('active');
            $table->dateTime('joined_date')->nullable();
            $table->unsignedBigInteger('invited_by')->nullable(); // Who invited this user
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('manager_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('primary_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['organization_id', 'user_id']);
            $table->index('organization_id');
            $table->index('user_id');
            $table->index('department_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_memberships');
    }
};
