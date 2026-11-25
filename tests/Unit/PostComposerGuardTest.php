<?php

namespace Tests\Unit;

use App\Http\Controllers\TenantChannelPostController;
use App\Models\{Channel, Post, User};
use App\Services\PostFormatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PostComposerGuardTest extends TestCase
{
    public function test_unauthorized_channel_is_rejected_before_content_is_saved(): void
    {
        $user = new User();
        $request = Request::create('/posts', 'POST', ['body' => 'Test content']);
        $request->setUserResolver(fn () => $user);
        $channel = Mockery::mock(Channel::class)->makePartial();
        $channel->shouldReceive('userCan')->once()->with($user, 'post')->andReturn(false);
        try {
            app(TenantChannelPostController::class)->store($request, $channel);
            $this->fail('Expected channel permission denial.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
    }

    public function test_formats_validate_event_dates_and_keep_appearance_options(): void
    {
        $formats = app(PostFormatService::class);
        $request = Request::create('/', 'POST', ['post_format' => 'event']);
        $this->assertTrue(Validator::make($request->all(), $formats->rules($request))->fails());
        $data = ['post_format' => 'event', 'event_starts_at' => '2027-01-02 10:00', 'card_style' => 'gradient', 'priority' => 'high', 'badge_key' => 'featured', 'badge_label' => 'Open day', 'reactions' => ['love']];
        $request = Request::create('/', 'POST', $data);
        $this->assertFalse(Validator::make($data, $formats->rules($request))->fails());
        $post = new Post();
        $formats->fill($post, $data, $request);
        $this->assertSame('event', $post->post_format);
        $this->assertSame('gradient', $post->card_style);
        $this->assertSame('Open day', $post->badge_label);
        $this->assertSame(['love'], $post->options['reactions']);
        $this->assertNotEmpty($post->options['event']['starts_at']);
    }
}
