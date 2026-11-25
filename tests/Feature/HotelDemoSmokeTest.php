<?php
namespace Tests\Feature;

use App\Models\{Channel, Organization, User};
use App\Services\ActionCenterService;
use Illuminate\Support\Facades\DB;

/** Read-only smoke checks against an explicitly selected, freshly seeded local MySQL demo. */
class HotelDemoSmokeTest extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('HOTEL_DEMO_SMOKE_DATABASE');
        if (!$database) $this->markTestSkipped('Set HOTEL_DEMO_SMOKE_DATABASE to a local hotel demo database.');
        $this->assertContains($database, ['ipage_hotel_seed_check_20261124', 'ipage_db']);
        config(['database.default'=>'mysql','database.connections.mysql.database'=>$database,'database.connections.mysql.host'=>'127.0.0.1','database.connections.mysql.username'=>'root','database.connections.mysql.password'=>'']);
        DB::purge('mysql');
    }
    private function login(string $email): User {
        $user = User::where('email',$email)->sole();
        $this->actingAs($user);
        return $user;
    }
    public function test_booking_pages_and_guest_receipt_render(): void {
        $bufferLevel=ob_get_level();
        $admin=$this->login('admin@hilton.test');
        $channel=Channel::where('slug','cairo-guest-lounge')->sole();
        $dates=['check_in'=>today()->addDays(40)->toDateString(),'check_out'=>today()->addDays(42)->toDateString(),'guests'=>1];
        $this->get('/browse/org/'.$channel->organization_id)->assertOk()->assertSee('Book your stay');
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/browse/org/.$channel->organization_id)->assertOk()->assertSee(Book your stay);');
        $this->get('/tenant/channels/'.$channel->id.'/reservations')->assertOk();
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/tenant/channels/.$channel->id./reservations)->assertOk();');
        $this->get('/tenant/channels/'.$channel->id.'/reservations/rooms')->assertOk();
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/tenant/channels/.$channel->id./reservations/rooms)->assertOk();');
        $this->get('/tenant/channels/'.$channel->id.'/reservations/create?'.http_build_query($dates))->assertOk()->assertSee('Classic Double');
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/tenant/channels/.$channel->id./reservations/create?.http_build_query($dates))->assertOk()->assertSee(Classic Double);');
        $this->get('/book/channels/'.$channel->id.'?'.http_build_query($dates))->assertOk()->assertSee('Classic Double');
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/book/channels/.$channel->id.?.http_build_query($dates))->assertOk()->assertSee(Classic Double);');
        DB::beginTransaction();
        try {
            $room=\App\Models\HotelRoom::where('hotel_unit_id',app(\App\Services\HotelPropertyService::class)->require($channel)->id)->firstOrFail();
            $booking=app(\App\Services\HotelBookingService::class)->create($channel,$admin,$dates+['room_id'=>$room->id,'guest_name'=>'Temporary test','submission_key'=>(string)\Illuminate\Support\Str::uuid()]);
            $this->get('/tenant/channels/'.$channel->id.'/reservations/'.$booking->id)->assertOk();
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/tenant/channels/.$channel->id./reservations/.$booking->id)->assertOk();');
            $this->get('/book/channels/'.$channel->id.'/'.$booking->id)->assertOk();
        $this->assertSame($bufferLevel,ob_get_level(),'Output buffer leaked after: $this->get(/book/channels/.$channel->id./.$booking->id)->assertOk();');
        } finally { DB::rollBack(); }
    }
    public function test_group_pages_render_with_seeded_data(): void {
        $this->login('admin@hilton.test');
        $org = Organization::where('slug','hilton-demo')->sole();
        $hotelChannel = Channel::where('name','Hilton Cairo')->sole();
        foreach (['/tenant/hotel-group','/tenant/channels','/tenant/channels/create','/tenant/channels/'.$hotelChannel->id.'/edit','/posts/create','/tenant/actions',"/organization/{$org->id}/dashboard/users", "/organization/{$org->id}/dashboard/users/create"] as $path) {
            $this->get($path)->assertOk();
        }
        $counts = app(ActionCenterService::class)->data(auth()->user(),$org)['counts'];
        $this->assertSame(['posts'=>1,'organization'=>1,'channels'=>1],$counts);
    }
    public function test_cycles_and_unauthorized_root_creation_are_rejected(): void {
        $this->login('admin@hilton.test');
        $hotel = Channel::where('name','Hilton Cairo')->sole();
        $child = Channel::where('slug','cairo-front-desk')->sole();
        $this->putJson('/tenant/channels/'.$hotel->id,['name'=>$hotel->name,'type'=>'private','parent_channel_id'=>$child->id])->assertUnprocessable()->assertJsonValidationErrors('parent_channel_id');
        $this->login('publisher@hilton.test');
        $this->postJson('/tenant/channels',['name'=>'Unauthorized root','type'=>'private'])->assertForbidden();
    }
    public function test_country_manager_cannot_open_other_country_hotel(): void {
        $this->login('eg.admin@hilton.test');
        $cairo = Channel::where('name','Hilton Cairo')->sole();
        $dubai = Channel::where('name','Hilton Dubai')->sole();
        $this->get('/tenant/channels/'.$cairo->id)->assertOk();
        $this->get('/tenant/channels/'.$dubai->id)->assertForbidden();
        $this->get('/tenant/hotel-group')->assertOk()->assertSee('Hilton Cairo')->assertDontSee('Hilton Dubai');
    }
    public function test_granular_accounts_have_exact_capabilities(): void {
        $guest = Channel::where('slug','cairo-guest-lounge')->sole();
        $offers = Channel::where('slug','cairo-offers-experiences')->sole();
        $publisher = $this->login('publisher@hilton.test');
        $this->assertTrue($guest->userCan($publisher,'post'));
        $this->assertFalse($guest->userCan($publisher,'edit'));
        $this->assertFalse($offers->userCan($publisher,'post'));
        $this->get('/posts/create')->assertOk();
        $editor = $this->login('editor@hilton.test');
        $this->assertTrue($offers->userCan($editor,'edit'));
        $this->assertFalse($offers->userCan($editor,'post'));
        $this->assertFalse($offers->userCan($editor,'delete'));
        $viewer = $this->login('viewer@hilton.test');
        $this->assertFalse(Channel::where('slug','cairo-front-desk')->sole()->userCan($viewer,'post'));
        $this->get('/posts/create')->assertForbidden();
        $multi = $this->login('multi@hilton.test');
        $this->assertTrue($guest->userCan($multi,'post'));
        $this->assertTrue($offers->userCan($multi,'post'));
        $this->assertFalse($guest->userCan($multi,'edit'));
        $this->assertTrue($offers->userCan($multi,'edit'));
    }
}
