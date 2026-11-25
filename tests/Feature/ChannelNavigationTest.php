<?php
namespace Tests\Feature;
use App\Models\{Organization, User};
use App\Support\ChannelNavigation;
use Illuminate\Support\Facades\DB;
class ChannelNavigationTest extends \Tests\Support\ChannelAccessTestCase
{
    public function test_switcher_shows_only_authorized_nested_channels(): void {
        DB::table('organization_memberships')->insert(['organization_id'=>1,'user_id'=>2,'role'=>'staff','status'=>'active']);
        DB::table('channels')->insert([['id'=>4,'organization_id'=>1,'name'=>'Staff'],['id'=>5,'organization_id'=>1,'name'=>'Housekeeping']]);
        DB::table('channel_channel')->insert([['parent_channel_id'=>1,'child_channel_id'=>2],['parent_channel_id'=>2,'child_channel_id'=>4],['parent_channel_id'=>4,'child_channel_id'=>5]]);
        DB::table('channel_user')->insert(['channel_id'=>2,'user_id'=>2,'role'=>'admin','status'=>'approved']);
        $options = collect(ChannelNavigation::options(User::find(2),Organization::find(1)));
        $this->assertEqualsCanonicalizing([2,4,5],$options->pluck('id')->all());
        $this->assertSame('First / Second channel / Staff / Housekeeping',$options->firstWhere('id',5)['label']);
        $this->assertSame([],ChannelNavigation::options(User::find(3),Organization::find(1)));
    }
}
