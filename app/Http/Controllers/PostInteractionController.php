<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostInteractionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

/** Poll votes, event RSVPs, ratings and reactions (ADR-013). All require sign-in. */
class PostInteractionController extends Controller
{
    public function __construct(private PostInteractionService $interactions)
    {
    }

    public function vote(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['options' => 'required|array|min:1', 'options.*' => 'integer']);

        return $this->run($post, fn () => $this->interactions->vote($post, $request->user(), $data['options']), __('Thanks for voting!'));
    }

    public function rsvp(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|string']);

        return $this->run($post, fn () => $this->interactions->rsvp($post, $request->user(), $data['status']), __('Your answer was saved.'));
    }

    public function rate(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['rating' => 'required|integer|min:1|max:5']);

        return $this->run($post, fn () => $this->interactions->rate($post, $request->user(), $data['rating']), __('Thanks for your rating!'));
    }

    public function react(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['type' => 'required|string']);

        return $this->run($post, fn () => $this->interactions->react($post, $request->user(), $data['type']), null);
    }

    private function run(Post $post, \Closure $action, ?string $success): RedirectResponse
    {
        abort_unless($post->status === 'published', 404);
        abort_if($post->channel && ! $post->channel->canBeAccessedBy(auth()->user()), 403);

        try {
            $action();
        } catch (\DomainException $e) {
            return back()->with(['error' => $e->getMessage(), 'flash_post' => $post->id]);
        }

        return $success ? back()->with(['success' => $success, 'flash_post' => $post->id]) : back();
    }
}
