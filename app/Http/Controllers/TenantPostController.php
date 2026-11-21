<?php

namespace App\Http\Controllers;

use App\Models\Channel;
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
 * Tenant posts (ADR-016): published by the organization itself (channel_id NULL) and placed on
 * the public tenant page, in every channel, or in chosen channels (optionally cascading to their
 * sub-channels). Organization admins/managers only.
 */
class TenantPostController extends Controller
{
    public function __construct(private PostFormatService $formats)
    {
    }

    public function index(Request $request)
    {
        $organization = $this->organization();

        $posts = Post::where('organization_id', $organization->id)->whereNull('channel_id')->whereNotNull('placement')
            ->with(['author', 'targetChannels', 'organization'])
            ->latest('created_at')->paginate(15);

        return view('organizations.posts.index', compact('organization', 'posts'));
    }

    public function create()
    {
        $organization = $this->organization();

        return view('organizations.posts.create', ['organization' => $organization, 'channelTree' => self::channelTree($organization)]);
    }

    public function store(Request $request)
    {
        $organization = $this->organization();
        $format = $this->formats->formatOf($request);

        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:500',
            'body' => ['required', 'string', $format === 'standard' ? 'min:10' : 'min:3', 'max:5000'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ] + $this->publishRules($organization) + $this->formats->rules($request));

        if ($format === 'poll' && ! $this->formats->pollOptionsAreEnough($validated)) {
            throw ValidationException::withMessages(['poll_options' => __('Add at least :n options.', ['n' => config('posts.limits.poll_options_min')])]);
        }

        $post = new Post();
        $post->channel_id = null;
        $post->organization_id = $organization->id;
        $post->author_id = $request->user()->id;
        $post->title = $validated['title'] ?? null;
        $post->summary = $validated['summary'] ?? null;
        $post->body = $validated['body'];
        $post->audience = 'all';
        $post->status = 'published';
        $post->published_at = now();

        $afterSave = $this->formats->fill($post, $validated, $request);
        $this->applyPublishOptions($post, $request);

        if ($request->hasFile('image') && $format !== 'gallery') {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();
        $this->syncTargets($post, $validated);
        $afterSave($post);
        $sent = app(PostNotificationService::class)->published($post);

        return redirect()->route('organizations.posts.index')->with('success', __('Post published to :n channels. :m people notified.', [
            'n' => count($post->receivingChannelIds()),
            'm' => $sent['managers'] + $sent['subscribers'],
        ]));
    }

    public function edit(Post $post)
    {
        $organization = $this->organization();
        $this->authorizePost($organization, $post);
        $post->load('targetChannels');

        return view('organizations.posts.edit', ['organization' => $organization, 'post' => $post, 'channelTree' => self::channelTree($organization)]);
    }

    public function update(Request $request, Post $post)
    {
        $organization = $this->organization();
        $this->authorizePost($organization, $post);

        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:500',
            'body' => 'required|string|min:3|max:5000',
            'card_style' => ['nullable', Rule::in(array_keys(config('posts.card_styles')))],
            'priority' => ['nullable', Rule::in(array_keys(config('posts.priorities')))],
            'badge_key' => ['nullable', Rule::in(array_keys(config('posts.badges')))],
            'badge_label' => 'nullable|string|max:40',
        ] + $this->publishRules($organization));

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
        $this->syncTargets($post, $validated);

        return redirect()->route('organizations.posts.index')->with('success', __('Post updated.'));
    }

    public function destroy(Post $post)
    {
        $organization = $this->organization();
        $this->authorizePost($organization, $post);

        if ($thumb = $post->option('pin_thumb')) {
            Storage::disk('public')->delete($thumb);
        }
        $post->delete();

        return back()->with('success', __('Post deleted.'));
    }

    /** Toggle the highlight: a tenant pin shows on the tenant page and in every receiving channel. */
    public function pin(Post $post)
    {
        $organization = $this->organization();
        $this->authorizePost($organization, $post);

        $post->pinned_until = $post->isPinned() ? null : now()->addYears(10);
        $post->save();

        return back()->with('success', $post->isPinned() ? __('Pinned on the tenant page and its channels.') : __('Post unpinned.'));
    }

    /**
     * The tenant's channels as a tree: [['channel' => Channel, 'children' => [...]], ...].
     * Roots are channels without a parent; cycle-safe.
     */
    public static function channelTree(Organization $organization, bool $withCounts = false): array
    {
        $channels = Channel::where('organization_id', $organization->id)
            ->when($withCounts, fn ($q) => $q->withCount(['users' => fn ($u) => $u->where('channel_user.status', 'approved'), 'posts']))
            ->orderBy('name')->get()->keyBy('id');
        $edges = \Illuminate\Support\Facades\DB::table('channel_channel')
            ->whereIn('child_channel_id', $channels->keys())->whereIn('parent_channel_id', $channels->keys())
            ->get(['parent_channel_id', 'child_channel_id']);
        $childrenOf = $edges->groupBy('parent_channel_id')->map(fn ($g) => $g->pluck('child_channel_id')->all());
        $hasParent = $edges->pluck('child_channel_id')->flip();

        $seen = [];
        $build = function ($id) use (&$build, &$seen, $channels, $childrenOf) {
            $seen[$id] = true;
            $children = [];
            foreach ($childrenOf[$id] ?? [] as $child) {
                if (! isset($seen[$child]) && isset($channels[$child])) {
                    $children[] = $build($child);
                }
            }

            return ['channel' => $channels[$id], 'children' => $children];
        };

        $tree = [];
        foreach ($channels as $id => $channel) {
            if (! isset($hasParent[$id])) {
                $tree[] = $build($id);
            }
        }
        foreach ($channels as $id => $channel) {   // only reachable through a cycle
            if (! isset($seen[$id])) {
                $tree[] = $build($id);
            }
        }

        return $tree;
    }

    private function publishRules(Organization $organization): array
    {
        return [
            'placement' => ['required', Rule::in(Post::PLACEMENTS)],
            'channel_ids' => ['required_if:placement,selected', 'array'],
            'channel_ids.*' => ['integer', Rule::exists('channels', 'id')->where('organization_id', $organization->id)->whereNull('deleted_at')],
            'cascade' => 'nullable|boolean',
            'show_on_page' => 'nullable|boolean',
            'pinned' => 'nullable|boolean',
            'pinned_until' => 'nullable|date|after:now',
            'pin_thumb' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_pin_thumb' => 'nullable|boolean',
        ];
    }

    private function applyPublishOptions(Post $post, Request $request): void
    {
        $post->placement = $request->input('placement');
        $post->cascade = $post->placement === 'selected' && $request->boolean('cascade');
        // "Tenant page only" always shows on the page.
        $post->show_on_page = $post->placement === 'page' || $request->boolean('show_on_page');

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

    private function syncTargets(Post $post, array $validated): void
    {
        $post->targetChannels()->sync($post->placement === 'selected' ? array_map('intval', $validated['channel_ids'] ?? []) : []);
    }

    /** The current tenant; only its admins/managers (and super admins) publish tenant posts. */
    private function organization(): Organization
    {
        $user = auth()->user();
        $organization = $user->currentOrganization;
        abort_unless($organization && Channel::isOrgManager($user, $organization->id), 403);

        return $organization;
    }

    private function authorizePost(Organization $organization, Post $post): void
    {
        abort_unless($post->isTenantPost() && $post->organization_id === $organization->id, 404);
    }
}
