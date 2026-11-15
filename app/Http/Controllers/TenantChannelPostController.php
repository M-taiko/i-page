<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use App\Services\PostFormatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Channel posts: create, edit, delete and pin. Every action is authorized through
 * Channel::userCan() so granular member permissions apply (ADR-014).
 */
class TenantChannelPostController extends Controller
{
    public function __construct(private PostFormatService $formats)
    {
    }

    public function create(Channel $channel)
    {
        abort_unless($channel->userCan(auth()->user(), 'post'), 403);

        return view('tenant.channels.posts.create', ['channel' => $channel, 'organization' => $channel->organization]);
    }

    public function store(Request $request, Channel $channel)
    {
        abort_unless($channel->userCan($request->user(), 'post'), 403);

        $format = $this->formats->formatOf($request);

        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:500',
            // A poll question or event description can be short; a standard post needs real content.
            'body' => ['required', 'string', $format === 'standard' ? 'min:10' : 'min:3', 'max:5000'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ] + $this->publishRules() + $this->formats->rules($request));

        if ($format === 'poll' && ! $this->formats->pollOptionsAreEnough($validated)) {
            throw ValidationException::withMessages(['poll_options' => __('Add at least :n options.', ['n' => config('posts.limits.poll_options_min')])]);
        }

        $post = new Post();
        $post->channel_id = $channel->id;
        $post->author_id = auth()->id();
        $post->organization_id = $channel->organization_id;
        $post->title = $validated['title'] ?? null;
        $post->summary = $validated['summary'] ?? null;
        $post->body = $validated['body'];
        $post->audience = 'channel';
        $post->status = 'published';
        $post->published_at = now();

        $afterSave = $this->formats->fill($post, $validated, $request);
        $this->applyPublishOptions($post, $request);

        if ($request->hasFile('image') && $format !== 'gallery') {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();
        $afterSave($post);
        app(\App\Services\PostNotificationService::class)->published($post);

        return redirect()->to($this->channelUrl($channel))->with('success', __('Post created successfully!'));
    }

    public function edit(Channel $channel, Post $post)
    {
        $this->authorizePost($channel, $post, 'edit');

        return view('tenant.channels.posts.edit', ['channel' => $channel, 'post' => $post, 'organization' => $channel->organization]);
    }

    public function update(Request $request, Channel $channel, Post $post)
    {
        $this->authorizePost($channel, $post, 'edit');

        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:500',
            'body' => 'required|string|min:3|max:5000',
            'card_style' => ['nullable', Rule::in(array_keys(config('posts.card_styles')))],
            'priority' => ['nullable', Rule::in(array_keys(config('posts.priorities')))],
            'badge_key' => ['nullable', Rule::in(array_keys(config('posts.badges')))],
            'badge_label' => 'nullable|string|max:40',
        ] + $this->publishRules());

        $post->fill([
            'title' => $validated['title'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'card_style' => $validated['card_style'] ?? $post->card_style,
            'priority' => $validated['priority'] ?? $post->priority,
            'badge_key' => $validated['badge_key'] ?? null,
            'badge_label' => ($validated['badge_key'] ?? null) ? ($validated['badge_label'] ?? null) : null,
        ]);
        $this->applyPublishOptions($post, $request);
        $post->save();

        return redirect()->route('guest.post-detail', $post->id)->with('success', __('Post updated.'));
    }

    public function destroy(Channel $channel, Post $post)
    {
        $this->authorizePost($channel, $post, 'delete');

        if ($thumb = $post->option('pin_thumb')) {
            Storage::disk('public')->delete($thumb);
        }
        $post->delete();

        return redirect()->to($this->channelUrl($channel))->with('success', __('Post deleted.'));
    }

    /**
     * Quick pin from the post menu. With `scope` (channel|all) it pins (or changes the scope);
     * without it, it unpins.
     */
    public function pin(Request $request, Channel $channel, Post $post)
    {
        $this->authorizePost($channel, $post, 'edit');
        $scope = $request->validate(['scope' => 'nullable|in:channel,all'])['scope'] ?? null;

        if ($scope) {
            if (! $post->isPinned()) {
                $post->pinned_until = now()->addYears(10);
            }
            $post->options = array_merge($post->options ?? [], ['pin_scope' => $scope]);
            $message = $scope === 'all' ? __('Pinned in this channel and all its sub-channels.') : __('Pinned in this channel only.');
        } else {
            $post->pinned_until = null;
            $message = __('Post unpinned.');
        }
        $post->save();

        return back()->with('success', $message);
    }

    private function publishRules(): array
    {
        return [
            'cascade' => 'nullable|boolean',
            'pinned' => 'nullable|boolean',
            'pinned_until' => 'nullable|date|after:now',
            'pin_scope' => 'nullable|in:channel,all',
            'pin_thumb' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_pin_thumb' => 'nullable|boolean',
        ];
    }

    /** Cascade flag, pin state and highlight thumbnail from the publish-options form section. */
    private function applyPublishOptions(Post $post, Request $request): void
    {
        $post->cascade = $request->boolean('cascade');
        if (! $request->boolean('pinned')) {
            $post->pinned_until = null;
        } elseif ($request->filled('pinned_until')) {
            $post->pinned_until = \Illuminate\Support\Carbon::parse($request->input('pinned_until'));
        } elseif (! $post->isPinned()) {
            $post->pinned_until = now()->addYears(10);   // no end date
        }

        $options = $post->options ?? [];
        if ($request->boolean('pinned')) {
            $options['pin_scope'] = $request->input('pin_scope') === 'channel' ? 'channel' : 'all';
        }
        if ($request->hasFile('pin_thumb')) {
            if (! empty($options['pin_thumb'])) {
                Storage::disk('public')->delete($options['pin_thumb']);
            }
            $options['pin_thumb'] = $request->file('pin_thumb')->store('posts/highlights', 'public');
        } elseif ($request->boolean('remove_pin_thumb') && ! empty($options['pin_thumb'])) {
            Storage::disk('public')->delete($options['pin_thumb']);
            unset($options['pin_thumb']);
        }
        $post->options = $options;
    }

    private function authorizePost(Channel $channel, Post $post, string $ability): void
    {
        abort_unless($post->channel_id === $channel->id, 404);
        abort_unless($channel->userCan(auth()->user(), $ability), 403);
    }

    /** Managers return to the dashboard channel page; invited members to the public one. */
    private function channelUrl(Channel $channel): string
    {
        $user = auth()->user();
        $inOrgDashboard = $user->currentOrganization && $user->currentOrganization->id === $channel->organization_id;

        return $inOrgDashboard
            ? route('tenant.channels.show', $channel->id)
            : route('guest.channel-detail', [$channel->organization_id, $channel->slug]);
    }
}
