<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use App\Services\PostFormatService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TenantChannelPostController extends Controller
{
    public function __construct(private PostFormatService $formats)
    {
    }

    public function create(Channel $channel)
    {
        $organization = auth()->user()->organizations()->first();
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        return view('tenant.channels.posts.create', compact('channel', 'organization'));
    }

    public function store(Request $request, Channel $channel)
    {
        $organization = auth()->user()->organizations()->first();
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        $format = $this->formats->formatOf($request);

        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            // A poll question or event description can be short; a standard post needs real content.
            'body' => ['required', 'string', $format === 'standard' ? 'min:10' : 'min:3', 'max:5000'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ] + $this->formats->rules($request));

        if ($format === 'poll' && ! $this->formats->pollOptionsAreEnough($validated)) {
            throw ValidationException::withMessages(['poll_options' => __('Add at least :n options.', ['n' => config('posts.limits.poll_options_min')])]);
        }

        $post = new Post();
        $post->channel_id = $channel->id;
        $post->author_id = auth()->id();
        $post->organization_id = $organization->id;
        $post->title = $validated['title'] ?? null;
        $post->body = $validated['body'];
        $post->audience = 'channel';
        $post->status = 'published';
        $post->published_at = now();

        $afterSave = $this->formats->fill($post, $validated, $request);

        if ($request->hasFile('image') && $format !== 'gallery') {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();
        $afterSave($post);

        return redirect()->route('tenant.channels.show', $channel->id)
            ->with('success', __('Post created successfully!'));
    }
}
