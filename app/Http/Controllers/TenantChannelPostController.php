<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use Illuminate\Http\Request;

class TenantChannelPostController extends Controller
{
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

        $validated = $request->validate([
            'body' => 'required|string|min:10|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post = new Post();
        $post->channel_id = $channel->id;
        $post->author_id = auth()->id();
        $post->organization_id = $organization->id;
        $post->body = $validated['body'];
        $post->audience = 'channel';
        $post->status = 'published';
        $post->published_at = now();

        if ($request->hasFile('image')) {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();

        return redirect()->route('tenant.channels.show', $channel->id)
            ->with('success', 'Post created successfully!');
    }
}
