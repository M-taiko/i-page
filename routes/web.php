<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\QrScanController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TenantDashboardController;
use App\Http\Controllers\TenantChannelController;
use App\Http\Controllers\TenantOrganizationController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SlaRuleController;
use App\Http\Controllers\AudienceSegmentController;
use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Super Admin Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-dashboard', function () {
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403);
        }
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// Homepage Route (Redirect based on auth status)
Route::get('/', function () {
    if (auth()->check()) {
        // Super Admin goes to admin dashboard
        if (auth()->user()->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Regular users go to feed
        return redirect()->route('user.feed');
    }

    // Guests can browse public channels without logging in.
    return redirect()->route('guest.home');
})->name('home.index');

// Public Browse Routes — used by BOTH guests and authenticated Layer 3 users.
// This is the single organization/channel browsing surface for end users;
// authenticated visitors get real follow/subscribe/like/comment actions,
// guests get a "sign in required" prompt on the same pages.
Route::group([], function () {
    Route::get('/browse', [GuestController::class, 'home'])->name('guest.home');
    Route::get('/browse/search', [GuestController::class, 'searchOrganizations'])->name('guest.search-organizations');
    Route::get('/browse/org/{organization:id}', [GuestController::class, 'organizationDetail'])->name('guest.organization-detail');
    Route::get('/browse/org/{organization:id}/channel/{channelSlug}', [GuestController::class, 'channelDetail'])->name('guest.channel-detail');
    Route::get('/browse/post/{post}', [GuestController::class, 'postDetail'])->name('guest.post-detail');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        try {
            $organization = auth()->user()->organizations()->first();
            if (!$organization) {
                Auth::logout();
                return redirect()->route('guest.home')->with('error', __('You do not have access to any organizations'));
            }

            return redirect()->route('dashboard.home', $organization->id);
        } catch (\Exception $e) {
            Auth::logout();
            return redirect()->route('guest.home');
        }
    })->name('home');
});

// User Feed Routes
// ============================================================
// LAYER 3 — End User (Telegram-like: discover, follow, subscribe, react, comment)
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/feed', [\App\Http\Controllers\UserFeedController::class, 'index'])->name('user.feed');
    Route::get('/feed/channels', [\App\Http\Controllers\UserFeedController::class, 'exploreChannels'])->name('user.explore-channels');
    Route::get('/feed/organizations', [\App\Http\Controllers\UserFeedController::class, 'exploreOrganizations'])->name('user.explore-organizations');

    // User Profile Settings
    Route::get('/profile/settings', [SettingsController::class, 'showProfile'])->name('profile.settings');
    Route::post('/profile/settings', [SettingsController::class, 'updateProfileOnly'])->name('profile.updateProfile');
    Route::post('/profile/settings/appearance', [SettingsController::class, 'updateAppearanceOnly'])->name('profile.updateAppearance');
    Route::post('/profile/settings/notifications', [SettingsController::class, 'updateNotificationsOnly'])->name('profile.updateNotifications');
    Route::post('/profile/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('profile.updateAvatar');
    Route::delete('/profile/settings/avatar', [SettingsController::class, 'removeAvatar'])->name('profile.removeAvatar');
    Route::post('/profile/settings/cover', [SettingsController::class, 'updateCover'])->name('profile.updateCover');
    Route::delete('/profile/settings/cover', [SettingsController::class, 'removeCover'])->name('profile.removeCover');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('user.notifications');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('user.notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('user.notifications.read-all');
});

// API Routes (for search, etc.) - Public search for browsing
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/organizations/search', [\App\Http\Controllers\UserFeedController::class, 'searchOrganizations'])->name('organizations.search');
});

