<?php
namespace Tests\Feature;
use App\Http\Controllers\{PostController,TenantChannelPostController};
use App\Models\{Channel,Post,User};
use App\Services\PostNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Http\Request;
class PostComposerTest extends \Tests\Support\ChannelAccessTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('posts',function(Blueprint $t){$t->id();foreach(['channel_id','organization_id','author_id'] as $key)$t->integer($key);foreach(['title','summary','body','audience','status','post_type','language','post_format','card_style','priority','badge_key','badge_label','image_path'] as $key)$t->text($key)->nullable();$t->boolean('requires_acknowledgment')->default(false);$t->boolean('cascade')->default(false);$t->json('options')->nullable();$t->timestamp('published_at')->nullable();$t->timestamp('pinned_until')->nullable();$t->softDeletes();$t->timestamps();});
        Schema::create('post_poll_options',function(Blueprint $t){$t->id();$t->integer('post_id');$t->string('label');$t->integer('sort_order');$t->timestamps();});
        DB::table('organization_memberships')->insert(['organization_id'=>1,'user_id'=>2,'role'=>'member','status'=>'active']);
        Channel::find(1)->users()->attach(2,['role'=>'member','status'=>'approved','can_post'=>true]);
        $this->actingAs(User::find(2));
    }
    public function test_picker_only_returns_authorized_channels(): void
    {
        $view=app(PostController::class)->create();
        $this->assertSame([1],$view->getData()['channels']->modelKeys());
        Channel::find(2)->users()->attach(2,['role'=>'member','status'=>'approved','can_post'=>true]);
        $this->assertSame([1,2],app(PostController::class)->create()->getData()['channels']->modelKeys());
    }
    public function test_forged_channel_is_rejected(): void
    {
        $this->post('/posts',['channel_id'=>2,'body'=>'Not authorized'])->assertForbidden();
        $this->post('/posts',['channel_id'=>3,'body'=>'Other tenant'])->assertForbidden();
        $this->assertDatabaseCount('posts',0);
    }
    public function test_draft_saves_poll_appearance_highlight_without_notification(): void
    {
        $this->mock(PostNotificationService::class)->shouldNotReceive('published');
        $this->post('/posts',['channel_id'=>1,'body'=>'Which activity?','status'=>'draft','post_format'=>'poll','poll_options'=>['Sports','Art'],'card_style'=>'gradient','pinned'=>1,'pin_scope'=>'channel','reactions'=>['love']])->assertSessionHasNoErrors()->assertRedirect();
        $post=Post::firstOrFail();$this->assertSame(1,$post->channel_id);$this->assertSame('draft',$post->status);$this->assertNull($post->published_at);$this->assertSame('gradient',$post->card_style);$this->assertSame('poll',$post->post_format);$this->assertTrue($post->isPinned());$this->assertSame('channel',$post->pinScope());$this->assertSame(['love'],$post->options['reactions']);$this->assertSame(2,$post->pollOptions()->count());
    }
}
