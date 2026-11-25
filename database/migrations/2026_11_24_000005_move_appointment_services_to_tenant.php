<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('appointment_services', function (Blueprint $table) {
            $table->foreignId('channel_id')->nullable()->change();
            $table->decimal('price_from', 12, 2)->nullable()->after('description');
            $table->string('currency', 3)->nullable()->after('price_from');
        });
        Schema::table('organization_memberships', fn (Blueprint $table) => $table->boolean('can_manage_services')->default(false)->after('status'));
    }
    public function down(): void {
        Schema::table('organization_memberships', fn (Blueprint $table) => $table->dropColumn('can_manage_services'));
        Schema::table('appointment_services', function (Blueprint $table) {
            $table->dropColumn(['price_from','currency']);
        });
    }
};