// Comment Routes
Route::middleware('auth')->prefix('posts')->name('posts.')->group(function () {
    Route::post('{post}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::post('comments/{comment}/approve', [\App\Http\Controllers\CommentController::class, 'approve'])->name('comments.approve');
    Route::post('comments/{comment}/reject', [\App\Http\Controllers\CommentController::class, 'reject'])->name('comments.reject');
    Route::delete('comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('{post}/like', [\App\Http\Controllers\ReactionController::class, 'like'])->name('like');
});

// Follow Routes (organizations + brands)
Route::middleware('auth')->prefix('organizations')->name('organizations.')->group(function () {
    Route::post('{organization}/follow', [\App\Http\Controllers\FollowController::class, 'follow'])->name('follow');
    Route::post('{organization}/unfollow', [\App\Http\Controllers\FollowController::class, 'unfollow'])->name('unfollow');
});

Route::middleware('auth')->prefix('brands')->name('brands.')->group(function () {
    Route::post('{brand}/follow', [\App\Http\Controllers\FollowController::class, 'followBrand'])->name('follow');
    Route::post('{brand}/unfollow', [\App\Http\Controllers\FollowController::class, 'unfollowBrand'])->name('unfollow');
});

// Collections (personal folders of subscribed channels)
Route::middleware('auth')->prefix('collections')->name('collections.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CollectionController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\CollectionController::class, 'store'])->name('store');
    Route::post('reorder', [\App\Http\Controllers\CollectionController::class, 'reorder'])->name('reorder');
    Route::get('{collection}', [\App\Http\Controllers\CollectionController::class, 'show'])->name('show');
    Route::put('{collection}', [\App\Http\Controllers\CollectionController::class, 'update'])->name('update');
    Route::delete('{collection}', [\App\Http\Controllers\CollectionController::class, 'destroy'])->name('destroy');
    Route::post('{collection}/pin', [\App\Http\Controllers\CollectionController::class, 'togglePin'])->name('pin');
    Route::post('{collection}/mute', [\App\Http\Controllers\CollectionController::class, 'toggleMute'])->name('mute');
    Route::post('{collection}/channels/{channel}', [\App\Http\Controllers\CollectionController::class, 'addChannel'])->name('channels.add');
    Route::delete('{collection}/channels/{channel}', [\App\Http\Controllers\CollectionController::class, 'removeChannel'])->name('channels.remove');
    Route::post('{collection}/organizations/{organization}', [\App\Http\Controllers\CollectionController::class, 'addOrganization'])->name('organizations.add');
    Route::post('{collection}/brands/{brand}', [\App\Http\Controllers\CollectionController::class, 'addBrand'])->name('brands.add');
});

// ============================================================
// LAYER 1 — Super Admin (I-PAGE platform owner)
// ============================================================
Route::middleware(['auth', 'verified', 'CheckRole:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('organizations', OrganizationController::class);

    // Subscription lifecycle
    Route::post('organizations/{organization}/suspend', [OrganizationController::class, 'suspend'])->name('organizations.suspend');
    Route::post('organizations/{organization}/activate', [OrganizationController::class, 'activate'])->name('organizations.activate');
    Route::post('organizations/{organization}/cancel', [OrganizationController::class, 'cancel'])->name('organizations.cancel');

    // Manage any organization's brands
    Route::get('organizations/{organization}/brands/create', [BrandController::class, 'create'])->name('organizations.brands.create');
    Route::post('organizations/{organization}/brands', [BrandController::class, 'store'])->name('organizations.brands.store');
    Route::get('organizations/{organization}/brands/{brand}/edit', [BrandController::class, 'edit'])->name('organizations.brands.edit');
    Route::put('organizations/{organization}/brands/{brand}', [BrandController::class, 'update'])->name('organizations.brands.update');
    Route::delete('organizations/{organization}/brands/{brand}', [BrandController::class, 'destroy'])->name('organizations.brands.destroy');

    // Manage any organization's channels
    Route::get('organizations/{organization}/channels/create', [\App\Http\Controllers\AdminChannelController::class, 'create'])->name('organizations.channels.create');
    Route::post('organizations/{organization}/channels', [\App\Http\Controllers\AdminChannelController::class, 'store'])->name('organizations.channels.store');
    Route::get('organizations/{organization}/channels/{channel}/edit', [\App\Http\Controllers\AdminChannelController::class, 'edit'])->name('organizations.channels.edit');
    Route::put('organizations/{organization}/channels/{channel}', [\App\Http\Controllers\AdminChannelController::class, 'update'])->name('organizations.channels.update');
    Route::delete('organizations/{organization}/channels/{channel}', [\App\Http\Controllers\AdminChannelController::class, 'destroy'])->name('organizations.channels.destroy');
});

// Tenant (Organization Admin) Routes
Route::middleware(['auth', 'verified'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/', [TenantDashboardController::class, 'index'])->name('dashboard');
    Route::post('switch-organization', [TenantDashboardController::class, 'switchOrganization'])->name('switch-organization');
    Route::resource('channels', TenantChannelController::class);
    Route::post('channels/{channel}/toggle-status', [TenantChannelController::class, 'toggleStatus'])->name('channels.toggle-status');

    // Channel Posts
    Route::get('channels/{channel}/posts/create', [\App\Http\Controllers\TenantChannelPostController::class, 'create'])->name('channels.posts.create');
    Route::post('channels/{channel}/posts', [\App\Http\Controllers\TenantChannelPostController::class, 'store'])->name('channels.posts.store');

    // Channel Members (invite by email — registered or not — with a role)
    Route::post('channels/{channel}/members', [TenantChannelController::class, 'inviteMember'])->name('channels.members.store');
    Route::put('channels/{channel}/members/{user}', [TenantChannelController::class, 'updateMemberRole'])->name('channels.members.update');
    Route::delete('channels/{channel}/members/{user}', [TenantChannelController::class, 'removeMember'])->name('channels.members.destroy');
});

// Redirect guests trying to access protected routes
Route::middleware('guest')->prefix('admin')->group(function () {
    Route::get('{any?}', function () {
        return redirect()->route('guest.home');
    })->where('any', '.*');
});

Route::middleware('guest')->prefix('tenant')->group(function () {
    Route::get('{any?}', function () {
        return redirect()->route('guest.home');
    })->where('any', '.*');
});

// Public QR Code Scanning (Guest Access)
Route::prefix('qr')->name('qr.')->group(function () {
    Route::get('{code}', [QrScanController::class, 'redirect'])->name('redirect');
    Route::get('register/{channel_id}/{organization_id}', [QrScanController::class, 'guestRegisterForm'])->name('guest-register');
    Route::post('register', [QrScanController::class, 'guestRegisterStore'])->name('guest-register.store');
});

// ============================================================
// LAYER 2 — Organization Admin (own org only; blocked when org suspended/cancelled)
// ============================================================
Route::middleware(['auth', 'verified', 'organization.active'])->prefix('organizations')->name('organizations.')->group(function () {
    Route::get('/', function () {
        $organizations = auth()->user()->organizations;
        return view('organizations.index', compact('organizations'));
    })->name('index');

    // Organization Dashboard & Settings
    Route::get('dashboard', [\App\Http\Controllers\TenantOrganizationController::class, 'dashboard'])->name('dashboard');
    Route::get('settings', [\App\Http\Controllers\TenantOrganizationController::class, 'settings'])->name('settings');
    Route::put('{organization}', [\App\Http\Controllers\TenantOrganizationController::class, 'update'])->name('update');

    // Brand Management
    Route::get('{organization}/brands', [\App\Http\Controllers\BrandController::class, 'index'])->name('brands.index');
    Route::get('{organization}/brands/create', [\App\Http\Controllers\BrandController::class, 'create'])->name('brands.create');
    Route::post('{organization}/brands', [\App\Http\Controllers\BrandController::class, 'store'])->name('brands.store');
    Route::get('{organization}/brands/{brand}', [\App\Http\Controllers\BrandController::class, 'show'])->name('brands.show');
    Route::get('{organization}/brands/{brand}/edit', [\App\Http\Controllers\BrandController::class, 'edit'])->name('brands.edit');
    Route::put('{organization}/brands/{brand}', [\App\Http\Controllers\BrandController::class, 'update'])->name('brands.update');
    Route::delete('{organization}/brands/{brand}', [\App\Http\Controllers\BrandController::class, 'destroy'])->name('brands.destroy');

    // Location Management
    Route::get('{organization}/locations/create', [\App\Http\Controllers\LocationController::class, 'create'])->name('locations.create');
    Route::post('{organization}/locations', [\App\Http\Controllers\LocationController::class, 'store'])->name('locations.store');
    Route::get('{organization}/locations/{location}/edit', [\App\Http\Controllers\LocationController::class, 'edit'])->name('locations.edit');
    Route::put('{organization}/locations/{location}', [\App\Http\Controllers\LocationController::class, 'update'])->name('locations.update');
    Route::delete('{organization}/locations/{location}', [\App\Http\Controllers\LocationController::class, 'destroy'])->name('locations.destroy');

    // SLA Rules Management
    Route::get('{organization}/sla-rules/create', [\App\Http\Controllers\SlaRuleController::class, 'create'])->name('sla-rules.create');
    Route::post('{organization}/sla-rules', [\App\Http\Controllers\SlaRuleController::class, 'store'])->name('sla-rules.store');
    Route::get('{organization}/sla-rules/{rule}/edit', [\App\Http\Controllers\SlaRuleController::class, 'edit'])->name('sla-rules.edit');
    Route::put('{organization}/sla-rules/{rule}', [\App\Http\Controllers\SlaRuleController::class, 'update'])->name('sla-rules.update');
    Route::delete('{organization}/sla-rules/{rule}', [\App\Http\Controllers\SlaRuleController::class, 'destroy'])->name('sla-rules.destroy');
});

// Posts Routes (Communication MVP)
Route::middleware(['auth', 'verified', 'organization.active'])->prefix('posts')->name('posts.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('create', [PostController::class, 'create'])->name('create');
    Route::post('/', [PostController::class, 'store'])->name('store');
    Route::get('{post}', [PostController::class, 'show'])->name('show');
    Route::get('{post}/edit', [PostController::class, 'edit'])->name('edit');
    Route::put('{post}', [PostController::class, 'update'])->name('update');
    Route::delete('{post}', [PostController::class, 'destroy'])->name('destroy');
    Route::post('{post}/approve', [PostController::class, 'approve'])->name('approve');
    Route::post('{post}/reject', [PostController::class, 'reject'])->name('reject');
    Route::post('{post}/publish', [PostController::class, 'publish'])->name('publish');
    Route::post('{post}/archive', [PostController::class, 'archive'])->name('archive');
});

