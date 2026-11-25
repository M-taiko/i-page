<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('hotel_rooms',function(Blueprint $t){
            $t->foreignId('hotel_unit_id')->nullable()->constrained('hotel_units')->restrictOnDelete();
            $t->index('organization_id');
            $t->dropUnique(['organization_id','number']);
            $t->unique(['hotel_unit_id','number']);
        });
        Schema::table('hotel_bookings',fn(Blueprint $t)=>$t->uuid('submission_key')->nullable()->unique());
    }
    public function down(): void {
        Schema::table('hotel_bookings',fn(Blueprint $t)=>$t->dropColumn('submission_key'));
        Schema::table('hotel_rooms',function(Blueprint $t){$t->dropUnique(['hotel_unit_id','number']);$t->dropConstrainedForeignId('hotel_unit_id');});
    }
};
