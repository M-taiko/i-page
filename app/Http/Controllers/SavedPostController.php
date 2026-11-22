<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Saved posts: the bookmark on every post card and the "Saved" list (docs/27). */
class SavedPostController extends Controller
{
    public function index(Request $request, FeedService $feed): View|JsonResponse
    {
        $user = $request->user();
        $query = $user->savedPosts()->getQuery()->select('posts.*')->orderByDesc('post_saves.created_at');
        $posts = $feed->page($query, $user);

        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('guest.partials._feed-cards', ['posts' => $posts])->render(),
                'next' => $posts->nextPageUrl(),
            ]);
        }

        return view('saved.index', compact('posts'));
    }

    public function toggle(Request $request, Post $post): RedirectResponse|JsonResponse
    {
        abort_unless($post->status === 'published' && $post->isVisibleTo($request->user()), 404);

        $result = $request->user()->savedPosts()->toggle($post->id);
        $saved = ! empty($result['attached']);

        return $request->expectsJson()
            ? response()->json(['saved' => $saved])
            : back()->with('success', $saved ? __('Saved.') : __('Removed from saved.'));
    }
}