// Tickets Routes (CRM MVP)
Route::middleware(['auth', 'verified', 'organization.active'])->prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::get('create', [TicketController::class, 'create'])->name('create');
    Route::post('/', [TicketController::class, 'store'])->name('store');
    Route::get('{ticket}', [TicketController::class, 'show'])->name('show');
    Route::get('{ticket}/edit', [TicketController::class, 'edit'])->name('edit');
    Route::put('{ticket}', [TicketController::class, 'update'])->name('update');
    Route::delete('{ticket}', [TicketController::class, 'destroy'])->name('destroy');
    Route::post('{ticket}/status', [TicketController::class, 'updateStatus'])->name('update-status');
    Route::post('{ticket}/resolve', [TicketController::class, 'resolve'])->name('resolve');
    Route::post('{ticket}/close', [TicketController::class, 'close'])->name('close');
    Route::post('{ticket}/reopen', [TicketController::class, 'reopen'])->name('reopen');
    Route::post('{ticket}/messages', [TicketController::class, 'addMessage'])->name('add-message');
    Route::post('{ticket}/assign', [TicketController::class, 'assign'])->name('assign');
});

// Audience Segments Routes
Route::middleware(['auth', 'verified', 'organization.active'])->prefix('audience-segments')->name('audience-segments.')->group(function () {
    Route::get('/', [AudienceSegmentController::class, 'index'])->name('index');
    Route::get('create', [AudienceSegmentController::class, 'create'])->name('create');
    Route::post('/', [AudienceSegmentController::class, 'store'])->name('store');
    Route::get('{segment}/edit', [AudienceSegmentController::class, 'edit'])->name('edit');
    Route::put('{segment}', [AudienceSegmentController::class, 'update'])->name('update');
    Route::delete('{segment}', [AudienceSegmentController::class, 'destroy'])->name('destroy');
});

