<?php

namespace Tests\Feature;

use App\Models\{Channel, Organization, QrCode, User};
use App\Services\ChannelAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/** Focused SQLite fixture: unrelated legacy MySQL-only migrations are not required. */
class ChannelAccessTest extends \Tests\Support\ChannelAccessTestCase
{
    private function invite(array $overrides = []): QrCode
    {
        return QrCode::create(array_replace(['organization_id'=>1,'ownable_type'=>Organization::class,'ownable_id'=>1,'code'=>str_repeat('a',48),'url'=>'https://example.test/invitation','is_active'=>true,'expires_at'=>now()->addDay(),'metadata'=>['kind'=>'channel_access','created_by'=>1,'email'=>'recipient@example.test','grants'=>[1=>['role'=>'member','can_post'=>true,'can_edit'=>false,'can_delete'=>false],2=>['role'=>'member','can_post'=>false,'can_edit'=>true,'can_delete'=>false]]]], $overrides));
    }

    public function test_guest_registration_handoff_resumes_permissions_after_authentication(): void
    {
        $invite = $this->invite();
        $this->post('/channel-invitations/'.$invite->code, ['action'=>'register'])->assertRedirect(route('register'))->assertSessionHas('pending_channel_invitation', $invite->code);
        $this->assertDatabaseCount('channel_user',0);
        $request = \Illuminate\Http\Request::create('/register','POST');
        $request->setLaravelSession(session()->driver());
        $request->setUserResolver(fn () => User::find(2));
        $response = app(\App\Http\Controllers\ChannelAccessController::class)->resume($request);
        $this->assertSame(route('tenant.channels.index'),$response->getTargetUrl());
        $this->assertSame(1,session('current_organization_id'));
        $this->assertFalse(session()->has('pending_channel_invitation'));
        $this->assertTrue(Channel::find(1)->userCan(User::find(2),'post'));
    }

    public function test_qr_is_generated_as_downloadable_svg(): void
    {
        $image = app(\App\Services\QrCodeService::class)->generateImage($this->invite(),320);
        $this->assertStringContainsString('<svg',base64_decode($image));
    }

