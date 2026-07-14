<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => 'required|string|min:3|max:1000',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Comment posted! Waiting for approval.');
    }

    public function approve(Comment $comment)
    {
        $this->authorize('approve', $comment);
        $comment->approve();

        return back()->with('success', 'Comment approved!');
    }

    public function reject(Comment $comment)
    {
        $this->authorize('approve', $comment);
        $comment->reject();

        return back()->with('success', 'Comment rejected!');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return back()->with('success', 'Comment deleted!');
    }
}
