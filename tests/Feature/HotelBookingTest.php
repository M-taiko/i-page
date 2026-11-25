<?php
namespace Tests\Feature;
use App\Models\{Channel,HotelBooking,HotelRoom,User};
use App\Services\HotelBookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Support\Str;
class HotelBookingTest extends \Tests\Support\ChannelAccessTestCase
{
    protected function setUp(): void {
        parent::setUp();
        Schema::create('organization_templates',function(Blueprint $t){$t->id();$t->string('key');});
        Schema::table('organizations',function(Blueprint $t){$t->integer('organization_template_id')->nullable();$t->boolean('is_active')->default(true);});
        Schema::table('channels',function(Blueprint $t){$t->json('design_options')->nullable();$t->string('type')->default('public');});
        DB::table('organization_templates')->insert(['id'=>1,'key'=>'hotel']);
        DB::table('organizations')->update(['organization_template_id'=>1]);
        (require database_path('migrations/2026_11_24_000001_create_hotel_units_table.php'))->up();
        (require database_path('migrations/2026_11_23_000001_create_hotel_reservation_tables.php'))->up();
        (require database_path('migrations/2026_11_24_000003_scope_rooms_to_hotel_properties.php'))->up();
        DB::table('hotel_units')->insert([['id'=>1,'organization_id'=>1,'channel_id'=>1,'kind'=>'hotel','name'=>'Cairo','country'=>'Egypt'],['id'=>2,'organization_id'=>1,'channel_id'=>2,'kind'=>'hotel','name'=>'Hurghada','country'=>'Egypt']]);
        DB::table('channels')->where('id',1)->update(['design_options'=>json_encode(['modules'=>['bookings'],'booking_property_id'=>1])]);
        HotelRoom::create(['id'=>1,'hotel_unit_id'=>1,'organization_id'=>1,'number'=>'101','name'=>'Double','capacity'=>2,'base_rate_minor'=>10000,'currency'=>'EGP','is_active'=>true]);
        HotelRoom::create(['hotel_unit_id'=>2,'organization_id'=>1,'number'=>'101','name'=>'Other hotel','capacity'=>2,'base_rate_minor'=>10000,'currency'=>'EGP','is_active'=>true]);
    }
    private function data(array $extra=[]): array {return array_replace(['submission_key'=>(string)Str::uuid(),'room_id'=>1,'check_in'=>today()->addDays(2)->toDateString(),'check_out'=>today()->addDays(4)->toDateString(),'guests'=>2,'guest_name'=>'Test Guest','guest_phone'=>'01000000000'],$extra);}
    private function reserve(array $data): HotelBooking {return app(HotelBookingService::class)->create(Channel::find(1),User::find(2),$data);}
    public function test_price_snapshot_and_retries_do_not_duplicate_booking(): void {
        HotelRoom::find(1)->rates()->create(['date'=>today()->addDays(2)->toDateString(),'rate_minor'=>15000]);
        $data=$this->data();$booking=$this->reserve($data);
        $this->assertSame(25000,$booking->total_minor);
        $this->assertSame($booking->id,$this->reserve($data)->id);
        $this->assertDatabaseCount('hotel_bookings',1);$this->assertDatabaseCount('hotel_room_nights',2);
    }
    public function test_overlap_is_rejected(): void {
        $this->reserve($this->data());
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->reserve($this->data());
    }
    public function test_other_property_room_is_rejected(): void {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->reserve($this->data(['room_id'=>2]));
    }
    public function test_blocked_dates_are_unavailable(): void {
        HotelRoom::find(1)->nights()->create(['date'=>today()->addDays(2)->toDateString(),'reason'=>'Maintenance']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->reserve($this->data());
    }
    public function test_cancellation_releases_room_and_payment_records_are_idempotent(): void {
        $booking=$this->reserve($this->data());$service=app(HotelBookingService::class);
        $payment=['submission_key'=>(string)Str::uuid(),'type'=>'payment','amount'=>'50','method'=>'cash'];
        $service->payment($booking,User::find(1),$payment);$service->payment($booking,User::find(1),$payment);
        $this->assertSame(5000,$booking->fresh()->paid_minor);$this->assertDatabaseCount('hotel_payments',1);
        $service->transition($booking,'cancelled');$this->assertDatabaseCount('hotel_room_nights',0);
        $this->reserve($this->data());$this->assertDatabaseCount('hotel_bookings',2);
    }
    public function test_disabled_module_blocks_public_submission(): void {
        DB::table('channels')->where('id',1)->update(['design_options'=>null]);
        $this->actingAs(User::find(2))->postJson('/book/channels/1',$this->data())->assertNotFound();
        $this->assertDatabaseCount('hotel_bookings',0);
    }
    public function test_public_submission_uses_authenticated_email_and_receipt_is_private(): void {
        $this->actingAs(User::find(2))->post('/book/channels/1',$this->data(['guest_email'=>'forged@example.test']))->assertRedirect();
        $booking=HotelBooking::sole();$this->assertSame('recipient@example.test',$booking->guest_email);
        $this->actingAs(User::find(3))->get('/book/channels/1/'.$booking->id)->assertNotFound();
    }
}
