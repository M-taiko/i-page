<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function like(Post $post)
    {
        $user = auth()->user();

        $existing = Reaction::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('type', 'like')
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('message', 'Like removed');
        }

        Reaction::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'type' => 'like',
        ]);

        return back()->with('success', 'Post liked!');
    }

    public function unlike(Post $post)
    {
        Reaction::where('post_id', $post->id)
            ->where('user_id', auth()->id())
            ->where('type', 'like')
            ->delete();

        return back();
    }
}
