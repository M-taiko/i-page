<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostApprovalService;
use App\Services\MediaService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostApprovalService $approvalService,
        private MediaService $mediaService
    ) {
    }

    public function index(Request $request)
    {
        // Super admin sees all posts, regular users see their org posts
        if (auth()->user()->hasRole('super_admin')) {
            $query = Post::query();
        } else {
            $userOrganization = auth()->user()->organizations()->first();
            if (!$userOrganization) {
                abort(403, 'User must belong to an organization');
            }
            $query = $userOrganization->posts();
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $posts = $query->latest()->paginate(20);

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $this->authorize('create', Post::class);

        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        return view('posts.form', compact('organization'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'body' => 'required|string|max:10000',
            'post_type' => 'required|in:announcement,news,offer,emergency',
            'priority' => 'required|in:low,medium,high,critical',
            'language' => 'required|in:en,ar',
            'requires_acknowledgment' => 'boolean',
            'is_emergency' => 'boolean',
            'status' => 'in:draft,pending_approval,published',
            'scheduled_for' => 'nullable|date_format:Y-m-d H:i',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $requestedStatus = $validated['status'] ?? 'draft';
        $canApprove = auth()->user()->can('approvePost', new Post(['organization_id' => $organization->id]));

        $post = $organization->posts()->create([
            'channel_id' => $organization->channels()->first()?->id,
            'author_id' => auth()->id(),
            'status' => ($requestedStatus === 'published' && !$canApprove) ? 'pending_approval' : $requestedStatus,
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'body' => $validated['body'],
            'post_type' => $validated['post_type'],
            'priority' => $validated['priority'],
            'language' => $validated['language'],
            'requires_acknowledgment' => $validated['requires_acknowledgment'] ?? false,
            'is_emergency' => $validated['is_emergency'] ?? false,
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null,
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $this->mediaService->upload($file, $post, 'public', 'post_attachment');
            }
        }

        if ($requestedStatus === 'published' && $canApprove) {
            $this->approvalService->publish($post, auth()->user());
        }

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        if (!in_array($post->status, ['draft', 'rejected'])) {
            abort(403, 'Can only edit draft or rejected posts.');
        }

        return view('posts.form', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        if (!in_array($post->status, ['draft', 'rejected'])) {
            abort(403, 'Can only edit draft or rejected posts.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'body' => 'required|string|max:10000',
            'post_type' => 'required|in:announcement,news,offer,emergency',
            'priority' => 'required|in:low,medium,high,critical',
            'language' => 'required|in:en,ar',
            'requires_acknowledgment' => 'boolean',
            'is_emergency' => 'boolean',
            'status' => 'in:draft,pending_approval,published',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_image' => 'boolean',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $imagePath = $post->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('posts', 'public');
        } elseif ($request->boolean('remove_image') && $imagePath) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        $requestedStatus = $validated['status'] ?? 'draft';

        $post->update([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'body' => $validated['body'],
            'post_type' => $validated['post_type'],
            'priority' => $validated['priority'],
            'language' => $validated['language'],
            'requires_acknowledgment' => $validated['requires_acknowledgment'] ?? false,
            'is_emergency' => $validated['is_emergency'] ?? false,
            'status' => $requestedStatus === 'published' ? 'draft' : $requestedStatus,
            'image_path' => $imagePath,
        ]);

        if ($requestedStatus === 'published' && auth()->user()->can('approvePost', $post)) {
            $this->approvalService->publish($post, auth()->user());
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $this->mediaService->upload($file, $post, 'public', 'post_attachment');
            }
        }

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        if (!in_array($post->status, ['draft', 'rejected'])) {
            abort(403, 'Can only delete draft or rejected posts.');
        }

        $post->media()->delete();
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    public function approve(Request $request, Post $post)
    {
        $this->authorize('approve', $post);

        if ($post->status !== 'pending_approval') {
            abort(422, 'Only pending posts can be approved.');
        }

        $this->approvalService->approve($post, auth()->user());

        return back()->with('success', 'Post approved.');
    }

    public function reject(Request $request, Post $post)
    {
        $this->authorize('reject', $post);

        if ($post->status !== 'pending_approval') {
            abort(422, 'Only pending posts can be rejected.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $this->approvalService->reject($post, auth()->user(), $validated['reason'] ?? '');

        return back()->with('success', 'Post rejected.');
    }

    public function publish(Request $request, Post $post)
    {
        $this->authorize('publish', $post);

        $validated = $request->validate([
            'scheduled_for' => 'nullable|date_format:Y-m-d H:i|after:now',
        ]);

        if ($validated['scheduled_for'] ?? null) {
            $this->approvalService->schedule($post, $validated['scheduled_for'], auth()->user());
            return back()->with('success', 'Post scheduled.');
        }

        $this->approvalService->publish($post, auth()->user());

        return back()->with('success', 'Post published.');
    }

    public function archive(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $this->approvalService->archive($post, auth()->user());

        return back()->with('success', 'Post archived.');
    }
}