    public function test_manager_can_create_invitation_with_selected_permissions(): void
    {
        $this->actingAs(User::find(1))->post('/organization/1/dashboard/channel-invitations', [
            'label'=>'Editor invitation','days'=>7,'grants'=>[1=>['selected'=>1,'edit'=>1]],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $invite=QrCode::firstOrFail();
        $this->assertSame('channel_access',$invite->metadata['kind']);
        $this->assertTrue($invite->metadata['grants'][1]['can_edit']);
        $this->assertFalse($invite->metadata['grants'][1]['can_post']);
        $this->assertTrue($invite->canBeScanned());
        $this->get('/channel-invitations/'.$invite->code)->assertOk()->assertSee('First channel')->assertSee('Accept invitation');
    }

    public function test_acceptance_applies_distinct_channel_grants_and_consumes_invite(): void
    {
        $invite=$this->invite(); $service=app(ChannelAccessService::class); $user=User::findOrFail(2);
        $service->redeem($invite->code,$user);
        $this->assertDatabaseHas('organization_memberships',['organization_id'=>1,'user_id'=>2,'role'=>'member','status'=>'active']);
        $this->assertTrue(Channel::find(1)->userCan($user,'post'));
        $this->assertFalse(Channel::find(1)->userCan($user,'edit'));
        $this->assertTrue(Channel::find(2)->userCan($user,'edit'));
        $this->assertFalse(Channel::find(3)->userCan($user,'post'));
        $this->assertFalse($invite->fresh()->is_active);
        $service->redeem($invite->code,$user);
        $this->assertSame(2,DB::table('channel_user')->count());
        try { $service->redeem($invite->code,User::find(3)); $this->fail('Invite was reused'); } catch(HttpException $e) { $this->assertSame(410,$e->getStatusCode()); }
    }

    public function test_wrong_email_does_not_consume_invite_or_grant_access(): void
    {
        $invite=$this->invite();
        try { app(ChannelAccessService::class)->redeem($invite->code,User::find(3)); $this->fail('Wrong recipient accepted'); } catch(ValidationException $e) { $this->assertArrayHasKey('invitation',$e->errors()); }
        $this->assertTrue($invite->fresh()->is_active);
        $this->assertDatabaseCount('channel_user',0);
    }

    public function test_expired_and_revoked_invitations_are_rejected(): void
    {
        $invite=$this->invite(['expires_at'=>now()->subMinute()]);
        foreach ([['is_active'=>true,'expires_at'=>now()->subMinute()],['is_active'=>false,'expires_at'=>now()->addDay()]] as $state) {
            $invite->update($state);
            try { app(ChannelAccessService::class)->redeem($invite->code,User::find(2)); $this->fail('Inactive invite accepted'); } catch(HttpException $e) { $this->assertSame(410,$e->getStatusCode()); }
        }
        $this->assertDatabaseCount('channel_user',0);
    }

    public function test_cross_tenant_grants_are_rejected_and_actor_must_be_manager(): void
    {
        $service=app(ChannelAccessService::class); $org=Organization::find(1);
        try { $service->normalize($org,[3=>['selected'=>1,'full'=>1]]); $this->fail('Cross tenant grant allowed'); } catch(ValidationException $e) { $this->assertArrayHasKey('grants',$e->errors()); }
        try { $service->authorizeManager(User::find(2),$org); $this->fail('Non-manager allowed'); } catch(HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
    }

    public function test_replace_updates_and_revokes_direct_permissions(): void
    {
        $service=app(ChannelAccessService::class);$org=Organization::find(1);$user=User::find(2);
        $service->replace($org,$user,$service->normalize($org,[1=>['selected'=>1,'post'=>1],2=>['selected'=>1,'full'=>1]]));
        $this->assertTrue(Channel::find(2)->canAdminister($user));
        $service->replace($org,$user,$service->normalize($org,[1=>['selected'=>1,'edit'=>1]]));
        $this->assertFalse(Channel::find(1)->userCan($user,'post'));
        $this->assertTrue(Channel::find(1)->userCan($user,'edit'));
        $this->assertFalse(Channel::find(2)->canAdminister($user));
    }

    public function test_creator_revocation_prevents_later_acceptance(): void
    {
        $invite=$this->invite();DB::table('organization_memberships')->where('user_id',1)->update(['status'=>'suspended']);
        try {app(ChannelAccessService::class)->redeem($invite->code,User::find(2));$this->fail('Revoked creator still grants access');} catch(HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
        $this->assertDatabaseCount('channel_user',0);
    }

    public function test_sidebar_access_is_scoped_to_current_organization_and_channel_grants(): void
    {
        $user = User::find(2);
        DB::table('organization_memberships')->insert(['organization_id'=>1,'user_id'=>2,'role'=>'member','status'=>'active']);
        Channel::find(1)->users()->attach($user->id,['role'=>'member','status'=>'approved','can_post'=>true]);
        $access = \App\Support\SidebarAccess::for($user, Organization::find(1));

        $this->assertTrue($access->activeMember);
        $this->assertFalse($access->organizationManager);
        $this->assertSame([1], $access->channels->modelKeys());
        $this->assertSame([1], $access->publishingChannels->modelKeys());
        $this->assertTrue($access->canPublish());
        $this->assertFalse($access->canManageOrganization());
        $this->assertNotContains(3, $access->channels->modelKeys());
    }

    public function test_sidebar_manager_sees_all_current_organization_channels_only(): void
    {
        $access = \App\Support\SidebarAccess::for(User::find(1), Organization::find(1));
        $this->assertTrue($access->organizationManager);
        $this->assertSame([1,2], $access->channels->modelKeys());
        $this->assertSame([1,2], $access->managedChannels->modelKeys());
        $this->assertNotContains(3, $access->channels->modelKeys());
    }
}
