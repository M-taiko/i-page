<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Channel;
use App\Models\Organization;
use App\Models\Post;
use App\Models\PostReceipt;
use App\Models\QrCode;
use App\Models\QrScanLog;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Read models for the Platform CRM (super admin) and the Organization CRM.
 * Every organization-scoped method constrains by organization_id.
 */
class CrmService
{
    public const HEALTHY = 70;
    public const ATTENTION = 40;

    // ---------------------------------------------------------------- Platform

    /** Organizations 360 list with per-row health inputs. */
    public function organizations(Request $request): LengthAwarePaginator
    {
        $recent = CarbonImmutable::now()->subDays(30);

        $query = Organization::query()
            ->withCount([
                'users as active_members_count' => fn ($q) => $q->where('organization_memberships.status', 'active'),
                'channels',
                'posts as recent_posts_count' => fn ($q) => $q->where('created_at', '>=', $recent),
                'posts',
                'tickets as open_tickets_count' => fn ($q) => $q->where('status', '!=', 'closed'),
            ])
            ->addSelect([
                'last_activity_at' => ActivityLog::select('created_at')
                    ->whereColumn('organization_id', 'organizations.id')->latest()->limit(1),
                'qr_scans_total' => QrCode::selectRaw('coalesce(sum(scan_count),0)')
                    ->whereColumn('organization_id', 'organizations.id'),
            ])
            ->when($request->query('q'), fn (Builder $q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$term%")->orWhere('email', 'like', "%$term%")->orWhere('city', 'like', "%$term%")))
            ->when($request->query('status'), fn (Builder $q, $s) => $q->where('status', $s))
            ->orderBy($request->query('sort') === 'name' ? 'name' : 'created_at', $request->query('sort') === 'name' ? 'asc' : 'desc');

        $page = $query->paginate(15)->withQueryString();
        $page->getCollection()->transform(function (Organization $org) {
            $org->health = $this->health($org->recent_posts_count, $org->active_members_count, $org->channels_count, $org->last_activity_at);

            return $org;
        });

        if ($health = $request->query('health')) {
            $page->setCollection($page->getCollection()->filter(fn ($o) => $o->health['key'] === $health)->values());
        }

        return $page;
    }

    public function summary(): array
    {
        return [
            'organizations' => Organization::count(),
            'active' => Organization::where('status', 'active')->count(),
            'users' => User::count(),
            'visitors' => User::whereDoesntHave('organizationMemberships')->count(),
            'openTickets' => Ticket::where('status', '!=', 'closed')->count(),
            'activity24h' => ActivityLog::where('created_at', '>=', now()->subDay())->count(),
        ];
    }

    /** Everything the Organization 360 tabs need. */
    public function organization360(Organization $org): array
    {
        $recent = CarbonImmutable::now()->subDays(30);
        $lastActivity = ActivityLog::where('organization_id', $org->id)->latest()->value('created_at');
        $activeMembers = $org->memberships()->where('status', 'active')->count();
        $channels = $org->channels()->withCount(['posts', 'users'])->latest()->get();
        $recentPosts = $org->posts()->where('created_at', '>=', $recent)->count();

        return [
            'organization' => $org,
            'health' => $this->health($recentPosts, $activeMembers, $channels->count(), $lastActivity),
            'lastActivity' => $lastActivity,
            'stats' => [
                'members' => $org->memberships()->count(),
                'activeMembers' => $activeMembers,
                'followers' => $org->followers()->count(),
                'channels' => $channels->count(),
                'posts' => $org->posts()->count(),
                'recentPosts' => $recentPosts,
                'openTickets' => $org->tickets()->where('status', '!=', 'closed')->count(),
                'allTickets' => $org->tickets()->count(),
                'qrScans' => (int) $org->qrCodes()->sum('scan_count'),
                'brands' => $org->brands()->count(),
                'locations' => $org->locations()->count(),
            ],
            // Skip memberships whose account was deleted.
            'memberships' => $org->memberships()->whereHas('user')->with('user')->latest()->limit(50)->get(),
            'channels' => $channels,
            'posts' => $org->posts()->with(['author', 'channel'])->latest()->limit(15)->get(),
            'tickets' => $org->tickets()->with('assignee')->latest()->limit(15)->get(),
            'qrCodes' => $org->qrCodes()->latest()->limit(15)->get(),
            'activity' => ActivityLog::with('user')->where('organization_id', $org->id)->latest()->limit(25)->get(),
        ];
    }

    /** All accounts on the platform. */
    public function people(Request $request): LengthAwarePaginator
    {
        return User::query()
            ->withCount(['organizationMemberships', 'subscribedChannels', 'followedOrganizations'])
            ->when($request->query('q'), fn (Builder $q, $term) => $q->where(fn ($w) => $w
                ->where('first_name', 'like', "%$term%")->orWhere('last_name', 'like', "%$term%")
                ->orWhere('email', 'like', "%$term%")->orWhere('username', 'like', "%$term%")))
            ->when($request->query('type') === 'staff', fn (Builder $q) => $q->whereHas('organizationMemberships'))
            ->when($request->query('type') === 'visitors', fn (Builder $q) => $q->whereDoesntHave('organizationMemberships'))
            ->when($request->query('type') === 'super_admin', fn (Builder $q) => $q->role('super_admin'))
            ->with('roles')
            ->orderByDesc('last_seen_at')->orderByDesc('created_at')
            ->paginate(20)->withQueryString();
    }

    /** Person 360 (platform view: across all organizations). */
    public function person360(User $user): array
    {
        return [
            'user' => $user->load('roles'),
            'memberships' => $user->organizationMemberships()->with('organization')->get(),
            'channels' => $user->subscribedChannels()->with('organization')->get(),
            'followed' => $user->followedOrganizations()->get(),
            'tickets' => Ticket::with('organization')->where('created_by', $user->id)->latest()->limit(10)->get(),
            'stats' => [
                'posts' => Post::where('author_id', $user->id)->count(),
                'reactions' => $user->reactions()->count(),
                'comments' => $user->comments()->count(),
                'qrScans' => QrScanLog::where('user_id', $user->id)->count(),
                'received' => PostReceipt::where('user_id', $user->id)->count(),
                'read' => PostReceipt::where('user_id', $user->id)->whereNotNull('read_at')->count(),
            ],
            'activity' => ActivityLog::with('organization')->where('user_id', $user->id)->latest()->limit(20)->get(),
        ];
    }

    // ------------------------------------------------------------ Activity log

    /** Audit trail; pass an organization to scope it. */
    public function activity(Request $request, ?Organization $organization = null): LengthAwarePaginator
    {
        return ActivityLog::query()->with(['user', 'organization'])
            ->when($organization, fn (Builder $q) => $q->where('organization_id', $organization->id))
            ->when(! $organization && $request->query('organization'), fn (Builder $q, $id) => $q->where('organization_id', $id))
            ->when($request->query('event'), fn (Builder $q, $e) => $q->where('event', $e))
            ->when($request->query('user'), fn (Builder $q, $id) => $q->where('user_id', $id))
            ->when($request->query('from'), fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->query('to'), fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest('id')->paginate(25)->withQueryString();
    }

    // ------------------------------------------------------- Organization CRM

    /** Users related to the organization: members, followers, channel subscribers. */
    public function contacts(Organization $org, Request $request): LengthAwarePaginator
    {
        $page = $this->contactsQuery($org)
            ->when($request->query('q'), fn (Builder $q, $term) => $q->where(fn ($w) => $w
                ->where('first_name', 'like', "%$term%")->orWhere('last_name', 'like', "%$term%")
                ->orWhere('email', 'like', "%$term%")->orWhere('username', 'like', "%$term%")))
            ->when($request->query('relationship') === 'member', fn (Builder $q) => $q->whereHas('organizationMemberships', fn ($m) => $m->where('organization_id', $org->id)))
            ->when($request->query('relationship') === 'follower', fn (Builder $q) => $q->whereHas('followedOrganizations', fn ($f) => $f->where('organizations.id', $org->id)))
            ->when($request->query('relationship') === 'subscriber', fn (Builder $q) => $q->whereHas('subscribedChannels', fn ($c) => $c->where('channels.organization_id', $org->id)))
            ->withCount(['subscribedChannels as org_channels_count' => fn ($c) => $c->where('channels.organization_id', $org->id)])
            ->orderByDesc('last_seen_at')->orderBy('first_name')
            ->paginate(20)->withQueryString();

        $ids = $page->pluck('id');
        $memberships = $org->memberships()->whereIn('user_id', $ids)->get()->keyBy('user_id');
        $followers = $org->followers()->whereIn('users.id', $ids)->pluck('users.id')->flip();

        $page->getCollection()->transform(function (User $u) use ($memberships, $followers) {
            $u->membership = $memberships->get($u->id);
            $u->is_follower = $followers->has($u->id);
            $u->relationships = array_values(array_filter([
                $u->membership ? 'member' : null,
                $u->is_follower ? 'follower' : null,
                $u->org_channels_count > 0 ? 'subscriber' : null,
            ]));

            return $u;
        });

        return $page;
    }

    public function contactSummary(Organization $org): array
    {
        $members = $org->memberships()->count();
        $followers = $org->followers()->count();

        return [
            'total' => $this->contactsQuery($org)->count(),
            'members' => $members,
            'activeMembers' => $org->memberships()->where('status', 'active')->count(),
            'pendingMembers' => $org->memberships()->whereIn('status', ['pending', 'invited'])->count(),
            'followers' => $followers,
            'subscribers' => User::whereHas('subscribedChannels', fn ($c) => $c->where('channels.organization_id', $org->id))->count(),
        ];
    }

    public function belongsToOrganization(Organization $org, User $user): bool
    {
        return $this->contactsQuery($org)->whereKey($user->id)->exists();
    }

    /** Contact 360 scoped to one organization. */
    public function contact360(Organization $org, User $user): array
    {
        $postIds = Post::where('organization_id', $org->id)->select('id');
        $receipts = fn () => PostReceipt::where('user_id', $user->id)->whereIn('post_id', $postIds);

        return [
            'user' => $user,
            'membership' => $org->memberships()->where('user_id', $user->id)->with(['department', 'manager'])->first(),
            'isFollower' => $org->followers()->where('users.id', $user->id)->exists(),
            'channels' => $user->subscribedChannels()->where('channels.organization_id', $org->id)->get(),
            'tickets' => $org->tickets()->where('created_by', $user->id)->latest()->limit(10)->get(),
            'stats' => [
                'received' => $receipts()->count(),
                'viewed' => $receipts()->whereNotNull('first_viewed_at')->count(),
                'read' => $receipts()->whereNotNull('read_at')->count(),
                'acknowledged' => $receipts()->whereNotNull('acknowledged_at')->count(),
                'posts' => Post::where('organization_id', $org->id)->where('author_id', $user->id)->count(),
                'tickets' => $org->tickets()->where('created_by', $user->id)->count(),
                'qrScans' => QrScanLog::where('organization_id', $org->id)->where('user_id', $user->id)->count(),
            ],
            'activity' => ActivityLog::where('organization_id', $org->id)->where('user_id', $user->id)->latest()->limit(20)->get(),
        ];
    }

    private function contactsQuery(Organization $org): Builder
    {
        return User::query()->where(fn ($q) => $q
            ->whereHas('organizationMemberships', fn ($m) => $m->where('organization_id', $org->id))
            ->orWhereHas('followedOrganizations', fn ($f) => $f->where('organizations.id', $org->id))
            ->orWhereHas('subscribedChannels', fn ($c) => $c->where('channels.organization_id', $org->id)));
    }

    // ------------------------------------------------------------------ Health

    /** @return array{score:int,key:string,label:string,variant:string} */
    public function health(int $recentPosts, int $activeMembers, int $channels, mixed $lastActivity): array
    {
        $score = ($recentPosts > 0 ? 30 : 0)
            + ($activeMembers >= 2 ? 20 : 0)
            + ($channels >= 1 ? 20 : 0)
            + ($lastActivity && CarbonImmutable::parse($lastActivity)->gte(now()->subDays(14)) ? 30 : 0);

        [$key, $label, $variant] = match (true) {
            $score >= self::HEALTHY => ['healthy', __('Healthy'), 'success'],
            $score >= self::ATTENTION => ['attention', __('Attention'), 'warning'],
            default => ['risk', __('At risk'), 'danger'],
        };

        return compact('score', 'key', 'label', 'variant');
    }

    /** Human label for an activity subject, e.g. "Post #12". */
    public static function subjectLabel(ActivityLog $log): string
    {
        return $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : '—';
    }
}
