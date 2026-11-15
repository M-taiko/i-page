<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Organization;
use App\Models\Post;
use App\Services\PostFormatService;
use App\Services\PostNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Brand posts (ADR-015): one post published at brand level that every brand channel shows,
 * filtered by the audience chosen at publish time (all / public / private channels).
 * Stored with channel_id NULL and resolved at read time by Channel::feedQuery().
 */
class BrandPostController extends Controller
{
    public const AUDIENCES = ['all', 'public', 'private'];

    public function __construct(private PostFormatService $formats)
    {
    }

    public function create(Organization $organization, Brand $brand)
    {
        $this->authorizeBrand($organization, $brand, 'manage');

        return view('organizations.brands.posts.create', compact('organization', 'brand'));
    }

    public function store(Request $request, Organization $organization, Brand $brand)
    {
        $this->authorizeBrand($organization, $brand, 'manage');
        $format = $this->formats->formatOf($request);

        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:500',
            'body' => ['required', 'string', $format === 'standard' ? 'min:10' : 'min:3', 'max:5000'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ] + $this->publishRules() + $this->formats->rules($request));

        if ($format === 'poll' && ! $this->formats->pollOptionsAreEnough($validated)) {
            throw ValidationException::withMessages(['poll_options' => __('Add at least :n options.', ['n' => config('posts.limits.poll_options_min')])]);
        }

        $post = new Post();
        $post->channel_id = null;
        $post->brand_id = $brand->id;
        $post->organization_id = $brand->organization_id;
        $post->author_id = $request->user()->id;
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
        $sent = app(PostNotificationService::class)->published($post);

        return redirect()->route('organizations.brands.show', [$organization, $brand, 'tab' => 'posts'])
            ->with('success', __('Brand post published to :n channels. :m people notified.', [
                'n' => count(app(PostNotificationService::class)->targetChannelIds($post)),
                'm' => $sent['managers'] + $sent['subscribers'],
            ]));
    }

    public function edit(Organization $organization, Brand $brand, Post $post)
    {
        $this->authorizePost($organization, $brand, $post);

        return view('organizations.brands.posts.edit', compact('organization', 'brand', 'post'));
    }

    public function update(Request $request, Organization $organization, Brand $brand, Post $post)
    {
        $this->authorizePost($organization, $brand, $post);

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

        return redirect()->route('organizations.brands.show', [$organization, $brand, 'tab' => 'posts'])->with('success', __('Post updated.'));
    }

    public function destroy(Organization $organization, Brand $brand, Post $post)
    {
        $this->authorizePost($organization, $brand, $post);

        if ($thumb = $post->option('pin_thumb')) {
            Storage::disk('public')->delete($thumb);
        }
        $post->delete();

        return back()->with('success', __('Post deleted.'));
    }

    /** Toggle the highlight: a brand pin shows in every brand channel that receives the post. */
    public function pin(Organization $organization, Brand $brand, Post $post)
    {
        $this->authorizePost($organization, $brand, $post);

        if ($post->isPinned()) {
            $post->pinned_until = null;
            $message = __('Post unpinned.');
        } else {
            $post->pinned_until = now()->addYears(10);
            $message = __('Pinned in every channel of :brand.', ['brand' => $brand->name]);
        }
        $post->save();

        return back()->with('success', $message);
    }

    private function publishRules(): array
    {
        return [
            'brand_audience' => ['required', Rule::in(self::AUDIENCES)],
            'pinned' => 'nullable|boolean',
            'pinned_until' => 'nullable|date|after:now',
            'pin_thumb' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_pin_thumb' => 'nullable|boolean',
        ];
    }

    private function applyPublishOptions(Post $post, Request $request): void
    {
        $post->brand_audience = $request->input('brand_audience');
        $post->cascade = false;

        if (! $request->boolean('pinned')) {
            $post->pinned_until = null;
        } elseif ($request->filled('pinned_until')) {
            $post->pinned_until = Carbon::parse($request->input('pinned_until'));
        } elseif (! $post->isPinned()) {
            $post->pinned_until = now()->addYears(10);
        }

        $options = $post->options ?? [];
        unset($options['pin_scope']);
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

    /** 'manage' = brand admins (publish); 'moderate' = admins + moderators (edit/delete/pin). */
    private function authorizeBrand(Organization $organization, Brand $brand, string $level): void
    {
        abort_unless($brand->organization_id === $organization->id, 404);
        $user = auth()->user();
        abort_unless($level === 'manage' ? $brand->isManagedBy($user) : $brand->isModeratedBy($user), 403);
    }

    private function authorizePost(Organization $organization, Brand $brand, Post $post): void
    {
        abort_unless($post->brand_id === $brand->id && $post->isBrandPost(), 404);
        $this->authorizeBrand($organization, $brand, 'moderate');
    }
}
