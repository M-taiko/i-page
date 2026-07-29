<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE organization_memberships MODIFY status ENUM('active', 'inactive', 'suspended', 'invited', 'pending', 'rejected') DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE organization_memberships MODIFY status ENUM('active', 'inactive', 'suspended', 'invited') DEFAULT 'active'");
    }
};
