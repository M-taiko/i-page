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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Computes dashboard analytics. All numbers shown on the super admin and
 * organization dashboards come from here so Blade stays free of queries.
 */
class DashboardStatsService
{
    public const RANGES = [7, 30, 90];

    private const CACHE_SECONDS = 300;

    public static function normalizeRange(mixed $days): int
    {
        return in_array((int) $days, self::RANGES, true) ? (int) $days : 30;
    }

    /** Platform-wide statistics (super admin). */
    public function platform(int $days): array
    {
        return Cache::remember("dash.platform.$days", self::CACHE_SECONDS, function () use ($days) {
            $since = CarbonImmutable::now()->subDays($days)->startOfDay();
            $prev = $since->subDays($days);

            $orgStatus = Organization::query()
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

            $topOrgs = Organization::query()
                ->withCount(['posts', 'users', 'channels'])
                ->orderByDesc('posts_count')->limit(6)->get(['id', 'name']);

            return [
                'days' => $days,
                'kpis' => [
                    $this->kpi(__('Organizations'), 'briefcase', 'primary', Organization::count(), $this->countBetween(Organization::query(), $since), $this->countBetween(Organization::query(), $prev, $since)),
                    $this->kpi(__('Total Users'), 'users', 'success', User::count(), $this->countBetween(User::query(), $since), $this->countBetween(User::query(), $prev, $since)),
                    $this->kpi(__('Total Posts'), 'file-text', 'info', Post::count(), $this->countBetween(Post::query(), $since), $this->countBetween(Post::query(), $prev, $since)),
                    $this->kpi(__('Channels'), 'message-circle', 'purple', Channel::count(), $this->countBetween(Channel::query(), $since), $this->countBetween(Channel::query(), $prev, $since)),
                    $this->kpi(__('QR Scans'), 'grid', 'warning', (int) QrCode::sum('scan_count'), $this->countBetween(QrScanLog::query(), $since), $this->countBetween(QrScanLog::query(), $prev, $since)),
                    $this->kpi(__('Open Tickets'), 'tag', 'danger', Ticket::where('status', '!=', 'closed')->count(), $this->countBetween(Ticket::query(), $since), $this->countBetween(Ticket::query(), $prev, $since)),
                ],
                'growth' => $this->series($days, [
                    __('Posts') => $this->perDay(Post::query(), $since),
                    __('New users') => $this->perDay(User::query(), $since),
                    __('New organizations') => $this->perDay(Organization::query(), $since),
                ]),
                'scans' => $this->series($days, [__('QR scans') => $this->perDay(QrScanLog::query(), $since)]),
                'orgStatus' => [
                    'labels' => [__('Active'), __('Suspended'), __('Cancelled')],
                    'data' => [(int) ($orgStatus['active'] ?? 0), (int) ($orgStatus['suspended'] ?? 0), (int) ($orgStatus['cancelled'] ?? 0)],
                ],
                'topOrgs' => [
                    'labels' => $topOrgs->pluck('name')->all(),
                    'posts' => $topOrgs->pluck('posts_count')->all(),
                    'users' => $topOrgs->pluck('users_count')->all(),
                    'channels' => $topOrgs->pluck('channels_count')->all(),
                ],
                'devices' => $this->devices(QrScanLog::query(), $since),
                'postTypes' => $this->postTypes(Post::query(), $since),
                'engagement' => $this->engagement(PostReceipt::query(), $since),
                'ticketsByStatus' => Ticket::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all(),
                'recentActivity' => ActivityLog::with('user')->latest()->limit(8)->get(),
                'newestOrganizations' => Organization::withCount('users', 'channels', 'posts')->latest()->limit(6)->get(),
                'openTickets' => Ticket::where('status', '!=', 'closed')->with('organization')->latest()->limit(6)->get(),
                'totalChannelsActive' => Channel::where('status', 'active')->count(),
            ];
        });
    }