// Organization Dashboard Routes - Guest browsing allowed for home, auth required for management
Route::middleware(['organization.access'])->prefix('organization/{organization}/dashboard')->name('dashboard.')->group(function () {
    // Home - Channel Feeds (public browsing allowed)
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Management Routes - require authentication
    Route::middleware('auth')->group(function () {
        // Users Management
        Route::resource('users', UserController::class)->middleware('can:user.manage');

        // Groups Management
        Route::resource('groups', GroupController::class)->middleware('can:group.manage');

        // Channels Management
        Route::get('channels', [ChannelController::class, 'index'])->name('channels.index');
        Route::get('create-channel', [ChannelController::class, 'create'])->name('channels.create')->middleware('can:channel.create');
        Route::post('channels', [ChannelController::class, 'store'])->name('channels.store')->middleware('can:channel.create');
        Route::get('channels/{channelId}', [ChannelController::class, 'show'])->name('channels.show');
        Route::post('channels/{channelId}/subscribe', [ChannelController::class, 'subscribe'])->name('channels.subscribe');
        Route::post('channels/{channelId}/unsubscribe', [ChannelController::class, 'unsubscribe'])->name('channels.unsubscribe');
        Route::post('channels/{channelId}/toggle-notifications', [ChannelController::class, 'toggleNotifications'])->name('channels.toggle-notifications');
        Route::get('channels/{channelId}/edit', [ChannelController::class, 'edit'])->name('channels.edit')->middleware('can:channel.update');
        Route::put('channels/{channelId}', [ChannelController::class, 'update'])->name('channels.update')->middleware('can:channel.update');
        Route::delete('channels/{channelId}', [ChannelController::class, 'destroy'])->name('channels.destroy')->middleware('can:channel.delete');

        // News Feeds / Posts
        Route::get('feeds', [PostController::class, 'index'])->name('feeds.index');
        Route::get('feeds/create', [PostController::class, 'create'])->name('feeds.create')->middleware('can:feed.publish');
        Route::post('feeds', [PostController::class, 'store'])->name('feeds.store')->middleware('can:feed.publish');
        Route::delete('feeds/{feed}', [PostController::class, 'destroy'])->name('feeds.destroy')->middleware('can:feed.publish');

        // Dashboard Overview
        Route::get('overview', [DashboardController::class, 'index'])->name('overview')->middleware('can:dashboard.view');

        // Settings
        Route::get('settings', [SettingsController::class, 'show'])->name('settings.show');
        Route::post('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.updateProfile');
        Route::post('settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.updateAppearance');
        Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.updateNotifications');
    });
});

require __DIR__.'/auth.php';
