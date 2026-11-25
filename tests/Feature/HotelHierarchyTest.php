<?php
namespace Tests\Feature;
use App\Models\{Organization, User};
use App\Services\HotelHierarchyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
class HotelHierarchyTest extends \Tests\Support\ChannelAccessTestCase
{
    protected function setUp(): void {
        parent::setUp();
        Schema::create('organization_templates', function (Blueprint $t) { $t->id(); $t->string('key'); });
        Schema::table('organizations', function (Blueprint $t) { $t->integer('organization_template_id')->nullable(); });
        Schema::table('channels', function (Blueprint $t) { $t->string('slug')->nullable(); $t->string('type')->nullable(); $t->string('audience_profile')->default('public'); });
        DB::table('organization_templates')->insert(['id'=>1,'key'=>'hotel']);
        DB::table('organizations')->update(['organization_template_id'=>1]);
        (require database_path('migrations/2026_11_24_000001_create_hotel_units_table.php'))->up();
    }
    private function createUnit(array $data = [], int $actor = 1, int $org = 1) {
        return app(HotelHierarchyService::class)->create(Organization::findOrFail($org), User::findOrFail($actor), array_replace(['kind'=>'country','name'=>'Egypt','country'=>'Egypt','timezone'=>'Africa/Cairo','currency'=>'EGP'], $data));
    }
    public function test_country_admin_inherits_hotel_management(): void {
        $country = $this->createUnit();
        DB::table('channel_user')->insert(['channel_id'=>$country->channel_id,'user_id'=>2,'role'=>'admin','status'=>'approved']);
        $hotel = $this->createUnit(['kind'=>'hotel','name'=>'Cairo','parent_id'=>$country->id,'city'=>'Cairo'],2);
        $this->assertSame('Egypt',$hotel->country);
        $this->assertTrue($hotel->channel->canAdminister(User::find(2)));
        $this->assertFalse($hotel->channel->canAdminister(User::find(3)));
        $this->assertSame('private',$hotel->channel->type);
        $this->assertSame('business',$hotel->channel->audience_profile);
        $this->assertDatabaseHas('channel_channel',['parent_channel_id'=>$country->channel_id,'child_channel_id'=>$hotel->channel_id]);
    }
    public function test_member_cannot_create_hotel(): void {
        $country = $this->createUnit();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->createUnit(['kind'=>'hotel','parent_id'=>$country->id,'city'=>'Cairo'],2);
    }
    public function test_hotel_can_belong_directly_to_organization(): void {
        $hotel = $this->createUnit(['kind'=>'hotel','city'=>'Cairo']);
        $this->assertNull($hotel->parent_id);
        $this->assertSame(0,$hotel->channel->parentChannels()->count());
    }
    public function test_cannot_attach_to_another_organization(): void {
        $country = $this->createUnit();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->createUnit(['kind'=>'hotel','parent_id'=>$country->id],1,2);
    }
    public function test_non_hotel_template_is_rejected(): void {
        DB::table('organization_templates')->update(['key'=>'school']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->createUnit();
    }
}