    /** Statistics scoped to a single organization. */
    public function organization(Organization $organization, int $days): array
    {
        return Cache::remember("dash.org.{$organization->id}.$days", self::CACHE_SECONDS, function () use ($organization, $days) {
            $since = CarbonImmutable::now()->subDays($days)->startOfDay();
            $prev = $since->subDays($days);
            $id = $organization->id;

            $posts = fn () => Post::where('organization_id', $id);
            $tickets = fn () => Ticket::where('organization_id', $id);
            $scans = fn () => QrScanLog::where('organization_id', $id);
            $receipts = fn () => PostReceipt::whereIn('post_id', Post::where('organization_id', $id)->select('id'));
            $members = $organization->users();

            return [
                'days' => $days,
                'kpis' => [
                    $this->kpi(__('Posts'), 'file-text', 'primary', $posts()->count(), $this->countBetween($posts(), $since), $this->countBetween($posts(), $prev, $since)),
                    $this->kpi(__('Team Members'), 'users', 'success', $members->count(), 0, 0),
                    $this->kpi(__('Channels'), 'message-circle', 'purple', $organization->channels()->count(), $this->countBetween(Channel::where('organization_id', $id), $since), $this->countBetween(Channel::where('organization_id', $id), $prev, $since)),
                    $this->kpi(__('QR Scans'), 'grid', 'warning', (int) QrCode::where('organization_id', $id)->sum('scan_count'), $this->countBetween($scans(), $since), $this->countBetween($scans(), $prev, $since)),
                    $this->kpi(__('Open Tickets'), 'tag', 'danger', $tickets()->where('status', '!=', 'closed')->count(), $this->countBetween($tickets(), $since), $this->countBetween($tickets(), $prev, $since)),
                    $this->kpi(__('Followers'), 'heart', 'info', $organization->followers()->count(), 0, 0),
                ],
                'growth' => $this->series($days, [
                    __('Posts') => $this->perDay($posts(), $since),
                    __('Tickets') => $this->perDay($tickets(), $since),
                ]),
                'scans' => $this->series($days, [__('QR scans') => $this->perDay($scans(), $since)]),
                'devices' => $this->devices($scans(), $since),
                'postTypes' => $this->postTypes($posts(), $since),
                'engagement' => $this->engagement($receipts(), $since),
                'postStatus' => $posts()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all(),
                'topChannels' => Channel::where('organization_id', $id)->withCount('posts', 'users')->orderByDesc('posts_count')->limit(5)->get(),
                'recentActivity' => ActivityLog::with('user')->where('organization_id', $id)->latest()->limit(8)->get(),
                'recentPosts' => $posts()->with('author')->latest()->limit(5)->get(),
                'openTickets' => $tickets()->where('status', '!=', 'closed')->latest()->limit(5)->get(),
            ];
        });
    }

    private function kpi(string $label, string $icon, string $variant, int $total, int $current, int $previous): array
    {
        $change = $previous > 0 ? round((($current - $previous) / $previous) * 100) : ($current > 0 ? 100 : 0);

        return compact('label', 'icon', 'variant', 'total', 'current', 'previous', 'change');
    }

    private function countBetween(Builder $query, CarbonImmutable $from, ?CarbonImmutable $to = null): int
    {
        return $query->where('created_at', '>=', $from)
            ->when($to, fn ($q) => $q->where('created_at', '<', $to))
            ->count();
    }

    /** @return Collection<string,int> date (Y-m-d) => count */
    private function perDay(Builder $query, CarbonImmutable $since): Collection
    {
        return $query->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')->pluck('total', 'day');
    }

    /** Builds a zero-filled series so charts always have one point per day. */
    private function series(int $days, array $datasets): array
    {
        $labels = [];
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = CarbonImmutable::now()->subDays($i);
            $dates[] = $d->format('Y-m-d');
            $labels[] = $d->format('M d');
        }

        return [
            'labels' => $labels,
            'datasets' => collect($datasets)->map(fn (Collection $counts, string $name) => [
                'label' => $name,
                'data' => array_map(fn ($date) => (int) ($counts[$date] ?? 0), $dates),
            ])->values()->all(),
        ];
    }

    private function devices(Builder $scans, CarbonImmutable $since): array
    {
        $rows = $scans->where('created_at', '>=', $since)
            ->selectRaw("coalesce(device_type, 'unknown') as device, count(*) as total")
            ->groupBy('device')->pluck('total', 'device');

        return ['labels' => $rows->keys()->map(fn ($k) => ucfirst($k))->all(), 'data' => $rows->values()->all()];
    }

    private function postTypes(Builder $posts, CarbonImmutable $since): array
    {
        $rows = $posts->where('created_at', '>=', $since)
            ->selectRaw("coalesce(post_type, 'other') as type, count(*) as total")
            ->groupBy('type')->pluck('total', 'type');

        return ['labels' => $rows->keys()->map(fn ($k) => ucfirst(str_replace('_', ' ', $k)))->all(), 'data' => $rows->values()->all()];
    }

    private function engagement(Builder $receipts, CarbonImmutable $since): array
    {
        $row = $receipts->where('created_at', '>=', $since)->selectRaw(
            'count(*) as total, count(delivered_at) as delivered, count(first_viewed_at) as viewed, count(read_at) as `read`, count(acknowledged_at) as acknowledged'
        )->first();

        $total = (int) ($row->total ?? 0);
        $steps = [
            __('Delivered') => (int) ($row->delivered ?? 0),
            __('Viewed') => (int) ($row->viewed ?? 0),
            __('Read') => (int) ($row->read ?? 0),
            __('Acknowledged') => (int) ($row->acknowledged ?? 0),
        ];

        return [
            'total' => $total,
            'steps' => collect($steps)->map(fn ($n, $label) => ['label' => $label, 'count' => $n, 'pct' => $total ? round($n / $total * 100) : 0])->values()->all(),
        ];
    }
}
