<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
return new class extends Migration {
    public function up(): void {
        Schema::table('organization_memberships', function(Blueprint $table){
            $table->boolean('can_add_services')->default(false)->after('can_manage_services');
            $table->boolean('can_edit_services')->default(false)->after('can_add_services');
            $table->boolean('can_delete_services')->default(false)->after('can_edit_services');
        });
        DB::table('organization_memberships')->where('can_manage_services',true)->update(['can_add_services'=>true,'can_edit_services'=>true,'can_delete_services'=>true]);
    }
    public function down(): void {Schema::table('organization_memberships',fn(Blueprint $table)=>$table->dropColumn(['can_add_services','can_edit_services','can_delete_services']));}
};
