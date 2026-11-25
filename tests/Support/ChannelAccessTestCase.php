<?php
namespace Tests\Support;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
abstract class ChannelAccessTestCase extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('activity_logs', function (Blueprint $t) { $t->id(); $t->integer('user_id')->nullable(); $t->integer('organization_id')->nullable(); $t->integer('subject_id')->nullable(); foreach (['subject_type','event','description','changes','ip_address','user_agent'] as $key) $t->text($key)->nullable(); $t->timestamps(); });
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('email'); $t->string('profile_level')->nullable(); $t->softDeletes(); $t->timestamps(); });
        Schema::create('roles', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('guard_name'); });
        Schema::create('model_has_roles', function (Blueprint $t) { $t->integer('role_id'); $t->integer('model_id'); $t->string('model_type'); });
        Schema::create('organizations', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('status')->default('active'); $t->softDeletes(); $t->timestamps(); });
        Schema::create('organization_memberships', function (Blueprint $t) { $t->id(); $t->integer('organization_id'); $t->integer('user_id'); $t->string('role'); $t->string('status'); $t->string('job_title')->nullable(); $t->integer('department_id')->nullable(); $t->timestamp('joined_date')->nullable(); $t->integer('invited_by')->nullable(); $t->softDeletes(); $t->timestamps(); });
        Schema::create('channels', function (Blueprint $t) { $t->id(); $t->integer('organization_id'); $t->integer('admin_user_id')->nullable(); $t->string('name'); $t->string('status')->default('active'); $t->softDeletes(); $t->timestamps(); });
        Schema::create('channel_user', function (Blueprint $t) { $t->integer('channel_id'); $t->integer('user_id'); $t->string('role')->default('member'); $t->string('status')->default('approved'); $t->string('source')->nullable(); $t->timestamp('joined_at')->nullable(); $t->timestamp('muted_at')->nullable(); foreach (['post','edit','delete','manage_bookings'] as $key) $t->boolean('can_'.$key)->default(false); $t->primary(['channel_id','user_id']); });
        Schema::create('channel_channel', function (Blueprint $t) { $t->integer('parent_channel_id'); $t->integer('child_channel_id'); });
        Schema::create('qr_codes', function (Blueprint $t) { $t->id(); $t->integer('organization_id'); $t->string('ownable_type'); $t->integer('ownable_id'); $t->string('code')->unique(); $t->string('url'); $t->string('label')->nullable(); $t->boolean('is_active')->default(true); $t->timestamp('expires_at')->nullable(); $t->json('metadata')->nullable(); $t->softDeletes(); $t->timestamps(); });
        DB::table('users')->insert([['id'=>1,'email'=>'manager@example.test'],['id'=>2,'email'=>'recipient@example.test'],['id'=>3,'email'=>'other@example.test']]);
        DB::table('organizations')->insert([['id'=>1,'name'=>'First','status'=>'active'],['id'=>2,'name'=>'Second','status'=>'active']]);
        DB::table('organization_memberships')->insert(['organization_id'=>1,'user_id'=>1,'role'=>'organization_admin','status'=>'active']);
        DB::table('channels')->insert([['id'=>1,'organization_id'=>1,'name'=>'First channel'],['id'=>2,'organization_id'=>1,'name'=>'Second channel'],['id'=>3,'organization_id'=>2,'name'=>'Other tenant']]);
    }

}
