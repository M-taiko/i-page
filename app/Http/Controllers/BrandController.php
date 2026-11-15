<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Channel;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Organization $organization)
    {
        $this->authorize('view', $organization);

        $brands = $organization->brands()
            ->withCount(['channels', 'followers'])
            ->orderBy('name')
            ->get()
            // Organization managers see every brand; brand admins/moderators only their own (ADR-015).
            ->filter(fn (Brand $brand) => $brand->isModeratedBy(auth()->user()))
            ->values();

        return view('organizations.brands.index', compact('organization', 'brands'));
    }

    /**
     * Brand command center (ADR-015): overview stats, the channel tree, posts across the brand,
     * every person in its channels, brand managers and activity. Brand admins and moderators only.
     */
    public function show(Request $request, Organization $organization, Brand $brand)
    {
        abort_unless($brand->organization_id === $organization->id, 404);
        $this->authorize('view', $brand);

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'overview';
        $channelIds = $brand->channelIds();
        $data = compact('organization', 'brand', 'tab') + [
            'canManage' => $brand->isManagedBy($request->user()),
            'canAssign' => $brand->canAssignManagers($request->user()),
            'channelCount' => count($channelIds),
        ];

        $data += match ($tab) {
            'overview' => $this->overviewData($request, $brand),
            'channels' => ['tree' => $this->channelTree($brand)],
            'posts' => ['posts' => $this->brandFeed($brand, $channelIds)->paginate(15)->withQueryString()],
            'people' => $this->peopleData($request, $brand, $channelIds),
            'managers' => $this->managersData($brand),
            'activity' => ['logs' => $this->activityQuery($brand, $channelIds)->paginate(25)->withQueryString()],
        };

        return view('organizations.brands.show', $data);
    }

    /** Assign (or change the role of) a brand admin/moderator. Organization admins only. */
    public function addManager(Request $request, Organization $organization, Brand $brand)
    {
        abort_unless($brand->organization_id === $organization->id, 404);
        abort_unless($brand->canAssignManagers($request->user()), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('organization_memberships', 'user_id')
                ->where('organization_id', $organization->id)->where('status', 'active')->whereNull('deleted_at')],
            'role' => 'required|in:admin,moderator',
        ]);

        $brand->managers()->syncWithoutDetaching([$validated['user_id'] => ['role' => $validated['role']]]);
        DB::table('notifications')->insert([
            'user_id' => $validated['user_id'],
            'type' => 'brand.manager',
            'data' => json_encode([
                'message' => __('You are now :role of :brand.', ['role' => $validated['role'] === 'admin' ? __('an admin') : __('a moderator'), 'brand' => $brand->name]),
                'link' => route('organizations.brands.show', [$organization, $brand]),
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', __('Brand manager saved.'));
    }

    public function removeManager(Request $request, Organization $organization, Brand $brand, User $user)
    {
        abort_unless($brand->organization_id === $organization->id, 404);
        abort_unless($brand->canAssignManagers($request->user()), 403);

        $brand->managers()->detach($user->id);

        return back()->with('success', __('Brand manager removed.'));
    }

    private const TABS = ['overview', 'channels', 'posts', 'people', 'managers', 'activity'];

    private function overviewData(Request $request, Brand $brand): array
    {
        $days = DashboardStatsService::normalizeRange($request->query('range'));

        return [
            'days' => $days,
            'stats' => app(DashboardStatsService::class)->brand($brand, $days),
            'latestPosts' => $this->brandFeed($brand, $brand->channelIds())->limit(6)->get(),
        ];
    }

    /** Brand posts plus every post in a brand channel, newest first. */
    private function brandFeed(Brand $brand, array $channelIds)
    {
        return Post::where(fn ($q) => $q->whereIn('channel_id', $channelIds)
                ->orWhere(fn ($b) => $b->whereNull('channel_id')->where('brand_id', $brand->id)->whereNotNull('brand_audience')))
            ->with(['author', 'channel', 'brand'])
            ->latest('created_at');
    }

    /**
     * The brand's channels as a tree. A root is a brand channel with no parent inside the brand;
     * each node is ['channel' => Channel, 'children' => [...]]. Cycle-safe.
     */
    private function channelTree(Brand $brand): array
    {
        $channels = $brand->channels()
            ->withCount(['users' => fn ($q) => $q->where('channel_user.status', 'approved'), 'posts'])
            ->orderBy('name')->get()->keyBy('id');
        $edges = DB::table('channel_channel')
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
        foreach ($channels as $id => $channel) {   // channels only reachable through a cycle
            if (! isset($seen[$id])) {
                $tree[] = $build($id);
            }
        }

        return $tree;
    }

    /** Everyone subscribed to a brand channel, with the channels they are in; staff vs visitors. */
    private function peopleData(Request $request, Brand $brand, array $channelIds): array
    {
        $staffIds = DB::table('organization_memberships')
            ->where('organization_id', $brand->organization_id)->where('status', 'active')->whereNull('deleted_at')
            ->pluck('role', 'user_id');
        $kind = in_array($request->query('kind'), ['staff', 'visitors'], true) ? $request->query('kind') : null;
        $search = trim((string) $request->query('q'));

        $people = User::query()
            ->whereIn('id', DB::table('channel_user')->whereIn('channel_id', $channelIds)->where('status', 'approved')->select('user_id'))
            ->when($kind === 'staff', fn ($q) => $q->whereIn('id', $staffIds->keys()))
            ->when($kind === 'visitors', fn ($q) => $q->whereNotIn('id', $staffIds->keys()))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")->orWhere('email', 'like', "%$search%")->orWhere('mobile', 'like', "%$search%")))
            ->orderBy('first_name')
            ->paginate(25)->withQueryString();

        $memberships = DB::table('channel_user')
            ->join('channels', 'channels.id', '=', 'channel_user.channel_id')
            ->whereIn('channel_user.channel_id', $channelIds)->whereIn('channel_user.user_id', $people->pluck('id'))
            ->where('channel_user.status', 'approved')
            ->get(['channel_user.user_id', 'channel_user.role', 'channels.id', 'channels.name'])
            ->groupBy('user_id');

        return compact('people', 'memberships', 'staffIds', 'kind', 'search');
    }

    private function managersData(Brand $brand): array
    {
        $managers = $brand->managers()->orderBy('first_name')->get();
        $candidates = User::whereIn('id', DB::table('organization_memberships')
                ->where('organization_id', $brand->organization_id)->where('status', 'active')->whereNull('deleted_at')->select('user_id'))
            ->whereNotIn('id', $managers->pluck('id'))
            ->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return compact('managers', 'candidates');
    }

    /** Audit entries about the brand, its channels and posts in them. */
    private function activityQuery(Brand $brand, array $channelIds)
    {
        $postIds = $this->brandFeed($brand, $channelIds)->select('posts.id');

        return ActivityLog::with('user')
            ->where(fn ($q) => $q
                ->where(fn ($w) => $w->where('subject_type', Brand::class)->where('subject_id', $brand->id))
                ->orWhere(fn ($w) => $w->where('subject_type', Channel::class)->whereIn('subject_id', $channelIds))
                ->orWhere(fn ($w) => $w->where('subject_type', Post::class)->whereIn('subject_id', $postIds)))
            ->latest();
    }

    public function create(Organization $organization)
    {
        $this->authorize('update', $organization);

        return view('organizations.brands.form', compact('organization'));
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $organization->brands()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'colors' => array_filter([
                'primary' => $validated['primary_color'] ?? null,
                'secondary' => $validated['secondary_color'] ?? null,
            ]),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->redirectAfterSave($organization, 'Brand created successfully.');
    }

    public function edit(Organization $organization, Brand $brand)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $brand);

        return view('organizations.brands.form', compact('organization', 'brand'));
    }

    public function update(Request $request, Organization $organization, Brand $brand)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $brand);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'description' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $brand->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'colors' => array_filter([
                'primary' => $validated['primary_color'] ?? null,
                'secondary' => $validated['secondary_color'] ?? null,
            ]),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->redirectAfterSave($organization, 'Brand updated successfully.');
    }

    public function destroy(Organization $organization, Brand $brand)
    {
        $this->authorize('update', $organization);
        $this->authorize('delete', $brand);

        $brand->delete();

        return $this->redirectAfterSave($organization, 'Brand deleted successfully.');
    }

    /**
     * Super Admin returns to the admin org panel; Org Admin to org settings.
     */
    private function redirectAfterSave(Organization $organization, string $message)
    {
        $route = auth()->user()->hasRole('super_admin')
            ? route('admin.organizations.show', $organization)
            : route('organizations.brands.index', $organization);

        return redirect($route)->with('success', $message);
    }
}
